<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Framework\Attributes\Route;
use Framework\Basic\BaseController;

class HomeController extends BaseController
{

	#[Route(path: '/api/home', methods: ['GET'], name: 'home.index')]
	public function index(Request $request):Response
	{

        $params = [
            'page' => (int)$this->input('page', 1),
            'limit' => (int)$this->input('limit', 20),
            'username' => $this->input('username', 'test'),
            'status' => $this->input('status', ''),
            'dept_id' => $this->input('dept_id', 1 ),
        ];
		
		//curl -X POST -d "page=111&limit=11&username=tom&status=ok" http://localhost:8000/api/home
		return new Response(json_encode($params));
		return new Response(
			'<html><body><h1>Hello, World!</h1></body></html>',
			Response::HTTP_OK, // Code（200）
			['Content-Type' => 'text/html; charset=UTF-8']
		);	
	}
	

}
