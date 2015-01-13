<?php

/**
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

namespace Phing\Filter;

/**
 * Holds a token.
 *
 * @package   phing.filters
 */
class Token
{

    /**
     * Token key.
     * @var string
     */
    private $_key;

    /**
     * Token value.
     * @var string
     */
    private $_value;

    /**
     * Sets the token key.
     *
     * @param string $key The key for this token. Must not be <code>null</code>.
     */
    public function setKey($key)
    {
        $this->_key = (string)$key;
    }

    /**
     * Sets the token value.
     *
     * @param string $value The value for this token. Must not be <code>null</code>.
     */
    public function setValue($value)
    {
        // special case for boolean values
        if (is_bool($value)) {
            if ($value) {
                $this->_value = "true";
            } else {
                $this->_value = "false";
            }
        } else {
            $this->_value = (string)$value;
        }
    }

    /**
     * Returns the key for this token.
     *
     * @return string The key for this token.
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * Returns the value for this token.
     *
     * @return string The value for this token.
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Sets the token value from text.
     *
     * @param string $value The value for this token. Must not be <code>null</code>.
     */
    public function addText($value)
    {
        $this->setValue($value);
    }
}
