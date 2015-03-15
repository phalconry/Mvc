<?php

namespace Phalconry\Mvc\Responder;

class Factory
{
	
	protected $_classes = array(
		'null' => 'Phalconry\Mvc\Responder\NullResponder',
		'view' => 'Phalconry\Mvc\Responder\ViewResponder',
		'json' => 'Phalconry\Mvc\Responder\JsonResponder',
		'xml' => 'Phalconry\Mvc\Responder\XmlResponder',
	);
	
	public function setTypeClass($responseType, $responderClass) {
		if (! $responseType) {
			$responseType = 'null';
		}
		$this->_classes[$responseType] = $responderClass;
	}
	
	public function getTypeClass($responseType) {
		if (! $responseType) {
			$responseType = 'null';
		}
		return isset($this->_classes[$responseType]) ? $this->_classes[$responseType] : null;
	}
	
	public function __invoke($responseType) {
		if ($class = $this->getTypeClass($responseType)) {
			return new $class();
		}
		throw new \InvalidArgumentException("No responder for response type: '{$responseType}'.");
	}
	
	public function factory($responseType) {
		return $this($responseType);
	}
	
}
