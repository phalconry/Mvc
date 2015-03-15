# Mvc
Multi-module (H)MVC application library using PhalconPHP.

##Features
 * Multi-module structure with application-aware modules
 * Cross-module HMVC requests
 * Responders for views and JSON (more to come)

##Basic usage
####1. Create `modules.php`, `loader.php`, and `services.php` in your config directory
__modules.php:__
```php
$app->registerModules(array(
	'frontend' => array(
		'className' => 'App\Frontend\Module',
		'path' => $app->getPath('modules').'frontend/Module.php'
	),
	'admin' => array(
		'className' => 'App\Admin\Module',
		'path' => $app->getPath('modules').'admin/Module.php'
	),
));

$app->setDefaultModule('frontend');
```
__loader.php:__
```php
// This is needed if you will be using cross-module HMVC requests
$loader->registerClasses(array(
	'App\Frontend\Module' => $app->getPath('modules').'frontend/Module.php',
	'App\Admin\Module' => $app->getPath('modules').'admin/Module.php',
));

$loader->registerNamespaces(array(
	'App\Controller' => $app->getPath('controllers'),
	'App\Model' => $app->getPath('models'),
));

$loader->registerDirs(array(
	$app->getPath('library'),
));
```
__services.php:__
```php
$di->set('hmvcRequest', function () {
	return new Phalconry\Mvc\HmvcRequest();
});
// ...
```
####2. Create a `GlobalConfig` object and set directory paths by name
```php
$config = new Phalconry\Mvc\GlobalConfig(array(
	'env' => 'development',
	'locale' => 'en_US', // setlocale() is called in constructor
	'timezone' => 'America/New_York', // date_default_timezone_set() is called in constructor
	// ...
));

$config->setPaths(array(
	'app'			=> '../app',
	'config'		=> '../app/config', // Required. modules.php, loader.php, and services.php live here
	'models'		=> '../app/models',
	'controllers'	=> '../app/controllers',
	'modules'		=> '../app/modules',
	'library'		=> '../app/library',
	'system'		=> '../system',	
	'cache'			=> '../system/var/cache',
	'temp'			=> '../system/var/temp',
	'logs'			=> '../system/var/logs',
	'web'			=> '../web',
	'assets'		=> '../web/assets',
));
```
####3. Create an `Application`, passing the `GlobalConfig` to the constructor
```php
$app = new Phalconry\Mvc\Application($config);
```
####4. Run the application
```php
try {
	$app->run();
} catch (Exception $e) {
	print $e->getMessage();
}
```
