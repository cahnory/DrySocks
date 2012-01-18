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
 * @package    DS\Ticket
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2012 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * DrySocks ticket
 *
 * Manage an unique form ticket in order to, for example,
 * prevent double posting on refresh.
 *
 * Here is a simple exemple:
 * <code>
 *
 * // Get/create your ticket manager 
 * if(array_key_exists('ticket', $_SESSION)) {
 *     $t  = $_SESSION['ticket'];
 * } else {
 *     $t  = $_SESSION['ticket'] = new \DS\Ticket;
 * }
 *
 * // Validate the input ticket
 * if($t->validate($_POST['ticket'])) {
 *
 *     // If your form is validated
 *     if([…]) {
 *
 *         // use data, for ex., store in db
 *         $t->delete($_POST['ticket']);
 *
 *         return 'Some success message, var…';
 *     }
 *
 * // Get a new valid ticket
 * } else {
 *     $_POST['ticket']    = $t->get();
 * }
 *
 * return 'Your form, filled with posted values (containing the ticket)';
 *
 * </code>
 *
 * @category   DS
 * @package    DS\Ticket
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2012 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Ticket
{
	protected $tickets = array();

	/**
	 * Generate a ticket id
	 * 
	 * @return string ticket id
	 * 
	 * @access public
	 * @static
	 */
	static function generate() {
		return	uniqid(rand(), true);
	}

	/**
	 * Return a new valid ticket id
	 * 
	 * @return string ticket id
	 * 
	 * @access public
	 */
	public function get() {
		while(array_key_exists($ticket = self::generate(), $this->tickets));
		$this->tickets[$ticket] = true;
		return	$ticket;
	}

	/**
	 * Delete a ticket
	 *
	 * @param string $ticket the ticket id
	 * 
	 * @return void
	 * 
	 * @access public
	 */
	public function delete($ticket) {
		$this->tickets[$ticket] = false;
	}

	/**
	 * Validate a ticket
	 *
	 * @param string $ticket the ticket id
	 * 
	 * @return bool if the ticket is valid
	 * 
	 * @access public
	 */
	public function validate($ticket) {
		return	array_key_exists($tickets, $this->tickets) && $this->tickets[$ticket];
	}
}

?>