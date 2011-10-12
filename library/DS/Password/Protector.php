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
	protected $hashAlgorithm	= 'sha512';
	protected $hashKey;
	protected $bcryptCost		= 7;
	protected $bcryptSalt		= NULL;
	protected $salts			= array();
	protected $preSalts			= array();
	protected $processor;
	
	/**
	 * Add salt
	 * 
	 * The salt value is generate on hashing
	 * 
	 * @param SaltInterface $salt the salt
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function addSalt(SaltInterface $salt) {
		$this->salts[]	= $salt;
	}
	
	/**
	 * Add preSalt
	 * 
	 * The salt has to be manually set in order
	 * to make matching tests work (non variable)
	 * 
	 * @param SaltInterface $salt the salt
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function addPreSalt(SaltInterface $salt) {
		$this->preSalts[]		= $salt;
	}
	
	/**
	 * Set hash properties
	 * 
	 * @param string $algorithm algorithm to use
	 * @param string key        shared secret key
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setHash($algorithm, $key = NULL) {
		if(!in_array($algorithm, hash_algos())) {
			throw new \Exception('Unknown algorithm '.$algorithm);
		}
		$this->hashAlgorithm	= $algorithm;
		$this->hashKey			= $key;
	}
	
	/**
	 * Set bcrypt properties
	 * 
	 * @param string $cost number of iterations
	 * @param string salt  shared secret salt
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setBcrypt($cost, $salt = NULL)
	{
	    if(preg_match('#^[./0-9A-Za-z]{0,22}$#', $salt) === 0) {
	    	throw new \Exception('bcrypt expects a salt of 0 to 22 digits of the alphabet [./0-9A-Za-z]');
	    }
	    if($cost < 4 || $cost > 31) {
	    	throw new \Exception('bcrypt expects cost parameter between 4 and 31');
		}
		$this->bcryptCost	= $cost;
		$this->bcryptSalt	= $salt;
	}
	
	/**
	 * Set user function to alterate the hash
	 * 
	 * @param callback $processor alteration function
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setProcessor($processor) {
		if(!is_callable($processor)) {
			throw new \Exception('Processor must be callable');
		}
		$this->processor	= $processor;
	}
	
	/**
	 * Set user function to alterate the hash
	 * 
	 * @param string $password the password to hash
	 * 
	 * @return string the hashed password
	 * 
	 * @access public
	 */
	public function hash($password) {
		// Add shared salts
		foreach($this->preSalts as $key => $salt) {
			$password	= $salt->apply($password);
		}
		
		// Hash the password
		$password	= hash_hmac($this->hashAlgorithm, $password, $this->hashKey);
		
		// Crypt the hash
	    $password	= substr(crypt($password, '$2a$'.sprintf('%02d', $this->bcryptCost).'$'.$this->bcryptSalt), 29);
		
		// Apply user processing function
		if($this->processor) {
			$password	=	call_user_func($this->processor, $password);
		}
		
		// Add password specific salts
		foreach($this->salts as $salt) {
			$salt->generate();
			$password	= $salt->apply($password);
		}
		
		return	$password;
	}
	
	/**
	 * Tel if a password and a hash match
	 * 
	 * @param string $password the password to test
	 * @param string $hash     the hash
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function match($password, $hash) {
		// Remove salts from hash
		$salts	= array_reverse($this->salts);
		foreach($salts as $salt) {
			$hash	= $salt->remove($hash);
		}
		
		// Add shared salts
		foreach($this->preSalts as $key => $salt) {
			$password	= $salt->apply($password);
		}
		
		// Hash the password
		$password	= hash_hmac($this->hashAlgorithm, $password, $this->hashKey);
		
		// Crypt the password hash
	    $password	= substr(crypt($password, '$2a$'.sprintf('%02d', $this->bcryptCost).'$'.$this->bcryptSalt), 29);
		
		// Apply user processing function
		if($this->processor) {
			$password	=	call_user_func($this->processor, $password);
		}
		return $password === $hash;
	}
}

?>