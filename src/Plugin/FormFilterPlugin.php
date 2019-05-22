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
use Yok\Base\BaseException;

class FormFilterPlugin extends Plugin {
	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher){
		$di = \Phalcon\DI::getDefault();
		$basePageInfo = $di->get('basePageInfo');
		if($basePageInfo->formCheck === false) {
			return true;
		}
		$request = $di->getRequest();
		$debug = $request->getPost("debug");
		if($debug == null){
			$debug = $request->getQuery("debug");
		}
		if(strtoupper($basePageInfo->requestType) !== $request->getMethod()){
			throw new BaseException(BaseException::PARAM_ERROR,$request->getMethod());
		}
		$module = ucfirst($basePageInfo->module);
		$method = ucfirst($basePageInfo->method);
		$className = PJS_NAMESPACE."\\Models\\".$module."\\Param\\".$method."Param";
		if(class_exists($className)) {
			$classIns = new $className();
			$classIns->vaild($classIns,$di,$basePageInfo);
			$basePageInfo->params = $classIns;
		} else {
			throw new BaseException(BaseException::INTER_ERROR);
		}
		echo $basePageInfo->params->debug;die;
		print_r($basePageInfo);die;
	}
}


