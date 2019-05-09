<?php

namespace App\Factories;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class ResponseFactory
 * @package App\Factories
 */
class ResponseFactory {


	/**
	 * @param string $content
	 * @param int $status
	 * @param array $headers
	 * @return Response
	 */
	public function make ($content = '', $status = 200, array $headers = []) {
		return new Response($content, $status, $headers);
	}

}