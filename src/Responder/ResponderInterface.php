<?php

namespace Phalconry\Mvc\Responder;

use Phalcon\Http\ResponseInterface;

interface ResponderInterface
{
	
	/**
	 * Enables the responder (default)
	 * 
	 * @return $this
	 */
	public function enable();
	
	/**
	 * Whether the responder is enabled
	 * 
	 * @return boolean
	 */
	public function isEnabled();
	
	/**
	 * Disables the responder
	 * 
	 * @return $this
	 */
	public function disable();
	
	/**
	 * Whether the responder is disabled
	 * 
	 * @return boolean
	 */
	public function isDisabled();
	
	/**
	 * Processes and sends a response
	 * 
	 * @param \Phalcon\Http\ResponseInterface $response
	 */
	public function send(ResponseInterface $response);
	
}
