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
 * Tests the Available Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 *
 * TODO: fix these tests on windows. Windows symlink command is mklink. I am not sure why these tests
 *       are throwing errors.
 * @requires OS ^(?:(?!Win).)*$
 */
class AvailableTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/system/AvailableTaskTest.xml"
        );
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("clean");
    }

    public function testDanglingSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNull($this->project->getProperty("prop." . __FUNCTION__));
    }

    public function testFileSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals('true', $this->project->getProperty("prop." . __FUNCTION__));
    }

    public function testFileAbsoluteSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals('true', $this->project->getProperty("prop." . __FUNCTION__));
    }

    public function testDirectorySymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals('true', $this->project->getProperty("prop." . __FUNCTION__));
    }

    public function testDirectoryAbsoluteSymlink()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals('true', $this->project->getProperty("prop." . __FUNCTION__));
    }

    public function testDirectorySymlinkBC()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNull($this->project->getProperty("prop." . __FUNCTION__));
    }
}
