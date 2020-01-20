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
 * The base class for all Tasks.
 *
 * Use {@link Project#createTask} to register a new Task.
 *
 * @author    Andreas Aderhold <andi@binarycloud.com>
 * @copyright 2001,2002 THYRELL. All rights reserved
 * @see       Project#createTask()
 * @package   phing
 */
abstract class Task extends ProjectComponent
{

    /**
     * Owning Target object
     *
     * @var Target
     */
    protected $target;

    /**
     * Internal taskname (req)
     *
     * @var string
     */
    protected $taskType;

    /**
     * Taskname for logger
     *
     * @var string
     */
    protected $taskName;

    /**
     * Wrapper of the task
     *
     * @var RuntimeConfigurable
     */
    protected $wrapper;

    /**
     * Sets the owning target this task belongs to.
     *
     * @param Target Reference to owning target
     */
    public function setOwningTarget(Target $target)
    {
        $this->target = $target;
    }

    /**
     * @return RuntimeConfigurable
     */
    public function getWrapper()
    {
        return $this->wrapper;
    }

    /**
     * Returns the owning target of this task.
     *
     * @return Target The target object that owns this task
     */
    public function getOwningTarget()
    {
        return $this->target;
    }

    /**
     * Returns the name of task, used only for log messages
     *
     * @return string Name of this task
     */
    public function getTaskName()
    {
        if ($this->taskName === null) {
            // if no task name is set, then it's possible
            // this task was created from within another task.  We don't
            // therefore know the XML tag name for this task, so we'll just
            // use the class name stripped of "task" suffix.  This is only
            // for log messages, so we don't have to worry much about accuracy.
            return preg_replace('/task$/i', '', get_class($this));
        }

        return $this->taskName;
    }

    /**
     * Sets the name of this task for log messages
     *
     * @param string $name
     */
    public function setTaskName($name)
    {
        $this->taskName = (string) $name;
    }

    /**
     * Returns the name of the task under which it was invoked,
     * usually the XML tagname
     *
     * @return string The type of this task (XML Tag)
     */
    public function getTaskType()
    {
        return $this->taskType;
    }

    /**
     * Sets the type of the task. Usually this is the name of the XML tag
     *
     * @param string $name The type of this task (XML Tag)
     */
    public function setTaskType($name)
    {
        $this->taskType = (string) $name;
    }

    /**
     * Returns a name
     *
     * @param  string $slotName
     * @return \RegisterSlot
     */
    protected function getRegisterSlot($slotName)
    {
        return Register::getSlot('task.' . $this->getTaskName() . '.' . $slotName);
    }

    /**
     * Provides a project level log event to the task.
     *
     * @param string $msg The message to log
     * @param int $level The priority of the message
     * @param Exception|null $t
     * @see   BuildEvent
     * @see   BuildListener
     */
    public function log($msg, $level = Project::MSG_INFO, Exception $t = null)
    {
        if ($this->getProject() !== null) {
            $this->getProject()->logObject($this, $msg, $level, $t);
        } else {
            parent::log($msg, $level);
        }
    }

    /**
     * Called by the parser to let the task initialize properly.
     * Should throw a BuildException if something goes wrong with the build
     *
     * This is abstract here, but may not be overloaded by subclasses.
     *
     * @throws BuildException
     */
    public function init()
    {
    }

    /**
     *  Called by the project to let the task do it's work. This method may be
     *  called more than once, if the task is invoked more than once. For
     *  example, if target1 and target2 both depend on target3, then running
     *  <em>phing target1 target2</em> will run all tasks in target3 twice.
     *
     *  Should throw a BuildException if someting goes wrong with the build
     *
     *  This is abstract here. Must be overloaded by real tasks.
     */
    abstract public function main();

    /**
     * Returns the wrapper object for runtime configuration
     *
     * @return RuntimeConfigurable The wrapper object used by this task
     */
    public function getRuntimeConfigurableWrapper()
    {
        if ($this->wrapper === null) {
            $this->wrapper = new RuntimeConfigurable($this, $this->getTaskName());
        }

        return $this->wrapper;
    }

    /**
     *  Sets the wrapper object this task should use for runtime
     *  configurable elements.
     *
     * @param RuntimeConfigurable $wrapper The wrapper object this task should use
     */
    public function setRuntimeConfigurableWrapper(RuntimeConfigurable $wrapper)
    {
        $this->wrapper = $wrapper;
    }

    /**
     *  Configure this task if it hasn't been done already.
     */
    public function maybeConfigure()
    {
        if ($this->wrapper !== null) {
            $this->wrapper->maybeConfigure($this->project);
        }
    }

    /**
     * Perfrom this task
     *
     * @return void
     *
     * @throws BuildException
     * @throws Error
     */
    public function perform(): void
    {
        $reason = null;
        try { // try executing task
            $this->project->fireTaskStarted($this);
            $this->maybeConfigure();
            DispatchUtils::main($this);
        } catch (\BuildException $ex) {
            $loc = $ex->getLocation();
            if ($loc === null || (string) $loc === '') {
                $ex->setLocation($this->getLocation());
            }
            $reason = $ex;
            throw $ex;
        } catch (\Exception $ex) {
            $reason = $ex;
            $be = new \BuildException($ex);
            $be->setLocation($this->getLocation());
            throw $be;
        } catch (\Error $ex) {
            $reason = $ex;
            throw $ex;
        } finally {
            $this->project->fireTaskFinished($this, $reason);
        }
    }

    /**
     * Bind a task to another; use this when configuring a newly created
     * task to do work on behalf of another.
     * Project, OwningTarget, TaskName, Location and Description are all copied
     *
     * Important: this method does not call {@link Task#init()}.
     * If you are creating a task to delegate work to, call {@link Task#init()}
     * to initialize it.
     *
     * @param Task $owner owning target
     */
    public function bindToOwner(Task $owner): void
    {
        $this->setProject($owner->getProject());
        $this->setOwningTarget($owner->getOwningTarget());
        $this->setTaskName($owner->getTaskName());
        $this->setDescription($owner->getDescription());
        $this->setLocation($owner->getLocation());
        $this->setTaskType($owner->getTaskType());
    }
}
