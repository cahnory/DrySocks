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
	 *	Send the template to execution
	 * 
	 *	@param string           $exe      template file/string to execute
	 *	@param string           $type     template type (file or string)
	 *	@param array            $data     data sent to the template
	 *
	 *	@return	string execution result
	 *
	 *	@access public
	 */
	public function execute($exe, $type, array $data) {
		return	$this->doExecution($exe, $type, $data, $this->template);
	}
	
	/**
	 *	Execute the template in a safe context
	 * 
	 *	@param string           $exe      template file/string to execute
	 *	@param string           $type     template type (file or string)
	 *	@param array            $data     data sent to the template
	 *	@param DS\View\Template $template
	 *
	 *	@return	string execution result
	 *
	 *	@access protected
	 */
	protected function doExecution($exe, $type, array $data, \DS\View\Template $template) {
		if($this->template !== $template) {
			throw new \Exception('Provided DS\View\Template must be the same as executioner\'s one');
		}
		
		ob_start();
		
		// Template file
		if($type === 'file') {
			extract($data);
			include	func_get_arg(0);
			
		// Template string
		} else {
			extract($data);
			eval('?>'.func_get_arg(0));
		}
		
		// Reset template, just in case ;)
		$this->template	= func_get_arg(3);
		return	ob_get_clean();
	}
	
	public function __get($name) {
		return	$this->template->$name;
	}
	
	public function __set($name, $value) {
		$this->template->$name	= $value;
	}
	
	public function __call($name, $attr) {
		return	call_user_func_array(array($this->template, $name), $attr);
	}
}

?>