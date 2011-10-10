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
	
	public function __get($name) {
		return	array_key_exists($name, $this->data)
			?	$this->data[$name]
			:	NULL;
	}
	
	public function __set($name, $value) {
		$this->data[$name]	= $value;
	}
	
	public function setPath($path) {
		$path	= rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		$this->unsetPath($path);
		array_unshift($this->paths, $path);
	}
	
	public function unsetPath($path) {
		$path	= rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
		if(($key = array_search($path, $this->paths)) !== false) {
			array_splice($this->path, $key, 1);
		}
	}
	
	/**
	 *	Return the filename corresponding to a template name
	 *
	 *	@param string $template template name
	 *	@param string $format   filename format to search
	 * 
	 *	@return	string filename or null if not found
	 *
	 *	@access public
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
	 *	Return the current format or default one
	 * 
	 *	@return	string format
	 *
	 *	@access public
	 */
	public function getFormat() {
		$i		= 0;
		$format	= NULL;
		
		// Last forced format is used (first in stack) 
		while(array_key_exists($i, $this->executing) && $format === NULL) {
			$format	= $this->executing[$i]['format'];
			$i++;
		}
		
		// Return found forced format or default format if it's null
		return	$format !== NULL
			?	$format
			:	$this->format;
	}
	
	/**
	 *	Return the result of template execution
	 *
	 *	@param string $template template to execute
	 *	@param array  $data     data used during execution
	 *	@param string $format   template format
	 *
	 *	@return	string execution result
	 *
	 *	@access public
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
	 *	Return the result of file execution
	 *
	 *	@param string $filename file to execute
	 *	@param array  $data     data used during execution
	 *	@param string $format   template format
	 *
	 *	@return	string execution result
	 *
	 *	@access public
	 */
	public function executeFile($filename, array $data = array(), $format = NULL)
	{
		if(!is_readable($filename)) {
			throw new \Exception('Template file '.$filename.' couldn\'t be loaded');
		}
		return	$this->executeTemplate('file', $filename, $data, $format);
	}
	
	/**
	 *	Return the result of string execution
	 *	When it's possible, always prefered file execution
	 *
	 *	@param string $string string to execute
	 *	@param array  $data   data used during execution
	 *	@param string $format template format
	 *
	 *	@return	string execution result
	 *
	 *	@access public
	 */
	public function executeString($string, array $data = array(), $format = NULL)
	{
		return	$this->executeTemplate('string', $string, $data, $format);
	}
	
	protected function executeTemplate($type, $template, array $data, $format) {
		$this->stackTemplate($type, $template, $format);
		$result	=	$this->applyWrapper($this->executioner->execute($template, $type, $data));
		array_shift($this->executing);
		return	$result;
	}
	
	protected function stackTemplate($type, $template, $format = NULL) {
		if($format === NULL) {
			$format	=	$this->getFormat();
		}
		array_unshift($this->executing, compact('type', 'template', 'format'));
	}
	
	/**
	 *	Return content charset
	 *
	 *	@return	string
	 *
	 *	@access public
	 */
	public function getCharset()
	{
		return	$this->charset;
	}
	
	/**
	 *	Loop in execution stack
	 *
	 *	@param string $back back length
	 *	@param array  $data data used during execution
	 *
	 *	@return	string execution result
	 *
	 *	@access public
	 */
	public function loop($back = 0, array $data = array())
	{
		if(is_array($back)) {
			$data	=	$back;
			$back	=	0;
		}
		if(isset($this->executing[$back])) {
			ob_start();
			extract($data);
			if($this->executing[$back][0] == 'file')
				include	$this->executing[$back][1];
			else
				eval('?>'.$this->executing[$back][1]);
			return	ob_get_clean();
		}
	}
	
	public function wrap($template, array $data = array(), $format = NULL) {
		if(!$filename = $this->getFile($template, $format)) {
			throw new \Exception('Template '.$template.' couldn\'t be found');
		}
		$this->fileWrap($filename, $data, $format);
	}
	
	public function fileWrap($filename, array $data = array(), $format = NULL) {
		array_unshift($this->wrappers, array(
			'type'			=> 'file',
			'template'		=> $filename,
			'data'			=> $data,
			'format'		=> $format,
			'executioner'	=> &$this->executing[0]));
	}
	
	public function stringWrap($string, array $data = array(), $format = NULL) {
		array_unshift($this->wrappers, array(
			'type'			=> 'string',
			'template'		=> $string,
			'data'			=> $data,
			'format'		=> $format,
			'executioner'	=> &$this->executing[0]));
	}
	
	public function getWrappedContent() {
		return	array_key_exists(0, $this->wrapped)
			?	$this->wrapped[0]
			:	NULL; 
	}
	
	protected function applyWrapper($content) {
		while(isset($this->wrappers[0]) && $this->wrappers[0]['executioner'] === $this->executing[0]) {
			$wrapper	= array_shift($this->wrappers);
			array_unshift($this->wrapped, $content);
			if($wrapper['type'] == 'file') {
				$content	= $this->executeFile($wrapper['template'], $wrapper['data'], $wrapper['format']);
			} else {
				$content	= $this->executeString($wrapper['template'], $wrapper['data'], $wrapper['format']);
			}
			array_shift($this->wrapped);
		}
		return	$content;
	}
	
	/**
	 *	Add the result of template execution at the end of view content
	 *
	 *	@param string $template template to execute
	 *	@param array  $data     data used during execution
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function put($template, array $data = array(), $format = NULL)
	{
		$this->view->setContent($this->view->getContent().$this->execute($template, $data, $format));
	}
	
	/**
	 *	Add the result of file execution at the end of view content
	 *
	 *	@param string $template file to execute
	 *	@param array  $data     data used during execution
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function putFile($filename, array $data = array(), $format = NULL)
	{
		$this->view->setContent($this->view->getContent().$this->executeFile($filename, $data, $format));
	}
	
	/**
	 *	Add the result of string execution at the end of view content
	 *
	 *	@param string $template string to execute
	 *	@param array  $data     data used during execution
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function putString($string, array $data = array(), $format = NULL)
	{
		$this->view->setContent($this->view->getContent().$this->executeString($string, $data, $format));
	}
	/**
	 *	Set content charset
	 *
	 *	@param string $charset content charset
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function setCharset($charset)
	{
		$this->charset	=	$charset;
		
		$this->setHeader();
	}
	
	/**
	 *	Set the template and mime type to use by associated url format
	 *
	 *	@param string $format url format
	 *
	 *	@return	void
	 *
	 *	@access public
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
	 *	Bind an url format to a template type and mime
	 *
	 *	@param string $format url format
	 *	@param string $type   template type
	 *	@param string $mime   mime type
	 *
	 *	@return	void
	 *
	 *	@access public
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
	 *	Set content-type header field
	 *
	 *	@return	void
	 *
	 *	@access private
	 */
	private	function setHeader()
	{
		$this->view->setHeader('Content-type', $this->mime.'; charset='.$this->charset);
	}
}

?>