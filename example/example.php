<?php

	define('IMGPATH', __DIR__.'/images/original/'); //папка где лежат картинки
	define('IMGCACHE', __DIR__.'/images/preview/');

	require '../thumbcache.php';

	echo thumbcache('popas.jpg', 200, 200);
