<?php

namespace Phalconry\Mvc\Responder;

use Phalcon\Http\ResponseInterface;
use Phalconry\Http\Response\JsonContent;

class JsonResponder extends AbstractDataResponder
{
	
	const CONTENT_TYPE = 'application/json';
	
	protected function prepare(ResponseInterface $response, $content = null) {
		
		$response->setContentType($this::CONTENT_TYPE);
		
		if (! $status = $response->getHeaders()->get('Status')) {
			$response->setStatusCode(200, 'OK');
			$status = '200 OK';
		}
		
		$json = new JsonContent($content);
		$json->prepend('status', (int)substr($status, 0, 3));
		$json->prepend('message', substr($status, 4));
		
		if ($meta = $this->getResponseMeta()) {
			$json->prepend('meta', $meta);
		}
		
		if ($response->getDI()->getRequest()->hasQuery('dev')) {
			$json->addOption(JSON_PRETTY_PRINT);
			$json->append('diagnostics', function () use($response) {
				return $response->getDI()->getDiagnostics()->getAll();
			});
		}
		
		$response->setJsonContent($json, $json->getOptions());
	}
	
}
