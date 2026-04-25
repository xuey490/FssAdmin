<?php

declare(strict_types=1);

namespace Plugins\Bbs\Controllers;

use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;
use Framework\Attributes\Route;
use Symfony\Component\HttpFoundation\Request;

class IndexController extends BaseController
{
    #[Route(path: '/bbs', methods: ['GET'], name: 'bbs.index')]
    public function index(Request $request): BaseJsonResponse
    {
        return $this->success([
            'message' => 'Bbs Plugin is working!',
        ]);
    }
}