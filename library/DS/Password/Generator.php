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
class Generator
{
	protected $length		= 16;
	protected $numeric		= true;
	protected $lowercase	= false;
	protected $uppercase	= false;
	protected $symbols		= false;
	
	public function useLowercase($use = true) {
		$this->lowercase	=	$use;
	}
	
	public function useUppercase($use = true) {
		$this->uppercase	=	$use;
	}
	
	public function useNumeric($use = true) {
		$this->numeric	=	$use;
	}
	
	public function useSymbols($use = true) {
		$this->symbols	=	$use;
	}
	
	public function getUsedChars() {
		return	  ($this->numeric	? '0123456789' : '')
				. ($this->lowercase	? 'abcdefghijklmnopqrstuvwxyz' : '')
				. ($this->uppercase	? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '')
				. ($this->symbols	? '`!"?$?%^&*()_-+={[}]:;@\'~#|\<,>.?/' : '');
	}
	
	public function generate() {
		$last		= NULL;
		$char		= NULL;
		$password	= NULL;
		if(!$chars = $this->getUsedChars()) {
			throw new Exception('No chars are in use to generate password');
		}
		for($i = 0; $i < $this->length; $i++) {
			while($char === $last) {
				$char	= substr($chars, rand(0, strlen($chars) - 1), 1);
			}
			$last	= $char;
			$password	.= $char;
		}
		return $password;
	}
}

?>