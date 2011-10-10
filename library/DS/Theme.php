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
class Theme implements ThemeInterface
{
	private $css	= array();
	
	/*public function __construct(\DS\) {
		
	}*/
	
	public function getCss($name) {
		if(!array_key_exists($name, $this->css)) {
			if($this->css[$name]['css'] === null) {
				$this->css[$name]['css']	=	new Theme\Css;
			}
			return	$this->css[$name];
		}
	}
	
	public function getCssMedia($name) {
		return	array_key_exists($name, $this->css)
			?	$this->css[$name]['media']
			:	null;
	}
	
	public function setCss($name, $media = 'all') {
		$this->css[$name]	= array(
			'css'	=> null,
			'media'	=> $media
		);
	}
	
	public function unsetCss($name) {
		if(!array_key_exists($name, $this->css)) {
			unset($this->css[$name]);
		}
	}
	
	public function listCss() {
		return	$this->css;
	}
	
	public function prepare() {
		/*********************
		
		$this->setCss('main');
		
		*********************/
	}
	
	public function define() {
		/*********************
		
		$main	= $this->getCss('main');
		$main->addFile('someCssFile.css');
		$main->addFile('someCssFontDefinition.css');
		
		*********************/
	}
	
	public function dress() {
		
	}
}

?>