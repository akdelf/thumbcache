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


	if (defined(IMGCACHE)) {
        $sfolder = IMGCACHE;
    }
    else
        $sfolder = $_SERVER['DOCUMENT_ROOT'].'/';

    if (defined(IMGLINK))
        $httplink = IMGLINK;
    else
        $httplink = '';

	
	if ($save == '') { // default pub/images
		$link = 'pub/images/preview/'.$width.'/'.$height.'/'.md5($file).'.jpg';
		$httplink .= $link;
		$save = $sfolder.$link;
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

    if (extension_loaded("vips")){
        $status = thumbcache_vips($file, $save, $width, $height, $type);
    }

    /*elseif (class_exists('Imagick')) # Imagick
		$status = thumbcache_im($file, $save, $width, $height, $type);
	elseif (extension_loaded('gd')) # gd
    	$status = thumbcache_gd($file, $save, $width, $height, $type);*/

	if ($status)
		return $httplink;


	return '';


}	



function thumbcache_manager($file, $params = array(), $fsave = null, $return = False){


    $currlib = thumbcache_findlib();
    $tfunc = 'thumbcache_'.$currlib;

    if ($fsave !== ''){

        $newdir = dirname($fsave);

        if (!is_dir($newdir)){
            $old = umask(0);
            mkdir($newdir,  0777, True);
            chmod($newdir, 0777);
            umask($old);
        }

    }

    $opt = $params;

    if (!isset($params['height'])){
        $opt['height'] = null;
    }

    if (function_exists($tfunc)) {
         $result =  $tfunc($file, $opt, $fsave, $return);

         if ($fsave !== null)
             file_put_contents($fsave, $result);

         if ($return)
             return $result;
         else
             return true;


    }
    else
        return False;

}




/**
 * @return string current image libary in system
 */
function thumbcache_findlib(){

    if (extension_loaded("vips"))
        return 'vips';

    elseif (class_exists('Imagick'))
        return 'imagick';

    elseif (class_exists('gd'))
        return 'gd';

}



function thumbcache_vips($src, $opt = array(), $fsave = null, $return = true) {

    $width = $opt['width'];
    unset($opt['width']);

    if (isset($opt['crop']))
        $opt['crop'] = (int)$opt['crop'];
    else
        $opt['crop'] = 0;


    $img = vips_call('thumbnail', null, $src, $width, $opt)['out'];

    if($img === null) {
        echo vips_error_buffer()."\n";
        return False;
    }


    $ext = pathinfo($src, PATHINFO_EXTENSION);
    $result = vips_image_write_to_buffer($img, '.'.$ext)["buffer"];


    return $result;

}


/**
*
*/
function thumbcache_imagick($src, $opt = array(), $fsave = null, $return = true) {


    /** if (substr($src, 0, 4) == 'http'){
    	$simage = file_get_contents($src);
        $im = new Imagick();
        $im->readimageblob($simage);
    }
    else {
        $im = new Imagick($src);
    }*/

    $width = $opt['width'];

    if (isset($opt['height'])){
        $height = $opt['height'];
    }
    else {
        $height = null;
    }

    if ($opt['crop'] == true)
        $type = 'crop';


    $im = new Imagick($src);

	if ($type == 'crop')
		$im->cropThumbnailImage($width, $height);
	else
        $im->thumbnailImage($width, $height, true);

	/**elseif ($type == 'fit')
		$im->thumbnailImage($width, $height, true);

	elseif ($type == 'width'){
        $height = null;
        $im->thumbnailImage($width, $height);
    }

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
	}*/



	$result =  $im->getImageBlob();
	$im->destroy();

	return $result;

}


/**
*
*/
function thumbcache_gd($src, $width, $height = null, $fsave = null, $type = 'crop') {

	
	
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
        $result = imagejpeg($destination_resource, $fsave);
    }
    else { # gif, png
        $function = "image$typestr";
        $result =  $function($destination_resource, $destination_path);
    }
    
    imagedestroy($destination_resource);
    imagedestroy($src_resource);

    return $result;

}


function thumbcache_resizeToWidth($width) {
    $ratio = $width / $this->getWidth();
    $height = $this->getheight() * $ratio;
    $this->resize($width,$height);
}








