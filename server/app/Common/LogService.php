<?php

declare(strict_types=1);

// App/Common/LogService.php#
namespace App\Common;

class LogService {
    public function info(string $msg) {
        dump( "[LOG]: $msg <br>");
    }
}