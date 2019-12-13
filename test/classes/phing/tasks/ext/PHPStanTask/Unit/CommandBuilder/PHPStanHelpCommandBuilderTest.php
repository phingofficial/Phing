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

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanHelpCommandBuilderTest extends TestCase
{
    /** @var PHPStanHelpCommandBuilder */
    private $builder;

    public function setUp(): void
    {
        $this->builder = new PHPStanHelpCommandBuilder();
    }

    public function testItHandlesCommandOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('phpstan');
        $task->setCommand('help');

        $task->setFormat('anyFormat');
        $task->setRaw(true);
        $task->setCommandName('anyCommand');

        $this->builder->build($task);

        $expectedCommand = <<<CMD
Executing 'phpstan' with arguments:
'help'
'--format=anyFormat'
'--raw'
'anyCommand'
The ' characters around the executable and arguments are not part of the command.
CMD;

        $this->assertEquals($expectedCommand, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }
}
