<?php

namespace Phalconry\Mvc\Responder;

use Phalcon\Http\ResponseInterface;

abstract class AbstractResponder implements ResponderInterface
{
	
	/**
	 * Whether responder is disabled
	 * @var boolean
	 */
	private $_disable = false;
	
	/**
	 * Enables the responder (default)
	 * 
	 * @return $this
	 */
	public function enable() {
		$this->_disable = false;
		return $this;
	}
	
	/**
	 * Whether the responder is enabled
	 * 
	 * @return boolean
	 */
	public function isEnabled() {
		return $this->_disable === false;
	}
	
	/**
	 * Disables the responder
	 * 
	 * @return $this
	 */
	public function disable() {
		$this->_disable = true;
		return $this;
	}
	
	/**
	 * Whether the responder is disabled
	 * 
	 * @return boolean
	 */
	public function isDisabled() {
		return $this->_disable === true;
	}
	
	/**
	 * Processes and sends a response
	 * 
	 * @param \Phalcon\Http\ResponseInterface $response
	 */
	public function send(ResponseInterface $response) {
		if ($this->isEnabled()) {
			$this->intervene($response);
		}
		$response->send();
	}
	
	/**
	 * Modifies the response based on the type of responder
	 * 
	 * @param \Phalcon\Http\ResponseInterface $response
	 */
	abstract protected function intervene(ResponseInterface $response);
	
	/**
	 * Returns the value returned by the controller.
	 * 
	 * @param \Phalcon\Http\ResponseInterface $response
	 * @return mixed
	 */
	protected function getReturnedValue(ResponseInterface $response) {
		return $response->getDI()->getDispatcher()->getReturnedValue();
	}
	
	/**
	 * Returns the view vars as an associative array.
	 * 
	 * @param \Phalcon\Http\ResponseInterface $response
	 * @return array
	 */
	protected function getViewVars(ResponseInterface $response) {
		return $response->getDI()->getView()->getParamsToView();
	}
	
}
