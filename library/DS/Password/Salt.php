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
abstract class Salt implements SaltInterface
{
	protected $length;
	protected $value;
	protected $position	= 0;
	
	protected $charset			= '';
	protected $charsetPattern	= '';
	protected $charsetLength	= 0;
	
	public function __construct($length = 8, $position = 0, $value = NULL) {
		$this->setLength($length);
		$this->setPosition($position);
		if($value === NULL) {
			$this->generate();
		} else {
			$this->setValue($value);
		}
	}
	
	/**
	 * Return the salt value
	 * 
	 * @return string
	 * 
	 * @access public
	 */
	public function __toString() {
		return	$this->getValue();
	}
	
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
	public function setValue($value) {
		if(!$this->validate($value)) {
			throw new \Exception('The salt value doesn\t match the pattern '.$this->charsetPattern);
		}
		$this->value	= (string)$value;
		$this->length	= strlen($this->value);
	}
	
	/**
	 * Return the salt value
	 * 
	 * @return string
	 * 
	 * @access public
	 */
	public function getValue() {
		return	$this->value;
	}
	
	
	/**
	 * Return the salt length
	 * 
	 * @return int
	 * 
	 * @access public
	 */
	public function getLength() {
		return	$this->length;
	}
	
	/**
	 * Set the salt length
	 * 
	 * @param int $length the length of the salt
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setLength($length) {
		$this->length = (int)$length;
	}
	
	/**
	 * Return the salt position
	 * 
	 * @return int
	 * 
	 * @access public
	 */
	public function getPosition() {
		return	$this->position;
	}
	
	/**
	 * Set the salt position
	 * 
	 * @param int $position the position of the salt in the string
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setPosition($position) {
		$this->position = (int)$position;
	}
	
	/**
	 * Apply the salt to the string
	 * 
	 * @param string $string the string to salt
	 * 
	 * @return string the string with salt applied
	 * 
	 * @access public
	 */
	public function apply($string) {
		if(strlen($string) < $this->position) {
			throw new \Exception('The string is to small to apply the salt');
		}
		return	substr($string, 0, $this->position)
		. $this->value
		. substr($string, $this->position);
	}
	
	/**
	 * Extract the salt from a string
	 * 
	 * @param string $string string which contains the salt
	 * 
	 * @return string the found salt
	 * 
	 * @access public
	 */
	public function extract($string) {
		if(strlen($string) < $this->position + $this->length) {
			throw new \Exception('The string is to small to contain the salt');
		}
		$salt	= substr($string, $this->position, $this->length);
		if(!$this->validate($salt)) {
			throw new \Exception('No salt matching the pattern '.$this->charsetPattern.' found');
		}
		return $salt;
	}
	
	/**
	 * Remove the salt from a string
	 * 
	 * @param string $string string which contains the salt
	 * 
	 * @return string the string without salt
	 * 
	 * @access public
	 */
	public function remove($string) {
		if(strlen($string) < $this->position + $this->length) {
			throw new \Exception('The string is to small to contain the salt');
		}
		$salt	= substr($string, $this->position, $this->length);
		if(!$this->validate($salt)) {
			throw new \Exception('No salt matching the pattern '.$this->charsetPattern.' found');
		}
		return substr($string, 0, $this->position).substr($string, $this->position + $this->length);
	}
	
	/**
	 * Generate a salt value
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function generate() {
		$this->value	= '';
		for($i = 0; $i < $this->length; $i++) {
			$this->value	.= substr($this->charset, rand(0, $this->charsetLength - 1), 1);	
		}
	}
	
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
	public function validate($salt) {
		return	(bool)preg_match('#^'.$this->charsetPattern.'*$#', $salt);
	}
}

?>