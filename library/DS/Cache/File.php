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

namespace DS\Cache;

/**
 * @category   DS
 * @package    DS\Cache
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class File extends \DS\Cache
{
	protected $filename;

	public function __construct($filename = NULL) {
		if($filename !== NULL) {
			$this->setFile($filename);
		}
		parent::__construct();
	}
	
	/**
	 * Set cache filename
	 * 
	 * @param string $filename the cache filename
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function setFile($filename) {
		if(!is_file($filename)) {
			if(!is_writable(dirname($filename))) {
				throw new \Exception('Couldn\'t create cache file "'.$filename.'"');
			}
			fopen($filename, 'x');
			$this->filename	= realpath($filename);
			$this->upToDate	= false;
			$this->save();
		} elseif(!is_readable($filename) || !is_writable($filename)) {
			throw new \Exception('Cache file "'.$filename.'" has to be readable and writable');
		} else {
			$this->filename	= realpath($filename);
			$this->upToDate	= false;
			$this->load();
		}
	}
	
	/**
	 * Load cache from file
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function load() {
		if(!$this->filename) {
			throw new \Exception('Filename has to be set in order to load cache file');
		}
		if(!$this->upToDate) {
			$this->exchangeArray(unserialize(file_get_contents($this->filename)));
			$this->upToDate	= true;
		}
	}
	
	/**
	 * Save cache to file
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function save() {
		if(!$this->filename) {
			throw new \Exception('Filename has to be set in order to save cache file');
		}
		if(!$this->upToDate) {
			file_put_contents($this->filename, serialize($this->getArrayCopy()));
			$this->upToDate	= true;
		}
	}
}

?>