<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/17
 * Time: 11:11:10
 * By: FormFilterPlugin.php
 */
namespace Yok\Plugin;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;
class FormFilterPlugin extends Plugin {
	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher){
		$di = \Phalcon\DI::getDefault();
		$basePageInfo = $di->get('basePageInfo');
		if($basePageInfo->formCheck === false) {
			return true;
		}
		$module = ucfirst($basePageInfo->module);
		$method = ucfirst($basePageInfo->method);
		$className = PJS_NAMESPACE."\\Models\\".$module."\\Param\\".$method."Param";
		if(class_exists($className)) {
			$classIns = new $className();
			$classIns->vaild($classIns,$di,$basePageInfo);
			$basePageInfo->params = $classIns;
		} else {
			echo "参数校验文件未找到";die;
		}
		print_r($basePageInfo);die;
	}
}

