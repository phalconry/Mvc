<?php

namespace Phalconry\Mvc;

use Phalcon\DI;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;

/**
 * Module
 * 
 * This class is "pseudo-DI-aware" in that its getDI() method returns the 
 * default DI container using DI::getDefault()
 */
abstract class Module implements ModuleDefinitionInterface
{
	
	/**
	 * The module name
	 * @var string
	 */
	protected $_name;
	
	/**
	 * The application
	 * @var \Phalconry\Mvc\Application
	 */
	protected $_application;
	
	/**
	 * Returns the DI container
	 * 
	 * @return \Phalcon\DiInterface
	 */
	public function getDI() {
		return DI::getDefault();
	}
	
	/**
	 * Returns a shared item from the DI container
	 * 
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		return $this->getDI()->getShared($key);
	}
	
	/**
	 * Sets the module name
	 * 
	 * @param string $name
	 */
	public function setName($name) {
		$this->_name = $name;
	}
	
	/**
	 * Returns the module name
	 * 
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}
	
	/**
	 * Sets the application
	 * 
	 * @param \Phalconry\Mvc\Application $app
	 */
	public function setApp(Application $app) {
		$this->_application = $app;
	}
	
	/**
	 * Returns the application
	 * 
	 * @return \Phalconry\Mvc\Application
	 * @throws \RuntimeException if app is not set
	 */
	public function getApp() {
		if (! isset($this->_application)) {
			throw new \RuntimeException("Module is not active");
		}
		return $this->_application;
	}
	
	/**
	 * Whether the module has been loaded
	 * 
	 * @return boolean
	 */
	public function isLoaded() {
		return isset($this->_application);
	}
	
	/**
	 * Whether this is the primary module
	 * 
	 * @return boolean
	 */
	public function isPrimary() {
		if ($this->isLoaded()) {
			return $this === $this->getApp()->getPrimaryModule();
		}
		return false;
	}
	
	/**
	 * Whether this is the default module
	 * 
	 * @return boolean
	 */
	public function isDefault() {
		if ($this->isLoaded()) {
			return $this->getName() === $this->getApp()->getDefaultModule();
		}
		return false;
	}
	
	/**
	 * Register separate autoloaders for the module, if any
	 */
	public function registerAutoloaders() {
		
	}
	
	/**
	 * Returns the default namespace to use for controllers
	 * 
	 * Called in Application on "application:afterStartModule"
	 * 
	 * @return string
	 */
	abstract public function getControllerNamespace();
	
	/**
	 * Allows the module to perform start-up tasks
	 * 
	 * Called in Application on "application:afterStartModule"
	 */
	public function onLoad() {
		
	}
	
	/**
	 * Allows the PRIMARY module to perform additional operations when responding with a view
	 * 
	 * Called in Application on "application:afterHandleRequest"
	 * ONLY IF respondse mode is 'view' - otherwise, the view is disabled
	 * 
	 * @param \Phalcon\Mvc\View $view
	 */
	public function onView(View $view) {
		
	}
	
}
