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

namespace Phing\Test\Io;

use Phing\Io\File;
use Phing\Io\FileSystem;
use Phing\Io\UnixFileSystem;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for UnixFileSystem.
 *
 * @author Michiel Rook <mrook@php.net>
 */
class UnixFileSystemTest extends TestCase
{
    /**
     * @var FileSystem
     */
    private $fs;

    public function setUp(): void
    {
        $this->fs = new UnixFileSystem();
    }

    public function tearDown(): void
    {
    }

    public function testCompare()
    {
        $f1 = new File(__FILE__);
        $f2 = new File(__FILE__);

        $this->assertEquals(0, $this->fs->compare($f1, $f2));
    }

    public function testHomeDirectory1()
    {
        $this->assertEquals('~/test', $this->fs->normalize('~/test'));
    }

    public function testHomeDirectory2()
    {
        $this->assertEquals('/var/~test', $this->fs->normalize('/var/~test'));
    }

    public function testHomeDirectory3()
    {
        $this->assertEquals('~test', $this->fs->normalize('~test'));
    }
}
