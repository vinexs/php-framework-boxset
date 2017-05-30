<?php

$SETTING['page_size'] = 30;

$SETTING['table_prefix'] = 'cms_';

$SETTING['db_source'] = 'test_db';

$SETTING['menu'] = array(
	'news' => array(
		'text' => 'nav_news',
		'desc' => 'nav_news_desc',
		'class_file' => 'DBnews',
		'icon_class' => 'fa fa-newspaper-o',
	),
	'photo' => array(
		'text' => 'nav_photo',
		'desc' => 'nav_photo_desc',
		'class_file' => 'DBPhoto',
		'icon_class' => 'fa fa-picture-o',
	),
);