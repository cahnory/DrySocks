<?php
/**
 * DrySocks Framework
 *
 * LICENSE
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   DS
 * @package    DS\Cache
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * @category   DS
 * @package    DS\Cache
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class ArrayObject extends \ArrayObject
{
	
	/**
	 * Sets the value at the specified offset
	 * 
	 * @param mixed $offset the offset being set
	 * @param mixed $value  the new value for the offset
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function __construct($input = NULL) {
		$input	= (array)$input;
		foreach($input as &$value) {
			if(is_array($value) || is_object($value)) {
				$value	= new self($value);
			}
		}
		parent::__construct($input);
	}
    
    /**
     * returns DS\ArrayObject
     *
     * @return string
     */
    public function __ToString()
    {
        return 'DS\ArrayObject';
    }
	
	/**
	 * Returns the cached value at the specified offset
	 * 
	 * @param mixed $offset the offset with the value
	 * 
	 * @return mixed the value at the specified offset or null
	 * 
	 * @access public
	 */
	public function __get($offset) {
		return	$this->offsetGet($offset);
	}
	
	/**
	 * Sets the value at the specified offset
	 * 
	 * @param mixed $offset the offset being set
	 * @param mixed $value  the new value for the offset
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function __set($offset, $value) {
		$this->offsetSet($offset, $value);
	}
	
	/**
	 * Returns the cached value at the specified offset
	 * 
	 * @param mixed $offset the offset with the value
	 * 
	 * @return mixed the value at the specified offset or null
	 * 
	 * @access public
	 */
	public function offsetGet($offset) {
		$value	= NULL;
		if($this->offsetExists($offset)) {
			$value	= parent::offsetGet($offset);
			if(is_array($value) || is_object($value)) {
				$value	= new self($value);
			}
		}
		return	$value;
	}
}

?>