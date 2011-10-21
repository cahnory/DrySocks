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
interface CacheInterface
{
	/**
	 * Load cache
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function load();
	
	/**
	 * Save cache
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function save();
	
	/**
	 * Defines cache autosave state
	 * 
	 * @param bool $save the autosave state
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function autosave($save);
	
	/**
	 * Returns the cached value at the specified offset
	 * 
	 * @param mixed $offset the offset with the value
	 * 
	 * @return mixed the value at the specified offset or null
	 * 
	 * @access public
	 */
	public function offsetGet($offset);
	
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
	public function offsetSet($offset, $value);
	
	/**
	 * Unsets the value at the specified offset
	 * 
	 * @param mixed $offset the offset being unset
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function offsetUnset($offset);
}

?>