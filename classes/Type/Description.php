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
 * Description is used to provide a project-wide description element
 * (that is, a description that applies to a buildfile as a whole).
 * If present, the &lt;description&gt; element is printed out before the
 * target descriptions.
 *
 * Description has no attributes, only text.  There can only be one
 * project description per project.  A second description element will
 * overwrite the first.
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Craeg Strong <cstrong@arielpartners.com> (Ant)
 * @package phing.types
 */
class Description extends DataType
{
    /**
     * Return the descriptions from all the targets of
     * a project.
     *
     * @param project the project to get the descriptions for.
     * @return string containing the concatenated descriptions of
     *         the targets.
     */
    public static function getAll(Project $project)
    {
        $targets = $project->getTargets();

        $description = '';
        foreach ($targets as $t) {
            self::concatDescriptions($project, $t, $description);
        }
        return $description;
    }

    private static function concatDescriptions(Project $project, Target $t, &$description)
    {
        foreach (self::findElementInTarget($t, 'description') as $task) {
            if ($task instanceof UnknownElement) {
                $ue = $task;
                $descComp = $ue->getWrapper()->getText();
                if ($descComp !== null) {
                    $description .= $project->replaceProperties($descComp);
                }
            }
        }
    }

    private static function findElementInTarget(Target $t, $name)
    {
        return array_filter($t->getTasks(), static function (Task $task) use ($name) {
            return $task->getTaskName() === $name;
        });
    }

    /**
     * Adds descriptive text to the project.
     *
     * @param  $text
     * @return void
     */
    public function addText($text)
    {
        $currentDescription = $this->getProject()->getDescription();
        if ($currentDescription === null) {
            $this->getProject()->setDescription($text);
        } else {
            $this->getProject()->setDescription($currentDescription);
        }
    }
}
