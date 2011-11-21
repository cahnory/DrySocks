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
 * @package    DS_Application
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * DrySock application
 *
 * @category   DS
 * @package    DS_Application
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Application
{
	protected $path;
	protected $errorController;
	
	protected $config;
	protected $errors		= array();
	protected $helpers	= array();
	protected $loader;
	protected $request;
	protected $router;
	protected $event;
	protected $view;
	
	public function __construct(Application\ConfigInterface $config = NULL) {
		$this->config	=	$config;
		if($this->config) {
			try {
				$this->config->apply($this);
			} catch(\Exception $e) {
				$this->throwError(500, $e);
			}
		}
	}
	
	public function __set($name, $value) {
		$this->helpers[$name]	= $value;
	}
	
	public function __get($name) {
		if($name == 'request') {
			return	$this->getRequest();
		} elseif($name == 'router') {
			return	$this->getRouter();
		} elseif($name == 'view') {
			return	$this->getView();
		} elseif($name == 'event') {
			return	$this->getEvent();
		}
		return	$this->getHelper($name);
	}
	
	public function getHelper($name) {
		if(!isset($this->helpers[$name])) {
			throw new \Exception('Helper "'.$name.'" is not defined');
		}
		return	$this->helpers[$name];
	}
	
	public function setHelper($name, $value) {
		$this->helpers[$name]	= $value;
	}
	
	public function getRequest() {
		if(!$this->request) {
			$this->request	= new Request();
		}
		return	$this->request;
	}
	
	public function setRequest(RequestInterface $request) {
		if(!$this->request) {
			$this->request	= $request;
		}
	}
	
	public function getRouter() {
		if(!$this->router) {
			$this->router	= new Router();
		}
		return	$this->router;
	}
	
	public function setRouter(RouterInterface $router) {
		if(!$this->router) {
			$this->router	= $router;
		}
	}
	
	public function getEvent() {
		if(!$this->event) {
			$this->event	= new Event\Dispatcher();
		}
		return	$this->event;
	}
	
	public function setEvent(Event\DispatcherInterface $event) {
		if(!$this->event) {
			$this->event	= $event;
		}
	}
	
	public function getView() {
		if(!$this->view) {
			$this->view	= new View();
		}
		return	$this->view;
	}
	
	public function setView(ViewInterface $view) {
		if(!$this->view) {
			$this->view	= $view;
		}
	}
	
	public function dispatch() {
		if($this->errors) {
			return false;
		}
		
		$event	=	$this->getEvent();
		$event->trigger('dispatch', array('app' => $this));
		
		// Get the requested route
		$alias	= $this->getRequest()->getRoute();
		
		// Get internal route from requested one
		$route	= $this->getRouter()->getRoute($alias);
		
		// Redirect to the main alias
		if($alias !== ($redirect = $this->getRouter()->getAlias($route))) {
			$this->getView()->setHeader('HTTP/1.1 301 Moved Permanently');
			$this->view->setHeader('location', $this->request->getBase().$redirect);
			$event->trigger('redirect', array('app' => $this));
			$this->display();
		
		// Dispatch
		} else {
			// Split the route in controller/action/array(args)
			if(!$route) {
				$controller	= 'Index';
				$action		= 'index';
				$args		= array();
			} else {
				$crumbs	= explode('/', $route);
				if(!array_key_exists(1, $crumbs)) {
					$controller	= $crumbs[0];
					$action		= 'index';
					$args		= array();
				} else {
					$controller	= $crumbs[0];
					$action		= $crumbs[1];
					$args		= array_slice($crumbs, 2);
				}
			}
			$controller	= $this->request->isCli()
						? 'Cli\\'.$controller
						: $controller;
			
			try {
				$controller	= $this->prepareController($controller);
				$event->trigger('control', array('app' => $this));
				// Call controller action
				$this->controlAction(
					$controller,
					$action,
					$args
				);
				$this->display();
				
			// Dispatch error
			} catch(Controller\Exception $e) {
				$this->throwError(404, $e);
			
			// Controller error
			} catch(\Exception $e) {
				$this->throwError(500, $e);
			}
		}
	}
	
	protected function display() {
		if(!$this->request->isCli()) {
			$this->event->trigger('display', array('app' => $this));
			$this->view->send();
			$this->event->trigger('shutdown', array('app' => $this));
		}
	}
	
	protected function throwError($error, $exception) {
		$this->errors[]	= $exception;
		if($this->errorController) {
			try {
				$event	= $this->getEvent();
				$event->trigger('error', array('app' => $this));
				$this->controlAction(
					$this->prepareController($this->errorController),
					'error'.$error,
					array($exception)
				);
				$this->display();
			} catch(\Exception $e) {
				if($error !== 500) {
					$this->throwError(500, $e);
				} else {
					$this->crash($e);
				}
			}
		} else {
			$this->crash($exception);
		}
	}
	
	protected function crash(\Exception $exception) {
		try {
			$event	= $this->getEvent();
			$event->trigger('die', array('app' => $this));
		} catch(\Exception $exception) {
			
		}
		var_dump('Error: '.$exception->getMessage(), 'File:'.$exception->getFile(), 'Line: '.$exception->getLine());
		die('Internal Application Error');
	}
	
	public function setErrorController($name) {
		$this->errorController	= $name;
	}
	
	protected function prepareController($name) {
		$class	= '\Controller\\'.$name;
		
		if(!class_exists($class, false) && !spl_autoload_call($class) && !class_exists($class, false)) {
			throw new Controller\Exception('Error 404: controller "'.$name.'" not found');
		}
		
		return	new $class($this);
	}
	
	protected function controlAction($controller, $action, $args = array()) {
		try {
			$reflection	= new \ReflectionMethod($controller, $action); 
		} catch(\Exception $e) {
			throw new Controller\Exception('Error 404: action "'.$action.'" not found in "'.get_class($controller).'" controller');
		}
		if(!$reflection->isPublic()) {
			throw new Controller\Exception('Error 404: action "'.$action.'" must be public in "'.get_class($controller).'" controller');
		}
		return	call_user_func_array(array($controller, $action), $args);
	}
}

?>