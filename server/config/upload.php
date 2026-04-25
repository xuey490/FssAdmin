<?php

// config/upload.php
return [
    'upload_dir' => dirname(__DIR__) .'/public/uploads',
    'max_size' => 5 * 1024 * 1024, // 5MB
    'whitelist_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'docx', 'txt'],
    'blacklist_extensions' => ['php', 'php5', 'phtml', 'exe', 'sh'],
    'naming' => 'uuid', // 'original', 'uuid', 'datetime', 'md5'
];