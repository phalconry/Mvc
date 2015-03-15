<?php

namespace Phalconry\Mvc;

use Phalcon\Registry;
use Phalcon\Loader;
use Phalcon\DiInterface;
use Phalcon\DI\FactoryDefault;
use Phalcon\Http\ResponseInterface;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Mvc\ViewInterface;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application as PhalconApp;

class Application extends PhalconApp
{
	
	/**
	 * Disables the responder
	 * @var boolean
	 */
	const RESPONSE_NONE = false;
	
	/**
	 * Designates a "view" response
	 * @var string
	 */
	const RESPONSE_VIEW = 'view';
	
	/**
	 * Designates a "json" response
	 * @var string
	 */
	const RESPONSE_JSON = 'json';
	
	/**
	 * Designates an "xml" response
	 * @var string
	 */
	const RESPONSE_XML = 'xml';
	
	/**
	 * Name of the primary module
	 * @var string
	 */
	protected $_moduleName;
	
	/**
	 * Module registry
	 * @var \Phalcon\Registry
	 */
	protected $_moduleRegistry;
	
	/**
	 * Response type (one of class constants)
	 * @var bool|string
	 */
	protected $_responseType = self::RESPONSE_VIEW;
	
	/**
	 * Responder
	 * @var \Phalconry\Http\Response\ResponderInterface
	 */
	protected $_responder;
	
	/**
	 * Application constructor.
	 *
	 * @param \Phalconry\Mvc\GlobalConfig $config Global config settings
	 */
	public function __construct(GlobalConfig $config) {
		
		$di = new FactoryDefault();
		
		$di['app'] = $this;
		$di['config'] = $config;
		
		parent::__construct($di);
		
		$this->_moduleRegistry = new Registry();
		
		$eventsManager = new EventsManager();
		$eventsManager->attach('application', $this);
		$this->setEventsManager($eventsManager);
	}
	
	/**
	 * Returns a directory path from the registry.
	 *
	 * @param string $name Path name.
	 * @return string
	 */
	public function getPath($name) {
		return $this->getDI()->getConfig()->getPath($name);
	}

	/**
	 * Sets a named directory path.
	 *
	 * @param string $name Path name.
	 * @param string $value Absolute directory path.
	 */
	public function setPath($name, $value) {
		$this->getDI()->getConfig()->setPath($name, $value);
	}
	
	/**
	 * Returns the name of a module from an object
	 * 
	 * @param \Phalconry\Module $module [Optional]
	 * @return string
	 */
	public function getModuleName(Module $module = null) {
		if (! isset($module)) {
			return $this->_moduleName;
		}
		$class = get_class($module);
		foreach($this->getModules() as $name => $args) {
			if ($args['className'] === $class) {
				return $name;
			}
		}
		return null;
	}
	
	/**
	 * Returns a module by name, or the primary module if none given.
	 *
	 * @param string $name [Optional] Module name
	 * @return \Phalconry\Module
	 */
	public function getModule($name = null) {
		if (! isset($name)) {
			return $this->_moduleObject;
		}
		return $this->_moduleRegistry[$name];
	}
	
	/**
	 * Adds a module to the registry
	 * 
	 * @param \Phalconry\Module $module
	 */
	public function setModule(Module $module) {
		
		$name = $this->getModuleName($module);
		
		if (isset($this->_moduleRegistry[$name])) {
			throw new \RuntimeException("Module is already set: '{$name}'.");
		}
		
		$module->setName($name);
		
		$this->_moduleRegistry[$name] = $module;
	}
	
	/**
	 * Loads a module
	 * 
	 * @param string|\Phalconry\Module $module
	 * @return \Phalconry\Module
	 */
	public function loadModule($module) {
		
		if (is_string($module)) {
			$moduleList = $this->getModules();
			if (! isset($moduleList[$module])) {
				throw new \InvalidArgumentException("Unknown module: '{$module}'.");
			}
			$class = $moduleList[$module]['className'];
			$module = new $class();
		}
		
		if (! $module instanceof Module) {
			throw new \InvalidArgumentException("Invalid module given");
		}
		
		if ($module->isLoaded()) {
			throw new \RuntimeException("Cannot reload module");
		}
		
		$this->setModule($module);
		
		$di = $this->getDI();
		
		$module->registerAutoloaders($di);
		$module->registerServices($di);
		$module->setApp($this);
		$module->onLoad();
		
		return $module;
	}
	
	/**
	 * Whether a module has been loaded
	 * 
	 * @param string|\Phalconry\Mvc\Module $module
	 * @return boolean
	 * @throws \InvalidArgumentException if not given a Module object or string
	 */
	public function isModuleLoaded($module) {
		
		if ($module instanceof Module) {
			return $module->isLoaded();
		} else if (! is_string($module)) {
			throw new \InvalidArgumentException("Expecting string or Module, given: ".gettype($module));
		}
		
		if ($module === $this->_moduleName) {
			return isset($this->_moduleObject) ? $this->_moduleObject->isLoaded() : false;
		}
		
		return isset($this->_moduleRegistry[$module]);
	}
	
	/**
	 * Returns the primary module name.
	 * 
	 * @return string
	 */
	public function getPrimaryModuleName() {
		return $this->_moduleName;
	}
	
	/**
	 * Returns the primary module
	 * 
	 * @return \Phalconry\Mvc\Module
	 */
	public function getPrimaryModule() {
		return $this->_moduleObject;
	}
	
	/**
	 * Sets the type of response
	 * 
	 * @param bool|string
	 */
	public function setResponseType($type) {
		$this->_responseType = $type;
	}
	
	/**
	 * Returns the type of response
	 * 
	 * @return bool|string
	 */
	public function getResponseType() {
		return $this->_responseType;
	}
	
	/**
	 * Sets the responder
	 * 
	 * @param \Phalconry\Mvc\Responder\ResponderInterface $responder
	 */
	public function setResponder(Responder\ResponderInterface $responder) {
		$this->_responder = $responder;
	}
	
	/**
	 * Returns the responder
	 * 
	 * @return \Phalconry\Mvc\Responder\ResponderInterface
	 */
	public function getResponder() {
		if (! isset($this->_responder)) {
			$this->setResponder($this->getDI()->getResponderFactory()->factory($this->getResponseType()));
		}
		return $this->_responder;
	}
	
	/** 
	 * Runs the application and sends the response.
	 */
	public function run() {
		$response = $this->handle();
		$this->getResponder()->send($response);
	}
	
	/**
	 * --------------------------------------------------------
	 * Application events
	 * --------------------------------------------------------
	 */
	
	/**
	 * application:boot
	 */
	public function boot(Event $event) {
		$this->_registerAutoloaders();
		$this->_registerServices();
		$this->_registerModules();
	}
	
	/**
	 * application:beforeStartModule
	 */
	public function beforeStartModule(Event $event, PhalconApp $application, $moduleName) {
		$this->_moduleName = $moduleName;
	}
	
	/**
	 * application:afterStartModule
	 */
	public function afterStartModule(Event $event) {
		$module = $this->getPrimaryModule();
		$module->setName($this->_moduleName);
		$this->getDI()->getDispatcher()->setDefaultNamespace($module->getControllerNamespace());
		$module->setApp($this);
		$module->onLoad();
	}
	
	/**
	 * application:beforeHandleRequest
	 */
	#public function beforeHandleRequest(Event $event) {}

	/**
	 * application:afterHandleRequest
	 */
	public function afterHandleRequest(Event $event) {
		
		$view = $this->getDI()->getView();
		
		if ($this->getResponseType() === static::RESPONSE_VIEW) {
			$this->getPrimaryModule()->onView($view);
		} else {
			$view->disable();
		}
	}
	
	/**
	 * --------------------------------------------------------
	 * Protected methods
	 * --------------------------------------------------------
	 */
	
	/**
	 * Registers class loader(s)
	 */
	protected function _registerAutoloaders() {
		$loader	= new Loader();
		require $this->getDI()->getConfig()->getPath('config').'loader.php';
		$loader->register();
	}
	
	/**
	 * Registers global services
	 */
	protected function _registerServices() {
		
		$app = $this;
		$di = $this->getDI();
		
		$di->setShared('dispatcherEvents', function () {
			$object = new EventsManager();
			$object->attach('dispatch', new Dispatcher\ExceptionHandler('index', 'serverError'));
			return $object;
		});
		
		$di->setShared('dispatcher', function () use($di) {
			$dispatcher = new Dispatcher();
			$dispatcher->setEventsManager($di['dispatcherEvents']);
			return $dispatcher;
		});
		
		$di->setShared('viewEvents', function () use($app) {
			$object = new EventsManager();
			$object->attach('view', $app->getPrimaryModule());
			return $object;
		});
		
		$di->setShared('view', function () use($di) {
			$view = new View();
			$view->setEventsManager($di['viewEvents']);
			return $view;
		});
		
		$di->setShared('responderFactory', function () {
			return new Responder\Factory();
		});
		
		require $this->getDI()->getConfig()->getPath('config').'services.php';	
	}
	
	/**
	 * Registers modules
	 */
	protected function _registerModules() {
		$app = $this;
		require $this->getDI()->getConfig()->getPath('config').'modules.php';
	}
	
}
