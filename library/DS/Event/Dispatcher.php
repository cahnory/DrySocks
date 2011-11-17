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
 * Event dispatcher
 *
 * @category   DS
 * @package    DS\Event
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
 
class Dispatcher implements DispatcherInterface
{
	private	$size		=	0;
	private	$events	=	array();
	private	$names		=	array(array());

	/**
	 *	Bind event listener by name(space)
	 *
	 *	@param string $name event name(space)
	 *
	 *	@return	dsEventDispatcher
	 *
	 *	@access public
	 */
	public	function	bind($name, ListenerInterface $event)
	{
		$spaces		=	self::getNameStack($name);
		$size		=	self::getNameSize($name);
		$name		=	$spaces[0];
		
		//	Add the event name
		$this->names[0][]	=	$name;	
		$this->events[]	=	$event;
		
		//	Add each namespace chunks
		for($i = 1; $i < $size; $i++) {
			//	Prepare namespace array (create, pad)
			if(!isset($this->names[$i]))
				$this->names[$i]	=	array_pad(array(), $this->size, NULL);				
			if(sizeof($this->names[$i]) < $this->size)
				$this->names[$i]	=	array_pad($this->names[$i], $this->size, NULL);
			
			$name	.=	'.'.$spaces[$i];
			
			$this->names[$i][]	.=	$name;
		}
		
		$this->size++;
		
		return	$this;
	}

	/**
	 *	Unbind event by its name(space)
	 *
	 *	Remove all event listeners for a
	 *	name and its sub namespaces
	 *
	 *	@param string $name event name(space)
	 *
	 *	@return	dsEventDispatcher
	 *
	 *	@access public
	 */
	public	function	unbind($name)
	{
		$depth	=	self::getNameSize($name) - 1;
		
		if(isset($this->names[$depth])) {
			//	Corrects keys while splicing
			$removed	=	0;
			
			if($keys = array_keys($this->names[$depth], $name)) {
				foreach($keys as $key) {
					$key	-=	 $removed;
					array_splice($this->events, $key, 1);
					foreach($this->names as &$space) {
						array_splice($space, $key, 1);
					}
					$this->size--;
					$removed++;
				}
			}
		}
		
		return	$this;
	}

	/**
	 *	Trigger event
	 *
	 *	@param string $name event name(space)
	 *	@param array  $data event data
	 *
	 *	@return	dsEventDispatcher
	 *
	 *	@access public
	 */
	public	function	trigger($name, array $data = array())
	{
		$depth	=	self::getNameSize($name) - 1;
		
		if(isset($this->names[$depth])) {
			if($keys = array_keys($this->names[$depth], $name)) {
				foreach($keys as $key) {
					$this->events[$key]->call($data);
				}
			}
		}
	
		return	$this;
	}

	/**
	 *	Return the size of the name(space)
	 *
	 *	@param string $name event name(space)
	 *
	 *	@return	int size of the name(space)
	 *
	 *	@access protected
	 *	@static
	 */
	static	protected	function	getNameSize($name)
	{
		return	substr_count($name, '.') + 1;
	}

	/**
	 *	Return name(space) parts
	 *
	 *	@param string $name event name(space)
	 *
	 *	@return	array parts of the name(space)
	 *
	 *	@access protected
	 *	@static
	 */
	static	protected	function	getNameStack($name)
	{
		return	explode('.', $name);
	}
}

?>