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

class LastModifiedAlgorithm implements Algorithm
{
    /**
     * This algorithm doesn't need any configuration.
     * Therefore it's always valid.
     * @return bool always true
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Computes a 'timestamp' for a file based on the lastModified time.
     * @param file  The file for which the value should be computed
     * @return string|null the timestamp or <i>null</i> if the timestamp couldn't be computed
     */
    public function getValue(PhingFile $file): ?string
    {
        $lastModified = $file->lastModified();
        if ($lastModified === 0) {
            return null;
        }

        return (string) $lastModified;
    }

    /**
     * @return string some information about this algorithm.
     */
    public function __toString(): string
    {
        return sprintf('<%s>', __CLASS__);
    }
}
