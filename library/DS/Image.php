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
 * @package    DS\Image
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */

namespace DS;

/**
 * Class for create and edit images
 *
 * @category   DS
 * @package    DS\Image
 * @author     François "cahnory" Germain <cahnory@gmail.com>
 * @copyright  Copyright (c) 2011 François "cahnory" Germain
 * @license    http://www.opensource.org/licenses/mit-license.php
 */
class Image
{
	private	$ressource;
	private	$height;
	private	$width;
	private $type;
	private $filename;
	const reframe	= 'reframe';
	const resize	= 'resize';
	const crop		= 'crop';
	
	public function __construct($width = 1, $height = 1) {
		$this->ressource	= $this->_getEmptyRessource($width, $height);
		$this->width		= $width;
		$this->height		= $height;
	}
	
	public function __clone() {
		$ressource	= $this->_getEmptyRessource($this->width, $this->height);
		imagecopy($ressource, $this->ressource, 0, 0, 0, 0, $this->width, $this->height);
		$this->ressource	= $ressource;
	}
	
	protected function _swapRessources($ressource) {
		imagedestroy($this->ressource);
		$this->ressource	= $ressource;
	}
	
	protected function _getEmptyRessource($width, $height) {
		$ressource	= imagecreatetruecolor($width, $height);
		$this->_prepareRessource($ressource);
		
		return	$ressource;
	}
	
	protected function _prepareRessource($ressource) {
		if($this->ressource && ($index = imagecolortransparent($this->ressource)) != -1) {
			$color	= imagecolorsforindex($this->ressource, $index);
			$index	= imagecolorallocate($ressource, $color['red'], $color['green'], $color['blue']);
			$index	= imagecolortransparent($ressource, $index);
		} else {
			imagealphablending($ressource, false);
			imagesavealpha($ressource, true);
		}
	}
	
	public function load($filename) {
		//	Le fichier est introuvable/lisible
		if(!is_readable($filename)) {
			throw new \Exception('Image file '.$filename.' was not found or is not readable');
		}
		
		// Le fichier ne semble pas être une image
		if(!$type = exif_imagetype($filename)) {
			throw new \Exception('File '.$filename.' is not a valid image');
		}
		
		// Recherche du type d'image
		if($type === IMAGETYPE_GIF) {
			$this->ressource	=	imagecreatefromgif($filename);
			$this->type			=	'gif';
		} elseif($type === IMAGETYPE_JPEG) {
			$this->ressource	=	imagecreatefromjpeg($filename);
			$this->type			=	'jpeg';
		} elseif($type === IMAGETYPE_PNG) {
			$this->ressource	=	imagecreatefrompng($filename);
			$this->type			=	'png';
		} elseif($type === IMAGETYPE_WBMP) {
			$this->ressource	=	imagecreatefromwbmp($filename);
			$this->type			=	'wbmp';
		} elseif($type === IMAGETYPE_XBM) {
			$this->ressource	=	imagecreatefromxbm($filename);
			$this->type			=	'xbm';
		} else {
			//	Type d'image non pris en charge
			throw new \Exception('Image type '.$type.' is not handled');
		}
		
		$this->_prepareRessource($this->ressource);
		$this->width	= imagesx($this->ressource);
		$this->height	= imagesy($this->ressource);
		$this->filename	= $filename;
		
		// Copy in a blank ressource of the same size
		$this->resize($this->width, $this->height);
	}
		
	public function save($filename, $type = NULL) {
		// File isn't writable
		if((is_file($filename) && !is_writable($filename)) || !is_writable(dirname($filename))) {
			throw new \Exception('Image couldn\'t be saved to '.$filename);
		}
		
		// Define image type
		if($type === NULL) {
			if(preg_match('#(?<=[^./]\.)[^./]+$#', $filename, $type)) {
				$type	=	strtolower($type[0]);
			} else {
				$type	=	'jpeg';
			}
		}
		
		// Save image
		$saved	=	false;
		if($type === 'gif') {
			$saved	=	imagegif($this->ressource, $filename);
		} else {
			$args	=	array_merge(array($this->ressource), func_get_args());
			if($type === 'png') {
				$saved	=	call_user_func_array('imagepng', $args);
			} elseif($type === 'wbmp' || $type === 'wbm') {
				$saved	=	call_user_func_array('imagewbmp', $args);
			} elseif($type === 'xbm') {
				$saved	=	call_user_func_array('imagexbm', $args);
			} else {
				$saved	=	call_user_func_array('imagejpeg', $args);
			}
		}
		
		// Image couldn't be saved
		if(!$saved) {
			throw new \Exception('Image of type '.$type.' couldn\'t be saved as '.$filename);
		}
		
		// Update image object
		$this->type		= $type;
		$this->filename	= $filename;
	}
	
	public function getType() {
		return	$this->type;
	}
	
	public function crop($width, $height = NULL, $x = 0, $y = 0)
	{
		if($height === NULL) {
			$height	=	$width;
		}
		$croped	= $this->_getEmptyRessource($width, $height);
		if(!imagecopyresampled($croped, $this->ressource, 0, 0, $x, $y, $width, $height, $width, $height)) {
			// Crop failed
			throw new \Exception('Image couldn\'t be croped');
		}
		$this->_swapRessources($croped);
		$this->width		= $width;
		$this->height		= $height;
	}
		
	public function resize($width, $height)
	{
		$resized	= $this->_getEmptyRessource($width, $height);
		if(!imagecopyresampled($resized, $this->ressource, 0, 0, 0, 0, $width, $height, $this->width, $this->height)) {
			// Resize failed
			throw new \Exception('Image couldn\'t be resized');
		}
		
		// Update image object
		$this->_swapRessources($resized);
		$this->width		= $width;
		$this->height		= $height;
	}
	
	public function scale($coef) {
		$this->resize($this->width * $coef, $this->height * $coef);
	}
	
	public function xScale($coef) {
			$this->resize($this->width * $coef, $this->height);
	}
	
	public function yScale($coef) {
			$this->resize($this->width, $this->height * $coef);
	}
	
	public function width($width = NULL, $homothetic = false) {
		if(!$width) {
			return	$this->width;
		}
		if($homothetic) {
			$coef	= $width / $this->width;
			$this->resize($width, round($this->height * $coef));
		} else {
			$this->resize($width, $this->height);
		}
	}
	
	public function height($height = NULL, $homothetic = false) {
		if(!$height) {
			return	$this->height;
		}
		if($homothetic) {
			$coef	= $height / $this->height;
			$this->resize(round($this->width * $coef), $height);
		} else {
			$this->resize($this->width, $height);
		}
	}
	
	public function fit($width, $height = NULL, $overflow = false) {
		if($height === NULL) {
			$height	= $width;
		} elseif(is_bool($height)) {
			$overflow	= $height;
			$height		= $width;
		}
		$x	= $width / $this->width;
		$y	= $height / $this->height;
		if($overflow) {
			$c	= $x > $y ? $x : $y;
		} else {
			$c	= $x < $y ? $x : $y;
		}
		$this->scale($c);
		$this->crop($width, $height, round(($this->width - $width) / 2), round(($this->height - $height) / 2));
	}
		
	public function rotate($angle, $backgroundColor = NULL, $mode = 'crop') {
		if($backgroundColor === NULL && ($backgroundColor = imagecolortransparent($this->ressource)) == -1) {
			$backgroundColor	= imagecolorallocatealpha($this->ressource, 0, 0, 0, 127);
		}
		if(!$rotated = imagerotate($this->ressource, -$angle%360, $backgroundColor)) {
			// Rotation failed
			throw new \Exception('Image couldn\'t be rotated');
		}
		$this->_prepareRessource($rotated);
		$this->_swapRessources($rotated);
		
		//	Reframe
		if($mode == self::reframe) {
			$this->width	= imagesx($this->ressource);
			$this->height	= imagesy($this->ressource);
		
		//	Resize
		} elseif($mode == self::resize) {
			$width	= $this->width;
			$height	= $this->height;
			$this->width	= imagesx($this->ressource);
			$this->height	= imagesy($this->ressource);
			$this->resize($width, $height);
		
		//	Crop
		} else {
			$this->crop($this->width, $this->height, round((imagesx($this->ressource) - $this->width) / 2), round((imagesy($this->ressource) - $this->height) / 2));
		}
	}
}

?>