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
 * @package    DS\Session
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * @category   DS
 * @package    DS\Session
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Session extends \ArrayObject
{
	protected $ip;
	protected $data;
	
	public function __construct() {
		// Start session if it's not 
		@session_start(); // @ is dirty but I don't found viable alternativ
		if(($error = error_get_last()) && $error['file'] === __FILE__ && $error['line'] == __LINE__ - 1) {
			var_dump(session_id(),$error);
			if($error['type'] === 2) {
				exit();
				throw new \Exception('Session couldn\'t start after headers were sent');
			}
		}
		
		$this->ip	=	$_SERVER['REMOTE_ADDR'];
		
		// Link to IP if not
		if(!array_key_exists('ip', $_SESSION)) {
			$_SESSION['data']	= $_SESSION;
			$_SESSION['ip']		= $this->ip;
		
		// IPs don't match
		} elseif($_SESSION['ip'] != $this->ip) {
			session_regenerate_id();
			setcookie(session_name(), session_id());
			$_SESSION['data']	= array();
			$_SESSION['ip']		= $this->ip;
		
		// Test if data key was deleted
		} elseif(!array_key_exists('data', $_SESSION)) {
			$_SESSION['data']	= $_SESSION;
			unset($_SESSION['data']['ip']);
		}
		
		parent::__construct(&$_SESSION['data']);
	}
	
	public function __get($offset) {
		return	$this->offsetGet($offset);
	}
	
	public function __set($offset, $value) {
		return	$this->offsetSet($offset, $value);
	}
	
	public function offsetGet($offset) {
		return	$this->offsetExists($offset)
			?	parent::offsetGet($offset)
			:	NULL;
	}
}

?>