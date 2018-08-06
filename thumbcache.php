<?php
/**
*
*/

function thumbcache($file, $width, $height = null, $save = '', $type = 'crop') {

    /**
	* IMAGECACHE, IMAGELINK 
	* $_SERVER['DOCUMENT_ROOT']
	* $_SERVER['HTTP_HOST']
	*/	

	if (substr($file, 0, 4) == 'http') # http file
		$newfile = str_replace(array('http://', 'http://www.'), '', $newfile);
	elseif (!file_exists($file)) # local file
		return '';

	
	if ($save == '') { // default pub/images
		$link = 'pub/images/preview/'.$width.'/'.$height.'/'.md5($file).'.jpg';
		$httplink = IMGLINK.'/'.$link;
		$save = $_SERVER['DOCUMENT_ROOT'].'/'.$link;
	}


	if (file_exists($save)){
		if (!file_exists($file))
			return $httplink;
		elseif (filectime($save) > filectime($file))
			return $httplink;
	}

	
	/*if (!file_exists($file)) {
	
		$sdir = dirname($file);
	
		if (!is_dir($sdir))
		 	$file = $_SERVER['DOCUMENT_ROOT'].$file;

		if (!file_exists($file))
			return ''; 
	
	}*/


	$newdir = dirname($save);


	if (!is_dir($newdir)){
		$old = umask(0);
		mkdir($newdir,  0777, True);
		chmod($newdir, 0777);
		umask($old);
	}

	//$status = thumbcache_gd($file, $save, $width, $height, $type);
	
	if (class_exists('Imagick')) # Imagick
		$status = thumbcache_im($file, $save, $width, $height, $type);
	elseif (extension_loaded('gd')) # gd
		$status = thumbcache_gd($file, $save, $width, $height, $type);

	if ($status)
		return $httplink;


	return '';


}	



/**
*
*/
function thumbcache_im($src, $newf, $width, $height, $type) {

	

    if (substr($src, 0, 4) == 'http'){
    	$simage = file_get_contents($src);
        $im = new Imagick();
        $im->readimageblob($simage);   
    }
    else {
        $im = new Imagick($src);
    }


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
	

	$result = $im->writeImage($newf);

	$im->destroy();

	

	return $result;


}	


/**
*
*/
function thumbcache_gd($src, $newf, $newwidth, $newheight = null, $type = 'crop') {

	
	
	ini_set("gd.jpeg_ignore_warning", 1); // иначе на некотоых jpeg-файлах не работает
    
    list($oldwidth, $oldheight, $type) = getimagesize($src);

    switch ($type) {
        case IMAGETYPE_JPEG: $typestr = 'jpeg'; break;
        case IMAGETYPE_GIF: $typestr = 'gif' ;break;
        case IMAGETYPE_PNG: $typestr = 'png'; break;
	default: return '';
    }
    
    $function = "imagecreatefrom$typestr";
    
    if (!function_exists($function))
    	return '';	
    
    $src_resource = $function($src);
   
    if (!$newheight) { $newheight = round($newwidth * $oldheight/$oldwidth); }
    elseif (!$newwidth) { $newwidth = round($newheight * $oldwidth/$oldheight); }
    $destination_resource = imagecreatetruecolor($newwidth,$newheight);
    
    imagecopyresampled($destination_resource, $src_resource, 0, 0, 0, 0, $newwidth, $newheight, $oldwidth, $oldheight);
    
    if ($type = 2) { # jpeg
        imageinterlace($destination_resource, 1); // чересстрочное формирование изображение
        $result = imagejpeg($destination_resource, $newf);      
    }
    else { # gif, png
        $function = "image$typestr";
        $result =  $function($destination_resource, $destination_path);
    }
    
    imagedestroy($destination_resource);
    imagedestroy($src_resource);

    return $result;

}








