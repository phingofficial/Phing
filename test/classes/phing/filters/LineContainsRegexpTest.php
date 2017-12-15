<?php

/*
 *  $Id$
 *
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

require_once 'phing/BuildFileTest.php';
include_once 'phing/util/FileUtils.php';

/**
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.filters
 */
class LineContainsRegexpTest extends BuildFileTest
{
    protected $fu;

    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/filters/linecontainsregexp.xml");
        $this->fu = new FileUtils();
    }

    public function tearDown()
    {
        $this->executeTarget("cleanup");
    }

    public function testLineContainsRegexp()
    {
        $this->executeTarget(__FUNCTION__);

        $expected = $this->getProject()->resolveFile("expected/linecontains.test");
        $result = $this->getProject()->resolveFile("result/linecontains.test");
        $this->assertTrue($this->fu->contentEquals($expected, $result), "Files don't match!");
    }

    public function testLineContainsRegexpNegate()
    {
        $this->executeTarget(__FUNCTION__);

        $expected = $this->getProject()->resolveFile("expected/linecontainsregexp-negate.test");
        $result = $this->getProject()->resolveFile("result/linecontains.test");
        $this->assertTrue($this->fu->contentEquals($expected, $result), "Files don't match!");
    }

    public function testLineContainsRegexpCaseInsensitive()
    {
        $this->executeTarget(__FUNCTION__);

        $expected = $this->getProject()->resolveFile("expected/linecontains.test");
        $result = $this->getProject()->resolveFile("result/linecontains.test");
        $this->assertTrue($this->fu->contentEquals($expected, $result), "Files don't match!");
    }
}
