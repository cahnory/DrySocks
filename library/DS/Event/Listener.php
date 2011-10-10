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
 * @package    DS\Event
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS\Event;

/**
 * Event Listener
 *
 * @category   DS
 * @package    DS\Event
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Listener implements ListenerInterface
{
	private	$callback;
	private	$arguments;
	
	/**
	 *	Set the callback
	 *
	 *	If $helper is null and a class called [$name]Helper exists,
	 *	it will be instanciated with DrySocks instance as single argument
	 *
	 *	@param callback $callback collable function
	 *
	 *	@return	void
	 *	@throws Exception $callback is not callable
	 *
	 *	@access public
	 */
	public   function	__construct($callback) {
		if(!is_callable($callback))
			throw new \Exception($callback.' is not a valid callback');
		
		$this->callback	=	$callback;
		$this->arguments	=	func_num_args() > 1
							?	(array)func_get_arg(1)
							:	array();
	}
	
	/**
	 *	Call the callback
	 *
	 *	@return	mixed
	 *
	 *	@access public
	 */
	public	function	call()
	{
		return	call_user_func_array($this->callback, $this->arguments);
	}
}

?>