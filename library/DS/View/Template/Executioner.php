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
 * @package    DS\View\Template
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS\View\Template;

/**
 * @category   DS
 * @package    DS\View\Template
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Executioner
{
	protected $template;
	
	public function __construct(\DS\View\Template $template) {
		$this->template	= $template;
	}
	
	/**
	 *	Execute the template in a safe context
	 * 
	 *	@param stdClass         $definition Definition of the template to execute
	 *	@param DS\View\Template $template   Execution invoker
	 *
	 *	@return	string execution result
	 *
	 *	@access protected
	 */
	public function __invoke(\stdClass $definition, \DS\View\Template $template) {
		if($definition !== $template->getExecuted()) {
			throw new \Exception('Execution definition isn\'t valid');
		}
		
		$this->template	= $template;
		ob_start();
		try {
		// Template file
			if($definition->type === 'file') {
				extract($definition->data);
				include	func_get_arg(0)->template;
				
			// Template string
			} else {
				extract($definition->data);
				eval('?>'.func_get_arg(0)->template);
			}
		} catch(\exception $exception) {
			ob_clean();
			$this->template	= func_get_arg(1);
			throw $exception;
		}
		
		// Reset template, just in case ;)
		$this->template	= func_get_arg(1);
		return	ob_get_clean();
	}
	
	/**
	 * Returns the DS\View\Template value at the specified offset
	 * 
	 * When a template is executed, it could access and override
	 * the $template var. In this case, a notice could be sent.
	 * After execution, the $template var is always restored.
	 * 
	 * @param mixed $offset the offset with the value
	 * 
	 * @return mixed the value at the specified offset or null
	 * 
	 * @access public
	 */
	public function __get($offset) {
		return	$this->template->$offset;
	}
	
	/**
	 * Sets the DS\View\Template value at the specified offset
	 * 
	 * @param mixed $offset the offset being set
	 * @param mixed $value  the new value for the offset
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function __set($offset, $value) {
		$this->template->$offset	= $value;
	}
	
	/**
	 * Call a DS\view\Template method
	 * 
	 * @param mixed $offset the offset being set
	 * @param mixed $value  the new value for the offset
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function __call($name, $attr) {
		if(!is_callable(array($this->template, $name))) {
			throw new \Exception('"'.$name.'" is not a valid DS\View\Template method');
		}
		return	call_user_func_array(array($this->template, $name), $attr);
	}
}

?>