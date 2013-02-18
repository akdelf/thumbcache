<?php

	//define('IMGPATH', __DIR__.'/images/original/'); //папка где лежат картинки
	define('IMGCACHE', __DIR__.'/images/preview/');

	require '../thumbcache.php';

	//echo thumbcache('popas.jpg', 200, 200);
	
	echo thumbcache('http://argumenti.ru/images/preview/arhnews/4b6f1d126ef7b13e36b00f221b3e0e02.jpg', 200, 200);

