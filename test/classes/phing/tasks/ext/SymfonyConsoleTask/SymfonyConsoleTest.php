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
 * Test class for the SymfonyConsoleTask.
 *
 * @author  Nuno Costa <nuno@francodacosta.com>
 * @package phing.tasks.ext
 */
class SymfonyConsoleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var SymfonyConsoleTask
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new SymfonyConsoleTask();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers SymfonyConsoleTask::setCommand
     * @covers SymfonyConsoleTask::getCommand
     */
    public function testSetGetCommand()
    {
        $o = $this->object;
        $o->setCommand('foo');
        $this->assertEquals('foo', $o->getCommand());
    }

    /**
     * @covers SymfonyConsoleTask::setConsole
     * @covers SymfonyConsoleTask::getConsole
     */
    public function testSetGetConsole()
    {
        $o = $this->object;
        $o->setConsole('foo');
        $this->assertEquals('foo', $o->getConsole());
    }

    /**
     * @covers SymfonyConsoleTask::setDebug
     * @covers SymfonyConsoleTask::getDebug
     */
    public function testSetGetDebug()
    {
        $o = $this->object;
        $o->setDebug(false);
        $this->assertEquals(false, $o->getDebug());
    }

    /**
     * @covers SymfonyConsoleTask::setSilent
     * @covers SymfonyConsoleTask::getSilent
     */
    public function testSetGetSilent()
    {
        $o = $this->object;
        $o->setSilent(true);
        $this->assertTrue($o->getSilent());
    }

    /**
     * @covers SymfonyConsoleTask::createArg
     */
    public function testCreateArg()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $this->assertTrue(get_class($arg) == 'Arg');
    }

    /**
     * @covers SymfonyConsoleTask::getArgs
     */
    public function testGetArgs()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $arg = $o->createArg();
        $arg = $o->createArg();
        $this->assertCount(3, $o->getArgs());
    }

    /**
     * @covers SymfonyConsoleTask::getCmdString
     * @todo Implement testMain().
     */
    public function testGetCmdString()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $arg->setName('name');
        $arg->setValue('value');

        $o->setCommand('command');
        $o->setConsole('console');

        $ret = "console command --name=value";

        $this->assertEquals($ret, $o->getCmdString());
    }

    /**
     * @covers SymfonyConsoleTask::getCmdString
     */
    public function testNoDebugGetCmdString()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $arg->setName('name');
        $arg->setValue('value');

        $o->setCommand('command');
        $o->setConsole('console');
        $o->setDebug(false);

        $ret = "console command --name=value --no-debug";

        $this->assertEquals($ret, $o->getCmdString());
    }

    /**
     * @covers SymfonyConsoleTask::getCmdString
     */
    public function testNoDebugOnlyOnce()
    {
        $o = $this->object;
        $arg = $o->createArg();
        $arg->setName('no-debug');

        $o->setCommand('command');
        $o->setConsole('console');
        $o->setDebug(false);

        $ret = "console command --no-debug";

        $this->assertEquals($ret, $o->getCmdString());
    }
}
