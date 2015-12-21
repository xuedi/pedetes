<?php
namespace Pedetes\core;

use \PDO;

//TODO: instead or returning null, fail with errors
class core_upload_model extends \Pedetes\model {


	function __construct($ctn) {
		parent::__construct($ctn);
		$this->pebug->log( "core_upload_model::__construct()" );
	}


	public function save($name, $form, $path) {
		// normal file
	}


	public function delete($name, $path, $hash) {
		$aPath = $this->ctn['pathApp'].$path;
		if($handle = opendir($aPath)) {
			while(false!==$entry=readdir($handle)) {
				$strLen = strLen($name.$hash)+1;
				if(substr($entry, 0, $strLen)=="{$name}_{$hash}") {
					if(file_exists($aPath.$entry)) unlink($aPath.$entry);
				}
			}
			closedir($handle);
		}
		return $this->saveImage($name, $path);
	}


	public function getFileList($path) {
		$retVal = array();
		$aPath = $this->ctn['pathApp'].$path;
		if($handle = opendir($aPath)) {
			while(false!==$entry=readdir($handle)) {
				if($entry!="."&&$entry!="..") {
					$retVal[] = $entry;
				}
			}
			closedir($handle);
		}
		return $retVal;
	}

	//TODO:seperate thumbnain cration from upload
	public function saveImage($name, $path, $form="DUMMY", $thumbnails=null, $hash=null) {
		$types = array('image/png'=>'png','image/jpeg'=>'jpg','image/gif'=>'gif','image/bmp'=>'bmp','image/tiff'=>'tiff');
		$temp = $_FILES[$form]['tmp_name'];
		$path = $this->ctn['pathApp'].$path;
		if($name && $path) {

			// only when upload
			if($temp) {
				$base = $path.$name.'_'.hash_file('md5', $temp);
				$type = $types[getimagesize($temp)['mime']];
				$file = $base.'_orig.'.$type;
				if(file_exists($file)) unlink($file);
				move_uploaded_file($temp, $file);
			} 


			// no upload, just for thumb generation
			if(!$temp && $hash) {
				$base = $path.$name.'_'.$hash;
				$type = 'jpg'; // somehow guess without ending
				$file = $base.'_orig.'.$type;
			}

			// resize alternative versions
			if(!empty($thumbnails) && $base) {
				foreach($thumbnails as $value) {
					$mode = explode('_', $value)[0]; // r=resize, c=chop
					$size = explode('_', $value)[1];
					if(is_numeric($size)) {
						if($mode=='c') { 
							$this->imageToFile($this->imageChop($file, $size),"{$base}_{$size}.{$type}");
						} else {
							$this->imageToFile($this->imageResize($file, $size),"{$base}_{$size}.{$type}");
						}
					}
				}
			}

			// get imagelist independent of action (only 'orig' and type)
			$files = array();
			if($handle = opendir($path)) {
				while(false!==$entry=readdir($handle)) {
					$strLen = 1+strLen($name);
					if(substr($entry, 0, $strLen)=="$name"."_") {
						$entryParts = explode('_', $entry);
						if('orig'==substr($entryParts[2], 0, 4)) {
							$tmpHash = $entryParts[1];
							$files[$tmpHash] = explode('.', $entryParts[2])[1];
							$flag=1;
						}
					}
				}
				closedir($handle);
			}
			if(!empty($files)) return serialize($files);
			else return null;
		} else return null;
	}





	private function imageResize($inputFileName, $maxSize = 100) {
		$info = getimagesize($inputFileName);
 		$type = isset($info['type']) ? $info['type'] : $info[2];
 
		// Check support of file type
		if ( !(imagetypes() & $type) ) 
			return false;
 
		$width  = isset($info['width'])  ? $info['width']  : $info[0];
		$height = isset($info['height']) ? $info['height'] : $info[1];
 
		// Calculate aspect ratio
		$wRatio = $maxSize / $width;
		$hRatio = $maxSize / $height;
 
		// Using imagecreatefromstring will automatically detect the file type
		$sourceImage = imagecreatefromstring(file_get_contents($inputFileName));
 
		// Calculate a proportional width and height no larger than the max size.
		if( ($width <= $maxSize) && ($height <= $maxSize) ) {
			return $sourceImage;
		} elseif ( ($wRatio * $height) < $maxSize ) {
			$tHeight = ceil($wRatio * $height);
			$tWidth  = $maxSize;
		} else {
			$tWidth  = ceil($hRatio * $width);
			$tHeight = $maxSize;
		}
		$thumb = imagecreatetruecolor($tWidth, $tHeight);
 
		if ( $sourceImage === false ) {
			return false;
		}

 
		imagecopyresampled($thumb, $sourceImage, 0, 0, 0, 0, $tWidth, $tHeight, $width, $height);
		imagedestroy($sourceImage);
 
		return $thumb;
	}


	private function imageChop($inputFileName, $size = 100) {
		$info = getimagesize($inputFileName);
 		$type = isset($info['type']) ? $info['type'] : $info[2];
 
		// Check support of file type
		if ( !(imagetypes() & $type) ) {
			return false;
		}
 
		$width  = isset($info['width'])  ? $info['width']  : $info[0];
		$height = isset($info['height']) ? $info['height'] : $info[1];

 
		// Using imagecreatefromstring will automatically detect the file type
		$sourceImage = imagecreatefromstring(file_get_contents($inputFileName));
 


		// starting points
		if($width>=$height) {
			$src_w = $height;
			$src_h = $height;
			$src_x = ceil(($width-$height)/2);
			$src_y = 0;
		} else {
			$src_w = $width;
			$src_h = $width;
			$src_x = 0;
			$src_y = ceil(($height-$width)/2);
		}
		$thumb = imagecreatetruecolor($size, $size);


 
		if ( $sourceImage === false ) {
			return false;
		}

		//imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
		imagecopyresampled($thumb, $sourceImage, 0, 0, $src_x, $src_y, $size, $size, $src_w, $src_h);
		imagedestroy($sourceImage);
 
		return $thumb;
	}


	function imageToFile($im, $fileName, $quality = 80) {
		if( !$im || file_exists($fileName) ) {
		   return false;
		}
		$ext = strtolower(substr($fileName, strrpos($fileName, '.')));
		switch ( $ext ) {
			case '.gif':
				imagegif($im, $fileName);
				break;
			case '.jpg':
			case '.jpeg':
				imagejpeg($im, $fileName, $quality);
				break;
			case '.png':
				imagepng($im, $fileName);
				break;
			case '.bmp':
				imagewbmp($im, $fileName);
				break;
			default:
				return false;
		}
 
		return true;
	}




}
