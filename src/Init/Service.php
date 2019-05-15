<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2017/05/13
 * Time: 18:06:02
 * By: Service.php
 */
namespace Yok\Init;

use Phalcon\Mvc\View;
use Phalcon\Events\Event;
use Phalcon\Mvc\View\Engine\Php as PhpEngine;
use Phalcon\Mvc\View\Engine\Volt as VoltEngine;
use Phalcon\Session\Adapter\Files as SessionAdapter;
use Phalcon\Mvc\Router\Annotations as RouterAnnotations;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Events\Manager as EventsManager;
use Yok\Library\ConfigLibrary;

class Service{
	public static function init(&$di) {
		/**
		 * Setting up the view component
		 */
		$di->setShared('view', function () {
				$config = ConfigLibrary::get('config','application');

				$view = new View();
				$view->setDI($this);
				$view->setViewsDir($config->viewsDir);

				$view->registerEngines(['.volt' => function ($view) {
						$config = $this->getConfig();

						$volt = new VoltEngine($view, $this);

						$volt->setOptions([
								'compiledPath' => $config->cacheDir,
								'compiledSeparator' => '_'
						]);

						return $volt;
				},'.phtml' => PhpEngine::class]);

				return $view;
		});

		/**
		 * Setting up the dispatch
		 */
		$di->set('dispatcher',function () {
				$lists = explode(',',ConfigLibrary::get('config','plugin','list'));
				if(empty($lists)){
					echo "插件有误"; die;
				}
				// Create an event manager
				$eventsManager = new EventsManager();
				foreach($lists as $list) {
					$pluginArr[] = explode('|',$list);
				}
				foreach($pluginArr as $plugins) {
					$pluginCnt = explode('_',$plugins[1]);
					if(count($pluginCnt) == 2 && $pluginCnt[0] == 'SYS'){
						$pluginName = "\\Yok\\Plugin\\".$plugins[1];
					} else {
						$pluginName = "\\".PJS_NAMESPACE."\\".PLUGIN_NAME."\\".$plugins[1];
					}
				}

				// Attach a listener for type 'dispatch'
				if( class_exists($pluginName)) {
					$eventsManager->attach('dispatch', new $pluginName());
				}

				$dispatcher = new MvcDispatcher();

				// Bind the eventsManager to the view component
				$dispatcher->setEventsManager($eventsManager);

				return $dispatcher;
		},true);

		// Define your routes here
		$di->setShared('route',function(){
				// Use the annotations router. We're passing false as we don't want the router to add its default patterns
				$router = new RouterAnnotations(false);
				$routerConfigs = explode(',',ConfigLibrary::get('config','route','list'));
				// Read the annotations from ProductsController if the URI starts with /*
				foreach($routerConfigs as $module) {
					$router->addResource( ucfirst($module), '/' . lcfirst($module) );
				}

				return $router;
		});

		/**
		 * Database connection is created based in the parameters defined in the configuration file
		 */
		$di->setShared('db', function () {
				$config = ConfigLibrary::get('config','database');

				$class = 'Phalcon\Db\Adapter\Pdo\\' . $config->adapter;
				$params = [
					'host'     => $config->host,
					'username' => $config->username,
					'password' => $config->password,
					'dbname'   => $config->dbname,
					'charset'  => $config->charset
				];

				if ($config->adapter == 'Postgresql') {
					unset($params['charset']);
				}

				$connection = new $class($params);

				return $connection;
		});

		/**
		 * Start the session the first time some component request the session service
		 */
		$di->setShared('session', function () {
				$session = new SessionAdapter();
				$session->start();

				return $session;
		});

	}
}
