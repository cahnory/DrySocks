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
	protected $cache;
	
	protected $aliasesBind		=	array();
	protected $aliasesPattern	=	array();
	protected $aliasesReplace	=	array();
	
	protected $routesBind		=	array();
	protected $routesPattern	=	array();
	protected $routesReplace	=	array();
	
	protected $cachedAliases	=	array();
	protected $cachedRoutes		=	array();
	
	static private $regPatterns	=	array(
		'alpha'		=>	'([a-zA-Z]+)',
		'alphanum'	=>	'([0-9a-zA-Z]+)',
		'end'		=>	'(?=$)',
		'num'		=>	'([0-9]+)',
		'rest'		=>	'(.+$)',
		'seo'		=>	'([0-9a-zA-Z-_]+)'
	);
	static private $patterns	=	'alpha|alphanum|end|num|rest|seo';
	
	/**
	 * Set a cache
	 * 
	 * In order to get route from bind but also bind from route
	 * regex and iterations are used which could consume ressources.
	 * To avoid it as much as possible, a "dictionnary" of results
	 * is made and could be cached.
	 * 
	 * @return \DS\CacheInterface $cache
	 * 
	 * @access public
	 */
	public function setCache(\DS\CacheInterface $cache) {
		$this->cache			= $cache;
		$this->aliasesBind		= (array)$this->cache->aliasesBind;
		$this->aliasesPattern	= (array)$this->cache->aliasesPattern;
		$this->aliasesReplace	= (array)$this->cache->aliasesReplace;
		
		$this->routesBind		= (array)$this->cache->routesBind;
		$this->routesPattern	= (array)$this->cache->routesPattern;
		$this->routesReplace	= (array)$this->cache->routesReplace;
		
		$this->cachedAliases	= (array)$this->cache->cachedAliases;
		$this->cachedRoutes		= (array)$this->cache->cachedRoutes;
	}
	
	/**
	 * Update the cache
	 * 
	 * @return void
	 * 
	 * @access protected
	 */
	protected function cache() {
		if($this->cache) {
			$this->cache->aliasesBind		= $this->aliasesBind;
			$this->cache->aliasesPattern	= $this->aliasesPattern;
			$this->cache->aliasesReplace	= $this->aliasesReplace;
			
			$this->cache->routesBind		= $this->routesBind;
			$this->cache->routesPattern		= $this->routesPattern;
			$this->cache->routesReplace		= $this->routesReplace;
			
			$this->cache->cachedAliases		= $this->cachedAliases;
			$this->cache->cachedRoutes		= $this->cachedRoutes;
		}
	}
	
	/**
	 * Bind an alias to a route
	 * 
	 * @param string $alias the alias, could contain wildcards
	 * @param string $route the destination route
	 * 
	 * @return void
	 * 
	 * @access public
	 */
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
			$this->cache();
		}
	}
	
	/**
	 * Unbind the specified alias
	 * 
	 * @param string $alias the alias to unbind
	 * 
	 * @return void
	 * 
	 * @access public
	 */
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
			$this->cache();
		}
	}
	
	/**
	 * Return the alias for the specified route
	 * 
	 * @param string $route the route
	 * 
	 * @return string the alias for the specified route
	 * 
	 * @access public
	 */
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
				$this->cache();
				$found							= null;
				return	$url;
			}
		}
		return	$route;
	}
	
	/**
	 * Return the route for the specified alias
	 * 
	 * @param string $alias the alias
	 * 
	 * @return string the route for the specified alias
	 * 
	 * @access public
	 */
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
				$this->cache();
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