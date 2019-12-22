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

class DescriptionTest extends BuildFileTest
{
    /**
     * Test that the aaddText method appends text to description w/o any spaces
     *
     * @dataProvider getFiles
     */
    public function test($fileName, $outcome)
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/types/{$fileName}.xml");
        $this->assertEquals($outcome, $this->getProject()->getDescription());
    }

    public function getFiles()
    {
        return [
            'Single' => ['description1', 'Test Project Description'],
            'Multi line' => ['description2', "Multi Line\nProject Description"],
            'Multi instance' => ['description3', 'Multi Instance Project Description'],
            'Multi instance nested' => ['description4', 'Multi Instance Nested Project Description'],
        ];
    }
}
