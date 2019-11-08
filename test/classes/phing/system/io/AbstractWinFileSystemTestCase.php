<?php

/*
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

/**
 * @author Daniel Holmes
 * @package phing.system.io
 */
abstract class AbstractWinFileSystemTestCase extends \PHPUnit\Framework\TestCase
{

    /**
     * @var FileSystem
     */
    private $fs;

    protected function setUp(): void
    {
        $this->fs = $this->createFileSystem();
    }

    abstract protected function createFileSystem();

    public function testGetSeparatorReturnsCorrect()
    {
        self::assertSame('\\', $this->fs->getSeparator());
    }

    public function testGetPathSeparatorReturnsCorrect()
    {
        self::assertSame(';', $this->fs->getPathSeparator());
    }

    /**
     * @dataProvider normaliseDataProvider
     * @param string $expected
     * @param string $path
     */
    public function testNormalise($expected, $path)
    {
        $normalisedPath = $this->fs->normalize($path);

        self::assertSame($expected, $normalisedPath);
    }

    public function normaliseDataProvider()
    {
        return [
            'alreadyNormal' => ['C:\\My Files\\file.txt', 'C:\\My Files\\file.txt'],
            'incorrectSlashes' => ['C:\\My Files\\file.txt', 'C:/My Files/file.txt'],
            'empty' => ['', ''],
            'relative' => ['My Files\\file.txt', 'My Files/file.txt'],
            'directoryRelative' => ['c:My Files\\file.txt', 'c:My Files\\file.txt'],
            'driveRelative' => ['\\My Files\\file.txt', '\\My Files/file.txt']
            // Error shown in version of phpunit using (3.6.10) when serialising this argument set.
            // Not sure if an issue in phpunit
            //'unc' => array('\\\\server\\My Files\\file.txt', '\\\\server\\My Files\\file.txt')
        ];
    }

    /**
     * @dataProvider prefixLengthDataPRovider
     * @param int $expected
     * @param string $pathname
     */
    public function testPrefixLength(int $expected, string $pathname)
    {
        $length = $this->fs->prefixLength($pathname);

        self::assertSame($expected, $length);
    }

    public function prefixLengthDataProvider()
    {
        return [
            'absoluteLocal' => [3, 'D:\\My Files\\file.txt'],
            // Error shown in version of phpunit using (3.6.10) when serialising this argument set.
            // Not sure if an issue in phpunit
            //'unc' => array(2, '\\\\My Files\file.txt')
            'empty' => [0, ''],
            'driveRelative' => [1, '\\My Files\\file.txt'],
            'directoryRelative' => [2, 'c:My Files\\file.txt'],
            'relative' => [0, 'My Files\\file.txt']
        ];
    }

    /**
     * @dataProvider resolveDataProvider
     * @param string $expected
     * @param string $parent
     * @param string $child
     */
    public function testResolve(string $expected, string $parent, string $child)
    {
        $resolved = $this->fs->resolve($parent, $child);

        self::assertSame($expected, $resolved);
    }

    public function resolveDataProvider()
    {
        return [
            'emptyParent' => ['My Files\\file.txt', '', 'My Files\\file.txt'],
            'emptyChild' => ['C:\\My Files', 'C:\\My Files', ''],
            // Not working properly on my version of phpunit (3.6.10)
            //'uncChild' => array('C:\\My Files\\files\\file.txt', 'C:\\My Files', '\\\\files\\file.txt')
            'driveRelativeChild' => ['C:\\My Files\\file.txt', 'C:\\My Files', '\\file.txt'],
            'endSlashParent' => ['C:\\My Files\\file.txt', 'C:\\My Files\\', '\\file.txt']
        ];
    }

    /**
     * @dataProvider resolveFileDataProvider
     * @param string $expected
     * @param string $path
     * @param string $prefix
     */
    public function testResolveFile(string $expected, string $path, string $prefix)
    {
        $file = $this->getMockBuilder(PhingFile::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getPath')->will($this->returnValue($path));
        $file->expects($this->any())->method('getPrefixLength')->will($this->returnValue($prefix));

        /** @var PhingFile $file */
        $resolved = $this->fs->resolveFile($file);

        self::assertSame($expected, $resolved);
    }

    public function resolveFileDataProvider()
    {
        $cwd = getcwd();
        $driveLetter = '';
        // This is a bit weird, but it lets us run the win tests on unix machines. Might be better
        // to find an abstraction for drive letter within file system
        if (substr(PHP_OS, 0, 3) === 'WIN') {
            $colonPos = strpos($cwd, ':');
            $driveLetter = substr($cwd, 0, $colonPos) . ':';
        } else {
            $cwd = str_replace('/', '\\', $cwd);
        }

        return [
            'absoluteLocal' => ['C:\\My Files\\file.txt', 'C:\\My Files\\file.txt', 3],
            // Error shown in version of phpunit using (3.6.10) when serialising this argument set.
            // Not sure if an issue in phpunit
            //'unc' => array('\\\\files\\file.txt', '\\\\files\\file.txt', 2)
            'relative' => [$cwd . '\\files\file.txt', 'files\\file.txt', 0],
            'driveRelative' => [$driveLetter . '\\files\\file.txt', '\\files\\file.txt', 1]
        ];
    }

    public function testResolveFileUnknownFile()
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Unresolvable path: file.txt');

        $file = $this->getMockBuilder(PhingFile::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getPath')->will($this->returnValue('file.txt'));
        $file->expects($this->any())->method('getPrefixLength')->will($this->returnValue(5));

        /** @var PhingFile $file */
        $this->fs->resolveFile($file);
    }

    public function testGetDefaultParent()
    {
        $parent = $this->fs->getDefaultParent();

        self::assertSame('\\', $parent);
    }

    /**
     * @dataProvider fromURIPathDataProvider
     * @param string $expected
     * @param string $path
     */
    public function testFromURIPath(string $expected, string $path)
    {
        $resultPath = $this->fs->fromURIPath($path);

        self::assertSame($expected, $resultPath);
    }

    public function fromURIPathDataProvider()
    {
        return [
            'singleLetter' => ['f', 'f'],
            'slashStart' => ['/foo', '/foo/'],
            'driveLetter' => ['c:/foo', '/c:/foo'],
            'slashPath' => ['c:/foo', 'c:/foo/'],
            'slashPathRootDrive' => ['c:/', '/c:/']
        ];
    }

    /**
     * @dataProvider isAbsoluteDataProvider
     * @param boolean $expected
     * @param string $path
     * @param int $prefix
     */
    public function testIsAbsolute(bool $expected, string $path, int $prefix)
    {
        $file = $this->getMockBuilder(PhingFile::class)->disableOriginalConstructor()->getMock();
        $file->expects($this->any())->method('getPath')->will($this->returnValue($path));
        $file->expects($this->any())->method('getPrefixLength')->will($this->returnValue($prefix));

        /** @var PhingFile $file */
        $is = $this->fs->isAbsolute($file);

        self::assertSame($expected, $is);
    }

    public function isAbsoluteDataProvider()
    {
        return [
            // Doesn't work for my current version of phpunit
            //'unc' => array(true, '\\\\file.txt', 2)
            'absoluteLocal' => [true, 'C:\\file.txt', 3],
            'driveRelative' => [true, '\\file.txt', 1],
            'relative' => [false, 'file.txt', 0]
        ];
    }
}
