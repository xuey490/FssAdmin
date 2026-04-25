<?php

declare(strict_types=1);

namespace App\Services;

use App\Dao\SysMailLogDao;
use Framework\Basic\BaseService;

class MailLogService extends BaseService
{
    protected SysMailLogDao $mailLogDao;

    public function __construct()
    {
        parent::__construct();
        $this->mailLogDao = new SysMailLogDao();
    }

    public function getPageList(array $params): array
    {
        return $this->mailLogDao->getPageList($params);
    }

    public function delete(array $ids): int
    {
        return $this->mailLogDao->deleteByIds($ids);
    }
}
