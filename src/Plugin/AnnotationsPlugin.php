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
use Yok\Library\Runmode;

class AnnotationsPlugin extends Plugin{
	/**
	 * @param Event $event
	 * @param Dispatcher $dispatcher
	 */
	public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {
		$basePageInfo = (\Phalcon\DI::getDefault())->get('basePageInfo');
		// get system Running env.
		$basePageInfo->runMode = Runmode::get();
		// Possible controller class name
		$controller = $dispatcher->getControllerClass();
		$basePageInfo->module = str_replace('Controller','',$controller);
		// Possible method name
		$activeMethod = $dispatcher->getActiveMethod();
		$basePageInfo->method = str_replace('Action','',$activeMethod);
		// Parse the annotations in the method currently executed
		$annotations = $this->annotations->getMethod($controller,$activeMethod);
		// Check if the method has an annotation Post
		if( $this->getAnnotationValue($annotations, "Post") !== null){
			$basePageInfo->requestType = "post";
		}
		// Check if the method has an annotation format
		if($this->getAnnotationValue($annotations,'login') === false) {
			$basePageInfo->login = false;
		}
		// Check if the method has an annotation formCheck
		if($this->getAnnotationValue($annotations,'formCheck') !== null){
			$basePageInfo->formCheck = $this->getAnnotationValue($annotations,'formCheck');
		}
		// Check if the method has an annotation format
		if($this->getAnnotationValue($annotations,'format') !== null) {
			$basePageInfo->format = trim($this->getAnnotationValue($annotations,'format'));
		}
		// Check if the method has an annotation Action
		if($this->getAnnotationValue($annotations,'Action') !== null){
			$basePageInfo->method = trim($this->getAnnotationValue($annotations,'Action'));
		}
	}

	/**
	 * @param $annotations
	 * @param $key
	 * @param bool $array
	 * @return null
	 */
	public function getAnnotationValue($annotations,$key,$array=false) {
		$value = null;
		// Check if the method has an annotation $key
		if ($annotations->has($key)) {
			// The method has the annotation $key
			$annotation = $annotations->get($key);
			// Print the arguments
			$values = $annotation->getArguments();
			if( $array === true){
				return $values;
			}
			if( count($values) > 0 ){
				$value = $values[0];
			}
		}
		return $value;
	}
}


