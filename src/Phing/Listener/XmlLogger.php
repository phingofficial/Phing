<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Listener;

use DOMDocument;
use DOMElement;
use Exception;
use Phing\Exception\BuildException;
use Phing\Io\FileOutputStream;
use Phing\Io\IOException;
use Phing\Io\OutputStream;
use Phing\Io\OutputStreamWriter;
use Phing\Phing;
use Phing\Project;

/**
 * Generates a file in the current directory with
 * an XML description of what happened during a build.
 * The default filename is "log.xml", but this can be overridden
 * with the property <code>XmlLogger.file</code>.
 *
 * @author  Michiel Rook <mrook@php.net>
 */
class XmlLogger implements BuildLogger
{
    /**
     * XML element name for a build.
     */
    public const BUILD_TAG = "build";

    /**
     * XML element name for a target.
     */
    public const TARGET_TAG = "target";

    /**
     * XML element name for a task.
     */
    public const TASK_TAG = "task";

    /**
     * XML element name for a message.
     */
    public const MESSAGE_TAG = "message";

    /**
     * XML attribute name for a name.
     */
    public const NAME_ATTR = "name";

    /**
     * XML attribute name for a time.
     */
    public const TIME_ATTR = "time";

    /**
     * XML attribute name for a message priority.
     */
    public const PRIORITY_ATTR = "priority";

    /**
     * XML attribute name for a file location.
     */
    public const LOCATION_ATTR = "location";

    /**
     * XML attribute name for an error description.
     */
    public const ERROR_ATTR = "error";

    /**
     * XML element name for a stack trace.
     */
    public const STACKTRACE_TAG = "stacktrace";

    /**
     * @var DOMDocument The XML document created by this logger.
     */
    private $doc;

    /**
     * @var int Start time for entire build.
     */
    private $buildTimerStart = 0;

    /**
     * @var DOMElement Top-level (root) build element
     */
    private $buildElement;

    /**
     * @var array DOMElement[] The parent of the element being processed.
     */
    private $elementStack = [];

    /**
     * @var array int[] Array of millisecond times for the various elements being processed.
     */
    private $timesStack = [];

    /**
     * @var int
     */
    private $msgOutputLevel = Project::MSG_DEBUG;

    /**
     * @var OutputStream Stream to use for standard output.
     */
    private $out;

    /**
     * @var OutputStream Stream to use for error output.
     */
    private $err;

    /**
     * @var string Name of filename to create.
     */
    private $outFilename;

    /**
     *  Constructs a new BuildListener that logs build events to an XML file.
     */
    public function __construct()
    {
        $this->doc = new DOMDocument("1.0", "UTF-8");
        $this->doc->formatOutput = true;
    }

    /**
     * Fired when the build starts, this builds the top-level element for the
     * document and remembers the time of the start of the build.
     *
     * @param BuildEvent Ignored.
     */
    public function buildStarted(BuildEvent $event)
    {
        $this->buildTimerStart = microtime(true);
        $this->buildElement = $this->doc->createElement(XmlLogger::BUILD_TAG);
        $this->elementStack[] = $this->buildElement;
        $this->timesStack[] = $this->buildTimerStart;
    }

    /**
     * Fired when the build finishes, this adds the time taken and any
     * error stacktrace to the build element and writes the document to disk.
     *
     * @param  BuildEvent $event An event with any relevant extra information.
     *                           Will not be <code>null</code>.
     * @throws BuildException
     */
    public function buildFinished(BuildEvent $event)
    {
        $xslUri = $event->getProject()->getProperty("phing.XmlLogger.stylesheet.uri");
        if ($xslUri === null) {
            $xslUri = "";
        }

        if ($xslUri !== '') {
            $xslt = $this->doc->createProcessingInstruction('xml-stylesheet', 'type="text/xsl" href="' . $xslUri . '"');
            $this->doc->appendChild($xslt);
        }

        $elapsedTime = microtime(true) - $this->buildTimerStart;

        $this->buildElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));

        if ($event->getException() != null) {
            $this->buildElement->setAttribute(XmlLogger::ERROR_ATTR, $event->getException()->getMessage());
            $errText = $this->doc->createCDATASection($event->getException()->getTraceAsString());
            $stacktrace = $this->doc->createElement(XmlLogger::STACKTRACE_TAG);
            $stacktrace->appendChild($errText);
            $this->buildElement->appendChild($stacktrace);
        }

        $this->doc->appendChild($this->buildElement);

        $outFilename = $event->getProject()->getProperty("XmlLogger.file");
        if ($outFilename == null) {
            $outFilename = "log.xml";
        }

        $stream = $this->getOut();

        try {
            if ($stream === null) {
                $stream = new FileOutputStream($outFilename);
            }

            // Yes, we could just stream->write() but this will eventually be the better
            // way to do this (when we need to worry about charset conversions.
            $writer = new OutputStreamWriter($stream);
            $writer->write($this->doc->saveXML());
            $writer->close();
        } catch (IOException $exc) {
            try {
                $stream->close(); // in case there is a stream open still ...
            } catch (Exception $x) {
            }
            throw new BuildException("Unable to write log file.", $exc);
        }

        // cleanup:remove the buildElement
        $this->buildElement = null;

        array_pop($this->elementStack);
        array_pop($this->timesStack);
    }

    /**
     * Fired when a target starts building, remembers the current time and the name of the target.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Will not be <code>null</code>.
     */
    public function targetStarted(BuildEvent $event)
    {
        $target = $event->getTarget();

        $targetElement = $this->doc->createElement(XmlLogger::TARGET_TAG);
        $targetElement->setAttribute(XmlLogger::NAME_ATTR, $target->getName());

        $this->timesStack[] = microtime(true);
        $this->elementStack[] = $targetElement;
    }

    /**
     * Fired when a target finishes building, this adds the time taken
     * to the appropriate target element in the log.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Will not be <code>null</code>.
     */
    public function targetFinished(BuildEvent $event)
    {
        $targetTimerStart = array_pop($this->timesStack);
        $targetElement = array_pop($this->elementStack);

        $elapsedTime = microtime(true) - $targetTimerStart;
        $targetElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));

        $parentElement = $this->elementStack[count($this->elementStack) - 1];
        $parentElement->appendChild($targetElement);
    }

    /**
     * Fired when a task starts building, remembers the current time and the name of the task.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Will not be <code>null</code>.
     */
    public function taskStarted(BuildEvent $event)
    {
        $task = $event->getTask();

        $taskElement = $this->doc->createElement(XmlLogger::TASK_TAG);
        $taskElement->setAttribute(XmlLogger::NAME_ATTR, $task->getTaskName());
        $taskElement->setAttribute(XmlLogger::LOCATION_ATTR, (string) $task->getLocation());

        $this->timesStack[] = microtime(true);
        $this->elementStack[] = $taskElement;
    }

    /**
     * Fired when a task finishes building, this adds the time taken
     * to the appropriate task element in the log.
     *
     * @param BuildEvent $event An event with any relevant extra information.
     *                          Will not be <code>null</code>.
     */
    public function taskFinished(BuildEvent $event)
    {
        $taskTimerStart = array_pop($this->timesStack);
        $taskElement = array_pop($this->elementStack);

        $elapsedTime = microtime(true) - $taskTimerStart;
        $taskElement->setAttribute(XmlLogger::TIME_ATTR, DefaultLogger::formatTime($elapsedTime));

        $parentElement = $this->elementStack[count($this->elementStack) - 1];
        $parentElement->appendChild($taskElement);
    }

    /**
     * Fired when a message is logged, this adds a message element to the
     * most appropriate parent element (task, target or build) and records
     * the priority and text of the message.
     *
     * @param BuildEvent An event with any relevant extra information.
     *              Will not be <code>null</code>.
     */
    public function messageLogged(BuildEvent $event)
    {
        $priority = $event->getPriority();

        if ($priority > $this->msgOutputLevel) {
            return;
        }

        $messageElement = $this->doc->createElement(XmlLogger::MESSAGE_TAG);

        switch ($priority) {
            case Project::MSG_ERR:
                $name = "error";
                break;
            case Project::MSG_WARN:
                $name = "warn";
                break;
            case Project::MSG_INFO:
                $name = "info";
                break;
            default:
                $name = "debug";
                break;
        }

        $messageElement->setAttribute(XmlLogger::PRIORITY_ATTR, $name);

        if (function_exists('mb_convert_encoding')) {
            $messageConverted = mb_convert_encoding($event->getMessage(), 'UTF-8');
        } else {
            $messageConverted = utf8_encode($event->getMessage());
        }

        $messageText = $this->doc->createCDATASection($messageConverted);

        $messageElement->appendChild($messageText);

        if (!empty($this->elementStack)) {
            $this->elementStack[count($this->elementStack) - 1]->appendChild($messageElement);
        }
    }

    /**
     *  Set the msgOutputLevel this logger is to respond to.
     *
     *  Only messages with a message level lower than or equal to the given
     *  level are output to the log.
     *
     *  <p> Constants for the message levels are in Project.php. The order of
     *  the levels, from least to most verbose, is:
     *
     *  <ul>
     *    <li>Project::MSG_ERR</li>
     *    <li>Project::MSG_WARN</li>
     *    <li>Project::MSG_INFO</li>
     *    <li>Project::MSG_VERBOSE</li>
     *    <li>Project::MSG_DEBUG</li>
     *  </ul>
     *
     *  The default message level for DefaultLogger is Project::MSG_ERR.
     *
     * @param int $level The logging level for the logger.
     * @see   BuildLogger#setMessageOutputLevel()
     */
    public function setMessageOutputLevel($level)
    {
        $this->msgOutputLevel = (int) $level;
    }

    /**
     * Sets the output stream.
     *
     * @see   BuildLogger#setOutputStream()
     */
    public function setOutputStream(OutputStream $output)
    {
        $this->out = $output;
    }

    /**
     * Sets the error stream.
     *
     * @see   BuildLogger#setErrorStream()
     */
    public function setErrorStream(OutputStream $err)
    {
        $this->err = $err;
    }

    /**
     * Sets this logger to produce emacs (and other editor) friendly output.
     *
     * @param bool $emacsMode true if output is to be unadorned so that emacs and other editors
     *                        can parse files names, etc.
     */
    public function setEmacsMode($emacsMode)
    {
    }

    /**
     * @return DOMDocument
     */
    public function getDoc()
    {
        return $this->doc;
    }

    /**
     * @return int
     */
    public function getBuildTimerStart()
    {
        return $this->buildTimerStart;
    }

    /**
     * @return DOMElement
     */
    public function getBuildElement()
    {
        return $this->buildElement;
    }

    public function setBuildElement($elem)
    {
        $this->buildElement = $elem;
    }

    public function &getElementStack(): array
    {
        return $this->elementStack;
    }

    public function &getTimesStack(): array
    {
        return $this->timesStack;
    }

    /**
     * @return int
     */
    public function getMsgOutputLevel()
    {
        return $this->msgOutputLevel;
    }

    /**
     * @return OutputStream
     */
    public function getOut()
    {
        return $this->out;
    }

    /**
     * @return OutputStream
     */
    public function getErr()
    {
        return $this->err;
    }

    /**
     * @return string
     */
    public function getOutFilename()
    {
        return $this->outFilename;
    }
}
