<?php

// config/log.php
return [
    'log_channel' => 'app',
    'log_path' => __DIR__ . '/../storage/logs',
    'log_size' => 5242880,	//5MB 5*1024*1024 =5242880
    'log_keep_days' => 30,	//30天
    'log_date_format' => 'Y-m-d H:i:s',	//日期格式

];