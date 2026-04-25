<?php

declare(strict_types=1);

/**
 * This file is part of FssPhp.
 *
 */

namespace App\Controllers;

use Framework\Utils\Captcha as CCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Captcha
{
	
    public function captchaImage(Request $request): Response
    {
		$CaptchaImage =\Framework\Utils\Captcha::base64();
		
		$imgsrc = $CaptchaImage['base64'];
		
		return new Response ( "<img src='{$imgsrc}'>" );

    }
	

}
