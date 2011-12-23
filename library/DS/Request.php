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
 * @package    DS\Request
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * Class for dealing with request (HTTP and CLI)
 * 
 * Assuming application is installed here:
 *  http://www.domain.com/folder/sub/
 *
 * for this url:
 *  http://www.domain.com/folder/sub/product/view/1.html
 *
 * Here are the generated var
 *	host:		'www.domain.com'
 *  protocol:	'http'
 *  url:		'/folder/sub/product/view/1.html'
 *  path:		'/folder/sub/product/view/1'
 *  base:		'/folder/sub/'
 *  route:		'product/view/1'
 *  format:		'html'
 *  crumbs:		array('product', 'view', '1')
 *
 * @category   DS
 * @package    DS\Request
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Request implements RequestInterface
{
    /**
     * Items uploaded via the HTTP POST method
     *
     * @var array
     * @access private
     */
	private $files = array();
	
    /**
     * Variables passed via the HTTP POST method
     *
     * @var array
     * @access private
     */
	private $post = array();
	
    /**
     * Variables passed via URL parameters
     *
     * @var array
     * @access private
     */
	private $get = array();
	
    /**
     * All variables and items passed
     *
     * @var array
     * @access private
     */
	private $input = array();
	
    /**
     * Variables passed via Command Line Interface
     *
     * @var array
     * @access private
     */
	private $arg = array();
	
    /**
     * The method type
     *
     * @var string
     * @access private
     */
	private $method;
	
    /**
     * If the request is asynchronous (Ajax)
     *
     * @var bool
     * @access private
     */
	private $isAjax;
	
    /**
     * If the script is executed via Command Line Interface
     *
     * @var bool
     * @access private
     */
	private $isCli;
	
    /**
     * The url format (html, txt,…)
     *
     * @var string
     * @access private
     */
	private $format;
	
    /**
     * The url protocol (http, https)
     *
     * @var string
     * @access private
     */
	private $protocol;
	
    /**
     * The host
     *
     * @var string
     * @access private
     */
	private $host;
	
    /**
     * The url without host (base+route)
     *
     * @var string
     * @access private
     */
	private $url;
	
    /**
     * The url without format
     *
     * @var string
     * @access private
     */
	private $path;
	
    /**
     * The base url
     *
     * @var string
     * @access private
     */
	private $base;
	
    /**
     * The route url
     *
     * @var string
     * @access private
     */
	private $route;
	
    /**
     * The route crumbs
     *
     * @var array
     * @access private
     */
	private $routeCrumbs;
	
	public function __construct(array $options = array()) {
		$options	=	array_merge(array(
			'files'		=> $_FILES,
			'post'		=> $_POST,
			'get'		=> $_GET,
			'arg'		=> array_key_exists('argv', $_SERVER) ? $_SERVER['argv'] : array_keys($_REQUEST),
			'method'	=> $_SERVER['REQUEST_METHOD']
		), $options);
		
		// Usefull request properties
		$this->isAjax	= array_key_exists('isAjax', $options)
						? $options['isAjax']
						: isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
		$this->isCli	= array_key_exists('isCli', $options)
						? $options['isCli']
						: isset($_SERVER['SHELL']);
		
		// Variables and items passed to the script
		if(!$this->isCli) {
			$this->method	= strtoupper($options['method']);
			
			$this->files	= self::formatFilesArray($options['files']);
			if (get_magic_quotes_gpc()) {
				$this->post	= array_map(array('self', 'removeMagicQuotes'), $options['post']);
				$this->get	= array_map(array('self', 'removeMagicQuotes'), $options['get']);
			} else {
				$this->post	= $options['post'];
				$this->get	= $options['get'];
			}
			$this->input	= array_merge($this->get, $this->post, $this->files);
		
			// URL
			$this->protocol	= array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] !== 'off'
						? 'https'
						: 'http';
			$this->host	= $_SERVER['HTTP_HOST'];
			$this->url	= array_key_exists('REDIRECT_URL', $_SERVER)
						? $_SERVER['REDIRECT_URL']
						: (false != $pos = strpos($_SERVER['REQUEST_URI'], '?')
							? substr($_SERVER['REQUEST_URI'], 0, $pos)
							: $_SERVER['REQUEST_URI']
						);
			
			// Path and format
			if(preg_match('#(?<=\.)[^\.]+$#', $this->url, $m)) {
				$this->format	= $m[0];
				// Remove format from route
				$this->path	= preg_replace('#\.'.preg_quote($m[0], '#').'$#', '', $this->url);
			} else {
				$this->path	= $this->url;
			}
			$this->setBase(
				array_key_exists('path', $options)
					? $options['path']
					: dirname($_SERVER['SCRIPT_FILENAME'])
			);
		} else {
			$this->arg			= array_slice($options['arg'], 1);
			$this->route		= array_key_exists(0, $this->arg) ? $this->arg[0] : NULL;
			$this->routeCrumbs	= explode('/', $this->route);
		}
	}
		
	/**
	 * Returns read only variables
	 *
	 * @return mixed
	 *
	 * @access public
	 */
	public function __get($name) {
		if ($name == 'get') {
			return	$this->get;
		} elseif ($name == 'post') {
			return	$this->post;
		} elseif ($name == 'files') {
			return	$this->files;
		} elseif ($name == 'input') {
			return	$this->input;
		} elseif ($name == 'arg') {
			return	$this->arg;
		} elseif ($name == 'crumb') {
			return	$this->arg;
		}
	}
		
	/**
	 * Returns if the request is asynchronous (Ajax)
	 *
	 * @return bool
	 *
	 * @access public
	 */
	public function isAjax() {
		return	$this->isAjax;
	}
		
	/**
	 * Returns if the script is executed via Command Line Interface
	 *
	 * @return bool
	 *
	 * @access public
	 */
	public function isCli() {
		return	$this->isCli;
	}
		
	/**
	 * Returns if input method is same as request method
	 *
	 * @param string $method the input method
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function isMethod($method) {
		return	$this->method === strtoupper($method);
	}
		
	/**
	 * Returns the request method
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getHost() {
		return	$this->host;
	}
		
	/**
	 * Returns the request method
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getMethod() {
		return	$this->method;
	}
		
	/**
	 * Returns the url format
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getFormat() {
		return	$this->format;
	}
		
	/**
	 * Returns the base path
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getBase() {
		return	$this->base;
	}
		
	/**
	 * Set the base path
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function setBase($path) {
		$this->base	=	substr(realpath($path), strlen($_SERVER['DOCUMENT_ROOT'])).'/';
		
		// Refresh route
		$a1 	= explode('/', $this->base);
		$a2 	= explode('/', $this->path);
		$break	=	0;
		for($i = 0; array_key_exists($i, $a1); $i++) {
			if(!array_key_exists($i, $a2) || $a1[$i] != $a2[$i]) {
				$break	= $i;
				break;
			}
		}
		$this->routeCrumbs	= array_slice($a2,$i);
		$this->route		= implode('/', $this->routeCrumbs);
	}
		
	/**
	 * Returns the url route
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getRoute() {
		return	$this->route;
	}
		
	/**
	 * Returns an url route crumb
	 *
	 * @param mixed $n the crumb pos
	 *
	 * @return string
	 *
	 * @access public
	 */
	public function getRouteCrumb($n) {
		return	array_key_exists($n, $this->routeCrumbs)
			?	$this->routeCrumbs[$n]
			:	null;
	}
		
	/**
	 * Unquote value
	 *
	 * @param mixed $value the values to unquote
	 *
	 * @return mixed
	 *
	 * @access public
	 * @static
	 */
	static	public	function	removeMagicQuotes($value)
	{
	    return	is_array($value)
	    	?	array_map(array(self, 'removeMagicQuotes'), $value)
	    	:	stripslashes($value);
	}
		
	/**
	 * Change files array tree
	 *
	 * $_FILES tree is different from $_GET and $_POST ones.
	 * This function reformat it like the other ones
	 *
	 * @param mixed $input The array of uploaded items
	 *
	 * @return array
	 *
	 * @access public
	 * @static
	 */
	static	public	function	formatFilesArray(array $input)
	{
		static	$depth	=	-1;
		static	$stack	=	array(array());
		static	$key;
		
		$depth++;
		foreach ($input as $name => $value) {
			if ($depth === 1) {
				$key		=	$name;
				if (is_array($value)) {
					$stack[0]	=	self::formatFilesArray($value);
				}
			} else {
				if(!array_key_exists($name, $stack[0])) {
					$stack[0][$name]	=	array();
				}
				array_unshift($stack, null);
				$stack[0]	=  &$stack[1][$name];
			}
			if (is_array($value)) {
				$stack[0]	=	self::formatFilesArray($value);
			} else {
				$stack[0][$key]	=	$value;	
			}
			if ($depth !== 1) {
				array_shift($stack);
			}
		}
		$depth--;
		
		return	$stack[0];
	}
}

?>