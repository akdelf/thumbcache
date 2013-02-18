<?php


/**
*
*
*/
function thumbcache($file, $width, $height, $type = 'crop') {

	/**
	* $_SERVER['DOCUMENT_ROOT']
	* $_SERVER['HTTP_HOST']
	*/	
	
	if (substr($file, 0, 4) == 'http') # http file
		$newfile = str_replace('http://', '', $newfile);
	elseif (!file_exists($file)) # local file
		return '';

	$newfile = $width.'/'.$height.'/'.md5($file);	

	if (defined('IMGCACHE')) {
		if (substr($file, 0, 4) == 'http')
			$newf = IMGCACHE.$newfile;
	}	

	if (defined('IMGLINK'))
		$result = IMGLINK.$newfile; # return link to preview
	else
		$result = $newf; # return path to thumbnail file
	
	if (file_exists($newf) and filectime($newf) > filectime($file))
		return $result;

	$newdir = dirname($newf);

	if (!is_dir($newdir)){
		if (!mkdir($newdir, 0775, True))
			return False;
	}

	if (class_exists('Imagick')) # Imagick
		if (thumbcache_im($file, $newf, $width, $height, $type))
			return $result;

	if (extension_loaded('gd')) # gd
		if (thumbcache_gd($file, $newf, $width, $height, $type))
			return $result;

	return '';

}	
		


/**
*
*/
function thumbcache_im($src, $newf, $width, $height, $type) {
		
	$handle = fopen($src, 'rb');

	$im = new Imagick();
	$im->readImageFile($handle); exit;

	if ($type == 'crop')
		$im->cropThumbnailImage($width, $height);
	elseif ($type == 'fit')
		$im->thumbnailImage($width, $height, true);
	elseif ($type == 'proportion') {	
		$m_width = (float) $width;
		$m_height = (float) $height;
		$curr_width = $im->getImageWidth();
		$curr_height = $im->getImageHeight();
		if (($m_width < $curr_width ) or ($m_height < $curr_height)){
			$w_k = $curr_width/$m_width;
			$h_k = $curr_height/$m_height;
			if ($w_k > $h_k){
				$new_width = $m_width;
				$new_height = $curr_height/$w_k;
			}
			else {
				$new_width = $curr_width/$h_k;
				$new_height = $m_height;
			}
			$im->resizeImage($new_width, $new_height, imagick::FILTER_LANCZOS, 1); 
		}
	}	


	if ($im->writeImage($newf))
		return True;

	return  False;


}	


/**
*
*/
function thumbcache_gd($src, $newf, $width, $height) {
	return False;
}		



