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
class Protector
{
	protected $salt;
	protected $saltPosition;
	protected $unicSaltLength;
	protected $unicSaltPosition;
	protected $algorithm	= 'sha512';
	protected $processor;
	
	static public function generateSalt($length, $algorithm = 'sha512') {
		if(!in_array($algorithm, hash_algos())) {
			throw new \Exception('Unknown algorithm '.$algorithm);
		}
		$salt	=	NULL;
		while(strlen($salt) < $length) {
			// Hash with hash algorithm to make salt more transparent
			$salt	.= hash($algorithm, uniqid(rand(), true));	
		}
		return	substr($salt, 0, $length);
	}

	public function setSalt($salt, $position = 0) {
		$this->salt			= $salt;
		$this->saltPosition	= $position;
	}
	
	public function setUnicSalt($length, $position = 0) {
		$this->unicSaltLength	= $length;
		$this->unicSaltPosition	= $position;
	}
	
	public function setProcessor($processor) {
		if(!is_callable($processor)) {
			throw new \Exception('Password protect processor must be callable');
		}
		$this->processor	= $processor;
	}
	
	public function setAlgorithm($algorithm) {
		if(!in_array($algorithm, hash_algos())) {
			throw new \Exception('Unknown algorithm '.$algorithm);
		}
		$this->algorithm	= $algorithm;
	}
	
	public function hash($password, $salt = NULL) {
		if($this->salt) {
			$password	= substr($password, 0, $this->saltPosition)
						. $this->salt
						. substr($password, $this->saltPosition);
		}
		$password	=	hash($this->algorithm, $password);
		if($this->unicSaltLength) {
			$password	= substr($password, 0, $this->unicSaltPosition)
						. ($salt !== NULL ? $salt : self::generateSalt($this->unicSaltLength, $this->algorithm))
						. substr($password, $this->unicSaltPosition);
		}
		if($this->processor) {
			$password	=	call_user_func($this->processor, $password);
		}
		return	$password;
	}
	
	public function getPasswordSalt($password) {
		$salt	= NULL;
		if($this->unicSaltLength) {
			$salt	= substr($password, $this->unicSaltPosition, $this->unicSaltLength);
		}
		return $salt;
	}
	
	public function match($password, $hash) {
		return $this->hash($password, $this->getPasswordSalt($hash)) === $hash;
	}
}

?>