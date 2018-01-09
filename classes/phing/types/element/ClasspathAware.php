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

trait ClasspathAware
{
    /** @var Path $classpath */
    protected $classpath;

    /**
     * Refid to already defined classpath
     */
    protected $classpathId;

    /**
     * Returns the classpath.
     *
     * @return Path|null
     */
    public function getClasspath(): ?\Path
    {
        return $this->classpath;
    }

    /**
     * @param Path $classpath
     *
     * @throws \BuildException
     */
    public function setClasspath(Path $classpath): void
    {
        if ($this->classpath === null) {
            $this->classpath = $classpath;
        } else {
            $this->classpath->append($classpath);
        }
    }

    /**
     * @return Path
     *
     * @throws \BuildException
     */
    public function createClasspath(): \Path
    {
        if ($this->classpath === null) {
            $this->classpath = new Path();
        }

        return $this->classpath->createPath();
    }

    /**
     * Reference to a classpath to use when loading the files.
     *
     * @param Reference $r
     *
     * @throws BuildException
     */
    public function setClasspathRef(Reference $r): void
    {
        $this->classpathId = $r->getRefId();
        $this->createClasspath()->setRefid($r);
    }
}
