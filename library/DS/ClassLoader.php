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
 * @package    DS\ClassLoader
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * Class for dealing with class files.
 *
 * @category   DS
 * @package    DS\ClassLoader
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class ClassLoader
{
	protected $path;
	protected $namespace;
	protected $namespacePattern		= '##';
	protected $namespaceSeparator	= '\\';
	protected $fileExtension		= '.php';
	
	public function __construct($path, $namespace = NULL, $namespaceSeparator = '\\', $fileExtension = '.php') {
		$this->path					= rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->namespace			= $namespace;
		$this->namespaceSeparator	= $namespaceSeparator;
		$this->fileExtension		= $fileExtension;
	}
	
	public function setNamespace($namespace) {
		$this->namespace	= $namespace;
	}
	
	public function setNamespaceSeparator($separator) {
		$this->namespaceSeparator	= $separator;
	}
	
	public function load($name) {
		if(!class_exists($name, false) && !interface_exists($name, false)) {
			$basespace	= $this->namespace
						? $this->namespace . $this->namespaceSeparator
						: NULL;
			if($basespace === NULL || substr($name, 0, strlen($basespace)) == $basespace) {
				$filename	= $this->path
							. str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, substr($name, strlen($basespace)))
							. $this->fileExtension;
				if(is_readable($filename)) {
					include $filename;
				}
			}
		}
	}
	
	public function register($bool = true) {
		if($bool) {
			spl_autoload_register(array($this, 'load'), true);
		} else {
			spl_autoload_unregister(array($this, 'load'));
		}
	}
}

?>