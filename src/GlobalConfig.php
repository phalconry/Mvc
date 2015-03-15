<?php

namespace Phalconry\Mvc;

use Phalcon\Config;
use Phalcon\Registry;

class GlobalConfig extends Config
{
	
	/**
	 * Default environment
	 * @var string
	 */
	const DEFAULT_ENVIRONMENT = 'production';
	
	/**
	 * Default local
	 * @var string
	 */
	const DEFAULT_LOCALE = 'en_US';
	
	/**
	 * Default timezone
	 * @var string
	 */
	const DEFAULT_TIMEZONE = 'UTC';
	
	/**
	 * Constructor
	 * 
	 * @param array $config
	 */
	public function __construct(array $config = array()) {
		
		if (! isset($config['env'])) {
			$config['env'] = getenv('ENVIRONMENT') ?: static::DEFAULT_ENVIRONMENT;
		}
		
		if (! isset($config['locale'])) {
			$config['locale'] = getenv('LOCALE') ?: static::DEFAULT_LOCALE;
		}
		
		if (! isset($config['timezone'])) {
			$config['timezone'] = getenv('TZ') ?: ini_get('date.timezone') ?: static::DEFAULT_TIMEZONE;
		}
		
		if (! isset($config['paths'])) {
			$config['paths'] = new Registry();
		}
		
		parent::__construct($config);
		
		setlocale(LC_ALL, $this['locale']);
		date_default_timezone_set($this['timezone']);
	}
	
	/**
	 * Sets the directory paths
	 * 
	 * @param array $paths Array of directory paths
	 */
	public function setPaths(array $paths) {
		foreach($paths as $name => $path) {
			$this->setPath($name, $path);
		}
	}
	
	/**
	 * Returns the directory path registry
	 * 
	 * @return \Phalcon\Registry
	 */
	public function getPaths() {
		return $this->paths;
	}
	
	/**
	 * Sets a directory path by name
	 * 
	 * @param string $name
	 * @param string $path
	 */
	public function setPath($name, $path) {
		$this->paths[$name] = realpath($path).DIRECTORY_SEPARATOR;
	}
	
	/**
	 * Returns a directory path by name
	 * 
	 * @param string $name
	 * @return string
	 */
	public function getPath($name) {
		return $this->paths[$name];
	}
	
	/**
	 * Returns an entry value from a given section.
	 * 
	 * @param string $section Section name
	 * @param string $key Item key in section
	 * @return mixed Item value if exists, otherwise null
	 */
	public function getFrom($section, $key) {
		return isset($this[$section]) ? $this[$section][$key] : null;
	}
	
}
