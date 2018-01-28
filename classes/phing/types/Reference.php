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
 * Class to hold a reference to another object in the project.
 *
 * @package phing.types
 */
class Reference
{
    /** @var string $refid */
    protected $refid;

    /** @var Project $project */
    private $project;

    /**
     * @param string $id
     */
    public function __construct(Project $project, $id = null)
    {
        $this->setRefId($id);
        $this->setProject($project);
    }

    /**
     * @param $id
     */
    public function setRefId($id)
    {
        $this->refid = (string) $id;
    }

    /**
     * @return string
     */
    public function getRefId()
    {
        return $this->refid;
    }

    public function setProject(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Get the associated project, if any; may be null.
     * @return Project the associated project
     */
    public function getProject(): \Project
    {
        return $this->project;
    }
    
    /**
     * returns reference to object in references container of project
     *
     * @param Project|null $fallback
     *
     * @return object
     */
    public function getReferencedObject(Project $fallback = null)
    {
        $project = $fallback ?? $this->project;

        if ($this->refid === null) {
            throw new BuildException("No reference specified");
        }
        $o = $project->getReference($this->refid);
        if ($o === null) {
            throw new BuildException("Reference {$this->refid} not found.");
        }

        return $o;
    }

    public function __toString()
    {
        return $this->refid;
    }
}
