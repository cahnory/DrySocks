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

namespace DS;

/**
 * @category   DS
 * @package    DS\View
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class View implements ViewInterface
{
	private	$content;
	private	$headers	=	array();
	
	/**
	 *	Return header value
	 *
	 *	@param string $name header name
	 *
	 *	@return string header value
	 */
	final public function getHeader($name)
	{
		return	array_key_exists($name, $this->headers)
			?	$this->headers[$name]
			:	NULL;
	}
	
	/**
	 *	Return content
	 *
	 *	@return string
	 */
	final public function getContent()
	{
		return	$this->content;
	}
	
	/*
		Set the content
		@param		string		$content	the view content
		@return		void
	*/
	public function setContent($content)
	{
		$this->content	=	$content;
	}
	
	/**
	 *	Set header value
	 *
	 *	@param string $name  header name
	 *	@param string $value header value
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function setHeader($name, $value = NULL)
	{
		if($value === NULL) {
			$this->headers[]		=	$name;
		} else {
			$this->headers[$name]	=	$name.': '.$value;
		}
	}
	
	/**
	 *	Set header value
	 *
	 *	@param string $name  header name
	 *	@param string $value header value
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function unsetHeader($name)
	{
		unset($this->headers[$name]);
	}
	
	/**
	 *	Send headers and display content
	 *
	 *	@return	void
	 *
	 *	@access public
	 */
	public function send()
	{
		if(!headers_sent()) {
			foreach($this->headers as $name => $value) {
				header($value);
			}
		}
		
		echo $this->content;
	}
}

?>