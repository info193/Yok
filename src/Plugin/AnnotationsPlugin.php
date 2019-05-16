<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/16
 * Time: 10:10:11
 * By: AnnotationsPlugin.php
 */
namespace Yok\Plugin;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

class AnnotationsPlugin {
	/**
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {
		// Possible controller class name
		$controllerName = $dispatcher->getControllerClass();

		// Possible method name
		$actionName = $dispatcher->getActiveMethod();
	}
}

