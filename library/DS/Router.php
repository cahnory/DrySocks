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
 * @package    DS\Router
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * Class for dealing with request (HTTP and CLI)
 *
 * @category   DS
 * @package    DS\Router
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Router
{
	private	$cacheFilename;
	private $cacheUpToDate;
	
	private $aliasesBind	=	array();
	private $aliasesPattern	=	array();
	private $aliasesReplace	=	array();
	
	private $routesBind		=	array();
	private $routesPattern	=	array();
	private $routesReplace	=	array();
	
	private $cachedAliases	=	array();
	private $cachedRoutes	=	array();
	
	static private $regPatterns	=	array(
		'alpha'		=>	'([a-zA-Z]+)',
		'alphanum'	=>	'([0-9a-zA-Z]+)',
		'end'		=>	'(?=$)',
		'num'		=>	'([0-9]+)',
		'rest'		=>	'(.+$)',
		'seo'		=>	'([0-9a-zA-Z-_]+)'
	);
	static private $patterns	=	'alpha|alphanum|end|num|rest|seo';
	
	public function __destruct() {
		if(is_writable($this->cacheFilename) && !$this->cacheUpToDate) {
			file_put_contents($this->cacheFilename, serialize(array(
				'aliasesBind'		=> $this->aliasesBind,
				'aliasesPattern'	=> $this->aliasesPattern,
				'aliasesReplace'	=> $this->aliasesReplace,
				
				'routesBind'		=> $this->routesBind,
				'routesPattern'		=> $this->routesPattern,
				'routesReplace'		=> $this->routesReplace,
				
				'cachedAliases'	=> $this->cachedAliases,
				'cachedRoutes'	=> $this->cachedRoutes
			)));
		} 
	}
	
	public function setCacheFile($filename) {
		// Read file if exist
		if(is_readable($filename)) {
			$content				= unserialize(file_get_contents($filename));
			$this->cacheFilename	= realpath($filename);
			if(is_array($content)) {
				$this->aliasesBind		= $content['aliasesBind'];
				$this->aliasesPattern	= $content['aliasesPattern'];
				$this->aliasesReplace	= $content['aliasesReplace'];
				
				$this->routesBind		= $content['routesBind'];
				$this->routesPattern	= $content['routesPattern'];
				$this->routesReplace	= $content['routesReplace'];
				
				$this->cachedAliases	= $content['cachedAliases'];
				$this->cachedRoutes		= $content['cachedRoutes'];
				$this->cacheUpToDate	= true;
			}
		
		// Or create file if folder is writable
		} elseif(is_writable(dirname($filename))) {
			fopen($filename, 'x');
			$this->cacheFilename	= realpath($filename);
		}
	}
	
	public function bind($alias, $route) {
		$alias	= trim($alias, '/');
		$route	= trim($route, '/');
		
		if(($key = array_search($alias, $this->aliasesBind)) !== false
		 && $this->routesBind[$key] != $route) {
			$this->unbind($alias);
			$key = false;
		}
		
		if($key === false) {
			$aliasPattern	= '#^'.preg_quote($alias, '#').'#';
			$aliasReplace	= $alias;
			
			$routePattern	= '#^'.preg_quote($route, '#').'#';
			$routeReplace	= $route;
			
			preg_match_all('#(?<=\<\:)'.self::$patterns.'(?=>)#', $alias, $ma);
			preg_match_all('#(?<=\$)[0-9]+#', $route, $mr);
			
			foreach($ma[0] as $k => $name) {
				$aliasPattern	= preg_replace('#\\\<\\\:'.$name.'\\\>#', self::$regPatterns[$name], $aliasPattern, 1);
				if(array_key_exists($k, $mr[0])) {
					$aliasReplace	= preg_replace('#\<\:'.$name.'>#', '\$'.$mr[0][$k], $aliasReplace, 1);
					$routePattern	= preg_replace('#\\\\\$'.$mr[0][$k].'(?![0-9])#', self::$regPatterns[$name], $routePattern);
				} else {
					$aliasReplace	= preg_replace('#\<\:'.$name.'>#', null, $aliasReplace, 1);
				}
			}
			
			array_unshift($this->aliasesBind,		$alias);
			array_unshift($this->aliasesPattern,	$aliasPattern);
			array_unshift($this->aliasesReplace,	$aliasReplace);
			
			array_unshift($this->routesBind,	$route);
			array_unshift($this->routesPattern,	$routePattern);
			array_unshift($this->routesReplace,	$routeReplace);
			
			$this->cachedAliases	= array();
			$this->cachedRoutes		= array();
			$this->cacheUpToDate	= false;
		}
	}
	
	public function unbind($alias) {
		if(($key = array_search($alias, $this->aliasesBind)) !== false) {
			array_splice($this->aliasesBind, $key, 1);
			array_splice($this->aliasesPattern, $key, 1);
			array_splice($this->aliasesReplace, $key, 1);
			
			array_splice($this->routesBind, $key, 1);
			array_splice($this->routesPattern, $key, 1);
			array_splice($this->routesReplace, $key, 1);
			
			$this->cachedAliases	= array();
			$this->cachedRoutes		= array();
			$this->cacheUpToDate	= false;
		}
	}
	
	public function getAlias($route) {
		static $found = null;
		$route	= trim($route, '/');
		if(array_key_exists($route, $this->cachedAliases)) {
			return	$this->cachedAliases[$route];
		}
		foreach($this->routesPattern as $k => $pattern) {
			$url	=	preg_replace($pattern, $this->aliasesReplace[$k], $route);
			if($url != $route) {
				if($url != $found) {
					$found	=	$url;
					$url	=	$this->getAlias($url);
				}
				$this->cachedAliases[$route]	= $url;
				$this->cacheUpToDate			= false;
				$found							= null;
				return	$url;
			}
		}
		return	$route;
	}
	
	public function getRoute($alias) {
		static $found	= NULL;
		static $aliases	= NULL;
		static $routes	= NULL;
		if($aliases === NULL) {
			$aliases	= $this->aliasesPattern;
			$routes		= $this->routesReplace;
		}
		
		$alias	= trim($alias, '/');
		if(array_key_exists($alias, $this->cachedRoutes)) {
			return	$this->cachedRoutes[$alias];
		}
		foreach($aliases as $k => $pattern) {
			$url	=	preg_replace($pattern, $routes[$k], $alias);
			if($url != $alias) {
				unset($aliases[$k], $routes[$k]); // prevent infinite loops
				if($url != $found) {
					$found	=	$url;
					$url	=	$this->getRoute($url);
				}
				$this->cachedRoutes[$alias]	= $url;
				$this->cacheUpToDate		= false;
				$found						= null;
				$aliases					= null;
				$routes						= null;
				return	$url;
			}
		}
		return	$alias;
	}
}

?>