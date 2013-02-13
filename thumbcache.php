<?php

define('IMGPATH', 'pub/images'); //папка где лежат картинки
define('IMGCACHE', 'pub/images/preview/');


function autothumb($src, $width, $height, $type = 'crop') {

	if (!file_exists($file)) return '';

	$file = new fileinfo($src);
	$newf = $file->name.'_'.$width.'_'.$height.'.'.$file->ext;

	$dir = dirname($file);
	$newdir = IMGCACHE.str_replace(IMGPATH, '', $dir);
	$newf = $newdir.$newf;

	if (file_exists($newf) and filectime($newf) > filectime($src))
		return $link;

	if (!is_dir($newdir)){
		if (!mkdir($newdir, 0775, True))
			return False;
		}
	}


	if (class_exists('Imagick'))
		return autothumb_im();
	

	if(extension_loaded('gd'))
		return autothumb_gd();

	return '';

}	
		


	/*
	*
	*/
	function autothumb_im() {
		
		$im = new Imagick();

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



		if ($im->writeImage($dest))
			return $link;

		return  '';


	}	


	function autothumb_gd() {
	
	}		



}

