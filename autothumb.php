<?php

define('IMGPATH', 'pub/images'); //папка где лежат картинки
define('IMGCACHE', 'pub/images/preview/');


function thumb($src, $width, $height, $type = 'crop') {

	if (!file_exists($file)) return '';

	$file = new fileinfo($src);
	$newf = $file->name.'_'.$width.'_'.$height.'.'.$file->ext;

	$dir = dirname($file);
	$newdir = IMGCACHE.str_replace(IMGPATH, '', $dir);
	$newf = $newdir.$newf;

	if (file_exists($newf) and filectime($newf) > filectime($src))
		return $link;









}

