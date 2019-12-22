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

use Monolog\Logger;

/**
 * Listener which sends events to Monolog.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.listener
 */
class MonologListener implements BuildListener
{
    /**
     * log category we log into
     */
    public const LOG_PHING = 'phing';

    /**
     * @var Logger
     */
    private $log;

    /**
     * Construct the listener
     */
    public function __construct()
    {
        $this->log = new Logger(self::LOG_PHING);
    }

    /**
     * @see BuildListener#buildStarted
     * {@inheritDoc}.
     */
    public function buildStarted(BuildEvent $event)
    {
        $log = $this->log->withName(Project::class);
        $log->info('Build started.');
    }

    /**
     * @see BuildListener#buildFinished
     * {@inheritDoc}.
     */
    public function buildFinished(BuildEvent $event)
    {
        $log = $this->log->withName(Project::class);
        if ($event->getException() === null) {
            $log->info('Build finished.');
        } else {
            $log->error('Build finished with error. ' . $event->getException());
        }
    }

    /**
     * @see BuildListener#targetStarted
     * {@inheritDoc}.
     */
    public function targetStarted(BuildEvent $event)
    {
        $log = $this->log->withName(Target::class);
        $log->info(sprintf('Target "%s" started.', $event->getTarget()->getName()));
    }

    /**
     * @see BuildListener#targetFinished
     * {@inheritDoc}.
     */
    public function targetFinished(BuildEvent $event)
    {
        $targetName = $event->getTarget()->getName();
        $cat        = $this->log->withName(Target::class);
        if ($event->getException() === null) {
            $cat->info(sprintf('Target "%s" finished.', $targetName));
        } else {
            $cat->error(sprintf('Target "%s" finished with error. %s', $targetName, $event->getException()));
        }
    }

    /**
     * @see BuildListener#taskStarted
     * {@inheritDoc}.
     */
    public function taskStarted(BuildEvent $event)
    {
        $task = $event->getTask();
        $log  = $this->log->withName(get_class($task));
        $log->info(sprintf('Task "%s" started.', $task->getTaskName()));
    }

    /**
     * @see BuildListener#taskFinished
     * {@inheritDoc}.
     */
    public function taskFinished(BuildEvent $event)
    {
        $task = $event->getTask();
        $log  = $this->log->withName(get_class($task));
        if ($event->getException() === null) {
            $log->info(sprintf('Task "%s" finished.', $task->getTaskName()));
        } else {
            $log->error(sprintf('Task "%s" finished with error. %s', $task->getTaskName(), $event->getException()));
        }
    }

    /**
     * @see BuildListener#messageLogged
     * {@inheritDoc}.
     */
    public function messageLogged(BuildEvent $event)
    {
        $categoryObject = $event->getTask();
        if ($categoryObject === null) {
            $categoryObject = $event->getTarget();
            if ($categoryObject === null) {
                $categoryObject = $event->getProject();
            }
        }

        $log = $this->log->withName(get_class($categoryObject));
        switch ($event->getPriority()) {
            case Project::MSG_WARN:
                $log->warn($event->getMessage());
                break;
            case Project::MSG_INFO:
                $log->info($event->getMessage());
                break;
            case Project::MSG_VERBOSE:
            case Project::MSG_DEBUG:
                $log->debug($event->getMessage());
                break;
            default:
                $log->error($event->getMessage());
                break;
        }
    }
}
