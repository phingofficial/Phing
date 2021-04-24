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

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the Move Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 */
class MoveTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/system/MoveTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testMoveSingleFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/fileB');
    }

    public function testMoveFileSet(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileDoesNotExist(PHING_TEST_BASE . '/etc/tasks/system/tmp/base/fileA');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/new/fileA');
    }

    public function testRenameDirectory(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileDoesNotExist(PHING_TEST_BASE . '/etc/tasks/system/tmp/base/fileA');
        $this->assertFileExists(PHING_TEST_BASE . '/etc/tasks/system/tmp/new/fileA');
    }

    /**
     * Regression test for ticket {@link http://www.phing.info/trac/ticket/582}
     * - Add haltonerror attribute to copy/move tasks.
     */
    public function testIgnoreErrors(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Could not find file ');
    }

    /**
     * Regression test for ticket {@link http://www.phing.info/trac/ticket/307}
     * - Replaceregexp filter works in Copy task but not Move task.
     */
    public function testReplaceRegexp(): void
    {
        $this->executeTarget(__FUNCTION__);

        $contents = file_get_contents(PHING_TEST_BASE . '/etc/tasks/system/tmp/anotherfile.bak');

        $this->assertEquals('BAR', $contents);
    }

    public function testGranularity(): void
    {
        $this->expectLogContaining(__FUNCTION__, 'Test omitted, Test is up to date');
    }
}
