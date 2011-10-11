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
 * @package    DS\Password
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS\Password;

/**
 * Class for password protection
 *
 * @category   DS
 * @package    DS\Password
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
interface SaltInterface
{
	public function __construct($length = 8, $position = 0, $value = NULL);
	
	/**
	 * Return the salt value
	 * 
	 * @return string
	 * 
	 * @access public
	 */
	public function __toString();
	
	/**
	 * Set the salt value
	 * 
	 * @param int $value the salt value matching the
	 * 					 charset pattern
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setValue($value);
	
	/**
	 * Return the salt value
	 * 
	 * @return string
	 * 
	 * @access public
	 */
	public function getValue();
	
	
	/**
	 * Return the salt length
	 * 
	 * @return int
	 * 
	 * @access public
	 */
	public function getLength();
	
	/**
	 * Set the salt length
	 * 
	 * @param int $length the length of the salt
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setLength($length);
	
	/**
	 * Return the salt position
	 * 
	 * @return int
	 * 
	 * @access public
	 */
	public function getPosition();
	
	/**
	 * Set the salt position
	 * 
	 * @param int $position the position of the salt in the string
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setPosition($position);
	
	/**
	 * Apply the salt to the string
	 * 
	 * @param string $string the string to salt
	 * 
	 * @return string the string with salt applied
	 * 
	 * @access public
	 */
	public function apply($string);
	
	/**
	 * Extract the salt from a string
	 * 
	 * @param string $string string which contains the salt
	 * 
	 * @return string the found salt
	 * 
	 * @access public
	 */
	public function extract($string);
	
	/**
	 * Remove the salt from a string
	 * 
	 * @param string $string string which contains the salt
	 * 
	 * @return string the string without salt
	 * 
	 * @access public
	 */
	public function remove($string);
	
	/**
	 * Generate a salt value
	 * 
	 * @return string a salt value
	 * 
	 * @access public
	 */
	public function generate();
	
	/**
	 * Tel if a string is a valid salt
	 * 
	 * To be valid, the string must match the
	 * charsetPattern.
	 * 
	 * @param string $salt the string to validate
	 * 
	 * @return bool
	 * 
	 * @access public
	 */
	public function validate($salt);
}

?>