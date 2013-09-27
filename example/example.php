<?php

	define('IMGPATH', __DIR__.'/images/original/'); //папка где лежат картинки
	define('IMGCACHE', __DIR__.'/images/preview/');
	defined('IMGLINK', 'http://argumenti.ru/');

	require '../thumbcache.php';

	echo 'preview: '.thumbcache(IMGPATH.'popas.jpg', 200, 200)."\n\n";
	echo 'preview: '.thumbcache('http://argumenti.ru/images/preview/arhnews/4b6f1d126ef7b13e36b00f221b3e0e02.jpg', 230, 230)."\n\n";

