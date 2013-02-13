<?php



/*
*
*/
function thumbcache($file, $width, $height, $type = 'crop') {

	
	$pathfile = IMGPATH.$file;
	
	if (!file_exists($pathfile))
		return '';

	$newfile = $width.'/'.$height.'/'.$file;

	$newf = IMGCACHE.$newfile;
	$link = IMGLINK.$newfile;

	/*if (file_exists($newf) and filectime($newf) > filectime($src))
		return $link;*/
	
	$newdir = dirname($newf);

	if (!is_dir($newdir)){
		if (!mkdir($newdir, 0775, True))
			return False;
	}


	if (class_exists('Imagick')) # Imagick
		if (thumbcache_im($pathfile, $newf, $width, $height, $type))
			return $link;

	if (extension_loaded('gd')) # gd
		if (thumbcache_gd($pathfile, $newf, $width, $height, $type))
			return $link;

	return '';

}	
		


/*
*
*/
function thumbcache_im($src, $newf, $width, $height, $type) {
		
	$im = new Imagick();

	$im->readImage($src);

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


function thumbcache_gd($src, $newf, $width, $height) {
	return False;
}		



