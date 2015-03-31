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

include_once 'phing/BuildFileTest.php';

/**
 * Testcase for the PhingVersion task/condition.
 *
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system.condition
 */
class PhingVersionTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/PhingVersionTest.xml'
        );
    }

    public function testPhingVersion()
    {
        $this->executeTarget(__FUNCTION__);
        $expectedVersion = $this->getProject()->getProperty('version1');
        $this->assertPropertyEquals('version1', $expectedVersion);
    }

    public function testPhingVersionAtLeastPos()
    {
        $this->executeTarget(__FUNCTION__);
        $expectedVersion = $this->getProject()->getProperty('version2');
        $this->assertPropertyEquals('version2', $expectedVersion);
    }

    public function testPhingVersionAtLeastNeg()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('version3');
    }

    public function testPhingVersionIsNotExact()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('version4');
    }

    public function testPhingVersionAsCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isTrue');
    }
}
