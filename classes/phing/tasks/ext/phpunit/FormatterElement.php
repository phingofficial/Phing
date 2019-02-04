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

/**
 * A wrapper for the implementations of PHPUnit2ResultFormatter.
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.ext.phpunit
 * @since   2.1.0
 */
class FormatterElement
{
    /**
     * @var PHPUnitResultFormatter7 $fomatter
     */
    protected $formatter;

    protected $type = "";

    protected $useFile = true;

    protected $toDir = ".";

    protected $outfile = "";

    protected $parent;

    /**
     * Sets parent task
     *
     * @param Task $parent Calling Task
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * Loads a specific formatter type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Loads a specific formatter class
     *
     * @param $className
     */
    public function setClassName($className)
    {
        $classNameNoDot = Phing::import($className);

        $this->formatter = new $classNameNoDot();
    }

    /**
     * Sets whether to store formatting results in a file
     *
     * @param $useFile
     */
    public function setUseFile($useFile)
    {
        $this->useFile = $useFile;
    }

    /**
     * Returns whether to store formatting results in a file
     */
    public function getUseFile()
    {
        return $this->useFile;
    }

    /**
     * Sets output directory
     *
     * @param string $toDir
     */
    public function setToDir($toDir)
    {
        if (!is_dir($toDir)) {
            $toDir = new PhingFile($toDir);
            $toDir->mkdirs();
        }

        $this->toDir = $toDir;
    }

    /**
     * Returns output directory
     *
     * @return string
     */
    public function getToDir()
    {
        return $this->toDir;
    }

    /**
     * Sets output filename
     *
     * @param string $outfile
     */
    public function setOutfile($outfile)
    {
        $this->outfile = $outfile;
    }

    /**
     * Returns output filename
     *
     * @return string
     */
    public function getOutfile()
    {
        if ($this->outfile) {
            return $this->outfile;
        } else {
            return $this->formatter->getPreferredOutfile() . $this->getExtension();
        }
    }

    /**
     * Returns extension
     *
     * @return string
     */
    public function getExtension()
    {
        return $this->formatter->getExtension();
    }

    /**
     * Returns formatter object
     *
     * @throws BuildException
     * @return PHPUnitResultFormatter7
     */
    public function getFormatter()
    {
        if ($this->formatter !== null) {
            return $this->formatter;
        }

        if (class_exists('PHPUnit_Runner_Version', false)) {
            if ($this->type === "summary") {
                $this->formatter = new SummaryPHPUnitResultFormatter5($this->parent);
            } elseif ($this->type === "clover") {
                $this->formatter = new CloverPHPUnitResultFormatter5($this->parent);
            } elseif ($this->type === "xml") {
                $this->formatter = new XMLPHPUnitResultFormatter5($this->parent);
            } elseif ($this->type === "plain") {
                $this->formatter = new PlainPHPUnitResultFormatter5($this->parent);
            } elseif ($this->type === "crap4j") {
                $this->formatter = new Crap4JPHPUnitResultFormatter5($this->parent);
            } else {
                throw new BuildException("Formatter '" . $this->type . "' not implemented");
            }
        } elseif (\version_compare('6', \PHPUnit\Runner\Version::id(), '>=')
            && \version_compare('7', \PHPUnit\Runner\Version::id(), '<')
        ) {
            if ($this->type === "summary") {
                $this->formatter = new SummaryPHPUnitResultFormatter6($this->parent);
            } elseif ($this->type === "clover") {
                $this->formatter = new CloverPHPUnitResultFormatter6($this->parent);
            } elseif ($this->type === "xml") {
                $this->formatter = new XMLPHPUnitResultFormatter6($this->parent);
            } elseif ($this->type === "plain") {
                $this->formatter = new PlainPHPUnitResultFormatter6($this->parent);
            } elseif ($this->type === "crap4j") {
                $this->formatter = new Crap4JPHPUnitResultFormatter6($this->parent);
            } else {
                throw new BuildException("Formatter '" . $this->type . "' not implemented");
            }
        } else {
            if ($this->type === "summary") {
                $this->formatter = new SummaryPHPUnitResultFormatter7($this->parent);
            } elseif ($this->type === "clover") {
                $this->formatter = new CloverPHPUnitResultFormatter7($this->parent);
            } elseif ($this->type === "xml") {
                $this->formatter = new XMLPHPUnitResultFormatter7($this->parent);
            } elseif ($this->type === "plain") {
                $this->formatter = new PlainPHPUnitResultFormatter7($this->parent);
            } elseif ($this->type === "crap4j") {
                $this->formatter = new Crap4JPHPUnitResultFormatter7($this->parent);
            } else {
                throw new BuildException("Formatter '" . $this->type . "' not implemented");
            }
        }

        return $this->formatter;
    }
}
