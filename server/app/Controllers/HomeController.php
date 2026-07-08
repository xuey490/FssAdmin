<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Basic\BaseController;
use Framework\Basic\BaseJsonResponse;

class HomeController extends BaseController
{

	#[Route(path: '/api/home', methods: ['GET'], name: 'home.index')]
	public function index(Request $request): BaseJsonResponse
	{
		return BaseJsonResponse::success(['message' => 'Hello, World!']);
	}
	

}
