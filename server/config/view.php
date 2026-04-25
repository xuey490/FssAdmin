<?php

// config/view.php
return [
	'driver' => 'Think', //Twig or Think
	'Twig' =>[
		'paths' => [
			dirname(__DIR__) . '/resource/view',
			dirname(__DIR__) . '/resource/acme/blog', 		// 第三方模块模板
			//dirname(__DIR__) . '/framework/View/templates', 	// 第三方模块模板
			// 可添加更多
		],
		'cache_path' =>  dirname(__DIR__) . '/storage/view', //false 不缓存
		'debug' => $_ENV['APP_DEBUG'] ?? true,
		'auto_reload' => true,
		'strict_variables' => false, //$_ENV['APP_DEBUG'] ?? true, //严格变量检查，慎用
		// 不在这里设 cache，我们用下面的方式传
	],
	'Think' => [
		// 模板引擎类型使用Think
		'type' => 'Think',
		// 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
		'auto_rule' => 1,
		'view_path'	    =>	dirname(__DIR__) . '/resource/view/',
		'cache_path'	=>	dirname(__DIR__). '/storage/view/',
		// 模板目录名
		'view_dir_name' => 'view',
		// 模板后缀
		'view_suffix' => 'html',
		// 模板文件名分隔符
		'view_depr' => DIRECTORY_SEPARATOR,
		// 模板引擎普通标签开始标记
		'tpl_begin' => '{',
		// 模板引擎普通标签结束标记
		'tpl_end' => '}',
		// 标签库标签开始标记
		'taglib_begin' => '{',
		// 标签库标签结束标记
		'taglib_end' => '}',
		//默认的过滤方法
		'default_filter' => 'htmlspecialchars',
		'tpl_replace_string' => array(
			/*
			'{__STATIC__}' => '/static',
			'{__ASSETS__}' => '/static/assets',
			'{__ADMIN__}' => '/static/admin',
			'{__CSS__}' => '/static/home/css',
			'{__JS__}' => '/static/home/js',
			'{__IMG__}' => '/static/home/images'
			*/
		),
	],

];
