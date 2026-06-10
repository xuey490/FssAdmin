<?php
return [
	'env'=>'local',
    'name' => 'MyApp',
    'debug' => env('APP_DEBUG', true),
    'dir_permission' => '0755',
	'time_zone' =>"Asia/Shanghai",
	'url' =>  env('APP_URL', 'http://localhost:8000'),

];