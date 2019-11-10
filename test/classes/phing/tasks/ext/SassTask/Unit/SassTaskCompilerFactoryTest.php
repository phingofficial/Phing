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

class SassTaskCompilerFactoryTest extends TestCase
{

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Neither sass nor scssphp are to be used.
     */
    public function testItFailsWhenNoCompilerIsSet(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('false');
        $sassTask->setUseScssphp('false');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $factory->prepareCompiler($sassTask);
    }

    public function testItReturnSassCompiler(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('false');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $compiler = $factory->prepareCompiler($sassTask);

        $this->assertInstanceOf(SassCompiler::class, $compiler);
    }

    public function testItPrefersSassCompiler(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('true');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $compiler = $factory->prepareCompiler($sassTask);

        $this->assertInstanceOf(SassCompiler::class, $compiler);
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage sass not found. Install sass.
     */
    public function testItFailsWhenSassExecutableNotFound(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('false');
        $sassTask->setExecutable('sass');
        $fileSystem = new FileSystemWhichStub(false);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $factory->prepareCompiler($sassTask);
    }
}
