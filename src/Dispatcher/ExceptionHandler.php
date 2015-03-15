<?php

namespace Phalconry\Mvc\Dispatcher;

use Phalcon\Events\Event;
use Phalcon\Dispatcher;
use Exception;

class ExceptionHandler
{
	protected $_default;
	protected $_handlers;

	public function __construct($defaultController, $defaultAction) {
		$this->_default = array(
			'controller' => $defaultController,
			'action' => $defaultAction,
		);
		$this->_handlers = array();
	}

	public function getDefaultController() {
		return $this->_default['controller'];
	}

	public function getDefaultAction() {
		return $this->_default['action'];
	}
	
	public function getDefaultHandler() {
		return $this->_default;
	}
	
	public function getHandler($exceptionCode) {
		if (isset($this->_handlers[$exceptionCode])) {
			return $this->_handlers[$exceptionCode];
		}
		return $this->getDefaultHandler();
	}
	
	public function setHandler($exceptionCode, $controller = null, $action = null) {
		$this->_handlers[$exceptionCode] = array(
			'controller' => isset($controller) ? $controller : $this->getDefaultController(),
			'action' => isset($action) ? $action : $this->getDefaultAction()
		);
	}
	
	public function beforeException(Event $event, Dispatcher $dispatcher, Exception $exception) {
		$dispatcher->forward($this->getHandler($exception->getCode()));
		return false;
	}

}
