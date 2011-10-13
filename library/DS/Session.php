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
	protected $footprint;
	protected $data;
	
	public function __construct() {
		// Start session if it's not 
		@session_start(); // @ is dirty but I don't found viable alternativ
		if(($error = error_get_last()) && $error['file'] === __FILE__ && $error['line'] == __LINE__ - 1) {
			if($error['type'] === 2) {
				throw new \Exception('Session couldn\'t start after headers were sent');
			}
		}
		
		$this->footprint	= $this->getFootprint();
		
		// Link to footprint if not
		if(!array_key_exists('footprint', $_SESSION)) {
			$_SESSION['data']		= $_SESSION;
			$_SESSION['footprint']	= $this->footprint;
		
		// Footprints don't match
		} elseif($_SESSION['footprint'] != $this->footprint) {
			session_regenerate_id();
			setcookie(session_name(), session_id());
			$_SESSION['data']		= array();
			$_SESSION['footprint']	= $this->footprint;
		
		// Test if data key was deleted
		} elseif(!array_key_exists('data', $_SESSION)) {
			$_SESSION['data']	= $_SESSION;
			unset($_SESSION['data']['footprint']);
		}
		
		parent::__construct(&$_SESSION['data']);
	}
	
	/**
	 * Return the client footprint
	 * 
	 * @return string client footprint
	 * 
	 * @access public
	 */
	public function getFootprint() {
		return	$_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT'];
	}
	
	/**
	 * Returns the session value at the specified offset
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
		return	$this->offsetSet($offset, $value);
	}
	
	/**
	 * Returns the session value at the specified offset
	 * 
	 * @param mixed $offset the offset with the value
	 * 
	 * @return mixed the value at the specified offset or null
	 * 
	 * @access public
	 */
	public function offsetGet($offset) {
		return	$this->offsetExists($offset)
			?	parent::offsetGet($offset)
			:	NULL;
	}
}

?>