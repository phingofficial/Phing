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

use Phing\Exception\BuildException;
use Phing\Test\Support\BuildFileTest;
use Phing\Util\DefaultClock;

/**
 * Tests the SleepTask.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 */
class SleepTaskTest extends BuildFileTest
{
    private const ERROR_RANGE = 1000000000;

    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/SleepTaskTest.xml'
        );
    }

    public function test1(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertGreaterThanOrEqual(0, $timer->time());
    }

    public function test2(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertGreaterThanOrEqual(0, $timer->time());
    }

    public function test3(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertGreaterThanOrEqual(2000000000 - self::ERROR_RANGE, $timer->time());
    }

    public function test4(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertTrue($timer->time() >= (2000000000 - self::ERROR_RANGE) && $timer->time() < 60000000000);
    }

    /**
     * Expected failure: negative sleep periods are not supported.
     */
    public function test5(): void
    {
        $this->expectException(BuildException::class);
        $this->executeTarget(__FUNCTION__);
    }

    public function test6(): void
    {
        $timer = $this->timer();
        $this->executeTarget(__FUNCTION__);
        $timer->stop();
        $this->assertLessThan(2000000000, $timer->time());
    }

    private function timer(): DefaultClock
    {
        return new class () extends DefaultClock {
            public function time(): float
            {
                return $this->etime - $this->stime;
            }
        };
    }
}
