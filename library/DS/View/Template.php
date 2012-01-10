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
 * @package    DS\View
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS\View;

/**
 * @category   DS
 * @package    DS\View
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Template
{
	private	$binds	=	array(
		'.'		=> 'html',	// no format wild card
		'htm'	=> 'html'
	);
	private $mimes	= array(
		'html'	=> 'text/html',
		'json'	=> 'application/json',
		'xml'	=> 'application/xml'
	);
	private	$format		= 'html';
	private	$mime		= 'text/html';
	private	$charset	= 'UTF-8';
	private $data		= array();
	private	$executing	= array();
	private $wrappLevel	= 0;
	private $wrappDepth	= 0;
	private	$wrappers	= array();
	private	$wrapped	= array();
	private $paths		= array();
	private $executioner;
	private	$view;
	
	public function __construct(\DS\ViewInterface $view)
	{
		$this->view			=	$view;
		$this->executioner	=	new Template\Executioner($this);
	}
	
	/**
	 * Returns the value at the specified offset
	 * 
	 * @param mixed $offset the offset with the value
	 * 
	 * @return mixed the value at the specified offset or null
	 * 
	 * @access public
	 */
	public function __get($offset) {
		return	array_key_exists($offset, $this->data)
			?	$this->data[$offset]
			:	NULL;
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
		$this->data[$offset]	= $value;
	}
	
	/**
	 * Set a template repository path
	 *
	 * @param string $path template path to set
	 * 
	 * @return void
	 *
	 * @access public
	 */
	public function setPath($path) {
		$path	= rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->unsetPath($path);
		array_unshift($this->paths, $path);
	}
	
	/**
	 * Unset a template repository path
	 *
	 * @param string $path template path to unset
	 * 
	 * @return void
	 *
	 * @access public
	 */
	public function unsetPath($path) {
		$path	= rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if(($key = array_search($path, $this->paths)) !== false) {
			array_splice($this->path, $key, 1);
		}
	}
	
	/**
	 * Return the filename corresponding to a template name
	 *
	 * @param string $template template name
	 * @param string $format   filename format to search
	 * 
	 * @return string filename or null if not found
	 *
	 * @access public
	 */
	public function getFile($template, $type = NULL) {
		if($type === NULL) {
			$type	= $this->getFormat();
		}
		$filename	= str_replace('\\', DIRECTORY_SEPARATOR, $template).'.'.$type;
		foreach($this->paths as $path) {
			// Simple search
			if(is_readable($path.$filename)) {
				return	$path.$filename;
			}
			
			// Deep search
			$viewname	= $filename;
			while(($pos = strrpos($viewname, '/')) !== false) {
				$viewname	=	substr_replace($viewname, '.', $pos, 1);
				if(is_readable($path.$viewname)) {
					return	$path.$viewname;
				}
			}
		}
	}
	
	/**
	 * Return the current format or default one
	 * 
	 * @return string format
	 *
	 * @access public
	 */
	public function getFormat() {
		$i		= 0;
		$format	= NULL;
		
		// Last forced format is used (first in stack) 
		while(array_key_exists($i, $this->executing) && $format === NULL) {
			$format	= $this->executing[$i]->format;
			$i++;
		}
		
		// Return found forced format or default format if it's null
		return	$format !== NULL
			?	$format
			:	$this->format;
	}
	
	public function getExecuted() {
		return	array_key_exists(0, $this->executing)
			?	$this->executing[0]
			:	NULL;
	}
	
	/**
	 * Return the result of template execution
	 *
	 * @param string $template template to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return string execution result
	 *
	 * @access public
	 */
	public function execute($template, array $data = array(), $format = NULL)
	{
		if($format === NULL) {
			$format	= $this->getFormat();
		}
		if(!$filename = $this->getFile($template, $format)) {
			throw new \Exception('Template '.$template.' couldn\'t be found');
		}
		return	$this->executeFile($filename, $data, $format);
	}
	
	/**
	 * Return the result of file execution
	 *
	 * @param string $filename file to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return string execution result
	 *
	 * @access public
	 */
	public function executeFile($filename, array $data = array(), $format = NULL)
	{
		if(!is_readable($filename)) {
			throw new \Exception('Template file "'.$filename.'" couldn\'t be loaded');
		}
		return	$this->executeTemplate('file', $filename, $data, $format);
	}
	
	/**
	 * Return the result of string execution
	 * When it's possible, always prefered file execution
	 *
	 * @param string $string string to execute
	 * @param array  $data   data used during execution
	 * @param string $format template format
	 *
	 * @return string execution result
	 *
	 * @access public
	 */
	public function executeString($string, array $data = array(), $format = NULL)
	{
		return	$this->executeTemplate('string', $string, $data, $format);
	}
	
	/**
	 * Execute template of any type
	 *
	 * @param string $type     template type
	 * @param string $template template to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return string execution result
	 *
	 * @access protected
	 */
	protected function executeTemplate($type, $template, array $data, $format) {
		$this->stackTemplate($type, $template, $data, $format);
		try {
			$result	= $this->executioner->__invoke($this->executing[0], $this);
			$result	= $this->applyWrapper($result);
		} catch(\exception $exception) {
			$this->removeWrapper();
			array_shift($this->executing);
			throw $exception;
		}
		array_shift($this->executing);
		return	$result;
	}
	
	/**
	 * Register template to the execution stack
	 *
	 * @param string $type     template type
	 * @param string $template template to register
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return void
	 *
	 * @access protected
	 */
	protected function stackTemplate($type, $template, array $data, $format) {
		if($format === NULL) {
			$format	= $this->getFormat();
		}
		$executed	= new \stdClass();
		$executed->template	= $template;
		$executed->type		= $type;
		$executed->data		= $data;
		$executed->format	= $format;
		array_unshift($this->executing, $executed);
	}
	
	/**
	 * Return a var in the scope of the template being
	 * executed, by its name.
	 * If the var is not set, empty or with a bad type,
	 * a default value is returned.
	 *
	 * @param string $name     var name
	 * @param mixed  $default  the var default value
	 * @param mixed  $type     if the type must match with
	 *                         default value, a class name
	 *                         or interface name
	 *
	 * @return string value or default value
	 *
	 * @access public
	 */
	public function attribute($name, $default = NULL, $type = true) {
		if(!array_key_exists($name, $this->executing[0]->data)) {
			$data	= $default;
		} else {
			$data	= $this->executing[0]->data[$name];
			if($data === NULL || $type
			&& ($default !== NULL && gettype($data) !== gettype($default)
				|| is_string($type) && !is_a($data, $type)
			)) {
				$data = $default;
			}
		}
		return	$data;
	}
	
	/**
	 * Return content charset
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getCharset()
	{
		return	$this->charset;
	}
	
	/**
	 * Loop in execution stack
	 *
	 * @param string $back back length
	 * @param array  $data data used during execution
	 *
	 * @return string execution result
	 *
	 * @access public
	 */
	public function loop($back = 0, array $data = array())
	{
		if(is_array($back)) {
			$data	=	$back;
			$back	=	0;
		}
		// TODO: use executeTemplate;-
		if(isset($this->executing[$back])) {
			return	$this->executeTemplate(
				$this->executing[$back]->type,
				$this->executing[$back]->template,
				$this->executing[$back]->data,
				$this->executing[$back]->format
			);
		}
	}
	
	/**
	 * Wrap the executed template by another one
	 *
	 * @param string $template template
	 * @param array  $data     data used during template execution
	 * @param string $format   template format
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function wrap($template, array $data = array(), $format = NULL) {
		if(!$filename = $this->getFile($template, $format)) {
			throw new \Exception('Template "'.$template.'" couldn\'t be found');
		}
		$this->fileWrap($filename, $data, $format);
	}
	
	/**
	 * Wrap the executed template by a file
	 *
	 * @param string $filename template file
	 * @param array  $data     data used during template execution
	 * @param string $format   template format
	 *
	 * @return string execution result
	 *
	 * @access public
	 */
	public function fileWrap($filename, array $data = array(), $format = NULL) {
		if(!is_readable($filename)) {
			throw new \Exception('Template file "'.$filename.'" couldn\'t be loaded');
		}
		$this->templateWrap('file', $filename, $data, $format);
	}
	
	/**
	 * Wrap the executed template by a string
	 *
	 * @param string $filename template string
	 * @param array  $data     data used during template execution
	 * @param string $format   template format
	 *
	 * @return string execution result
	 *
	 * @access public
	 */
	public function stringWrap($string, array $data = array(), $format = NULL) {
		$this->templateWrap('string', $string, $data, $format);
	}
	
	/**
	 * Wrap template of any type
	 *
	 * @param string $type     template type
	 * @param string $template template to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return string execution result
	 *
	 * @access protected
	 */
	protected function templateWrap($type, $template, array $data, $format) {
		if(!array_key_exists(0, $this->executing)) {
			throw new \Exception('wrap method could only be called inside a template');
		}
		array_unshift($this->wrappers, array(
			'type'			=> $type,
			'template'		=> $template,
			'data'			=> $data,
			'format'		=> $format,
			'executioner'	=> &$this->executing[0])
		);
	}
	
	/**
	 * Return the content being wrapped
	 *
	 * @return string wrapped template execution result
	 *
	 * @access public
	 */
	public function getWrappedContent() {
		return	array_key_exists(0, $this->wrapped)
			?	$this->wrapped[0]
			:	NULL; 
	}
	
	/**
	 * Apply wrapper to a template execution result
	 *
	 * @param string $content template execution result
	 *
	 * @return string wrapped execution result
	 *
	 * @access protected
	 */
	protected function applyWrapper($content) {
		while(isset($this->wrappers[0]) && $this->wrappers[0]['executioner'] === $this->executing[0]) {
			// Unstack the wrapper
			$wrapper	= array_shift($this->wrappers);
			
			// Stack wrapped content
			array_unshift($this->wrapped, $content);
			
			try {
				// Execute wrapper
				if($wrapper['type'] == 'file') {
					$content	= $this->executeFile($wrapper['template'], $wrapper['data'], $wrapper['format']);
				} else {
					$content	= $this->executeString($wrapper['template'], $wrapper['data'], $wrapper['format']);
				}

			// TODO: verifier
			} catch(\exception $exception) {
				// Unstack wrapped content
				array_shift($this->wrapped);
				throw $exception;
			}
			
			// Unstack wrapped content
			array_shift($this->wrapped);
		}
		return	$content;
	}
	
	/**
	 * Remove wrapper associated to the current template
	 * 
	 * This method is called when Exception is thrown during
	 * template execution.
	 *
	 * @return void
	 *
	 * @access protected
	 */
	protected function removeWrapper() {
		while(isset($this->wrappers[0]) && $this->wrappers[0]['executioner'] === $this->executing[0]) {
			// Unstack the wrapper
			$wrapper	= array_shift($this->wrappers);
		}
	}
	
	/**
	 * Add the result of template execution at the end of view content
	 *
	 * @param string $template template to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function put($template, array $data = array(), $format = NULL)
	{
		$this->view->setContent($this->view->getContent().$this->execute($template, $data, $format));
	}
	
	/**
	 * Add the result of file execution at the end of view content
	 *
	 * @param string $template file to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function putFile($filename, array $data = array(), $format = NULL)
	{
		$this->view->setContent($this->view->getContent().$this->executeFile($filename, $data, $format));
	}
	
	/**
	 * Add the result of string execution at the end of view content
	 *
	 * @param string $template string to execute
	 * @param array  $data     data used during execution
	 * @param string $format   template format
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function putString($string, array $data = array(), $format = NULL)
	{
		$this->view->setContent($this->view->getContent().$this->executeString($string, $data, $format));
	}
	/**
	 * Set content charset
	 *
	 * @param string $charset content charset
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function setCharset($charset)
	{
		$this->charset	=	$charset;
		
		$this->setHeader();
	}
	
	/**
	 * Set the template and mime type to use by associated url format
	 *
	 * @param string $format url format
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function setFormat($format)
	{
		if($format === NULL) {
			$format = '.';
		}
		
		// Format binded to another one
		if(array_key_exists($format, $this->binds)) {
			$format	= $this->binds[$format];
		}
		
		// Format isn't set
		if(!array_key_exists($format, $this->mimes)) {
			throw new \Exception('Unknown '.$format.' template format');
		}
		
		$this->format	= $format;
		$this->mime		= $this->mimes[$format];
		
		$this->setHeader();
	}
	
	/**
	 * Bind an url format to a template type and mime
	 *
	 * @param string $format url format
	 * @param string $type   template type
	 * @param string $mime   mime type
	 *
	 * @return void
	 *
	 * @access public
	 */
	public function bindFormat($urlFormat, $format, $mime = NULL)
	{
		$this->binds[$urlFormat]	= array($format);
		if($mime !== NULL) {
			$this->mimes[$format]	= $mime;
		} elseif(!array_key_exists($format, $this->mimes)) {
			throw new \Exception('No MIME type defined for '.$format.' view format');
		}
	}
	
	/**
	 * Set content-type header field
	 *
	 * @return void
	 *
	 * @access private
	 */
	private	function setHeader()
	{
		$this->view->setHeader('Content-type', $this->mime.'; charset='.$this->charset);
	}
}

?>