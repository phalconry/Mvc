<?php

namespace Phalconry\Mvc\Responder;

use Phalcon\Http\ResponseInterface;

abstract class AbstractDataResponder extends AbstractResponder
{
	
	/**
	 * Response metadata
	 * @var array
	 */
	protected $_meta = array();
	
	/**
	 * Adds metadata to the response
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function addResponseMeta($key, $value) {
		$this->_meta[$key] = $value;
		return $this;
	}
	
	/**
	 * Returns an array of response metadata
	 * 
	 * @return array
	 */
	public function getResponseMeta() {
		$meta = array();
		foreach($this->_meta as $key => $value) {
			$meta[$key] = is_callable($value) ? $value() : $value;
		}
		return $meta;
	}
	
	/**
	 * Modifies the response based on the type of responder
	 * 
	 * @param \Phalcon\Http\ResponseInterface $response
	 */
	protected function intervene(ResponseInterface $response) {
		
		$returnValue = $this->getReturnedValue($response);
		
		if (false === $returnValue || $response === $returnValue) {
			return;
		}
		
		$content = $response->getContent();
	
		if (empty($content)) {
			$content = $returnValue;
		}
		
		if (empty($content)) {
			$content = $this->getViewVars($response);
		}
		
		$this->prepare($response, $content);
	}
	
	abstract protected function prepare(ResponseInterface $response, $content = null);
	
}
