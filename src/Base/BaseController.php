<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2017/05/13
 * Time: 18:06:34
 * By: BaseController.php
 */
namespace Yok\Base;

class BaseController extends \Phalcon\Mvc\Controller {

	public function exectue() {
		$basePageInfo = (\Phalcon\DI::getDefault())->get('basePageInfo');
		$module = ucfirst($basePageInfo->module);
		$method = ucfirst($basePageInfo->method);
		$className = PJS_NAMESPACE."\\Models\\".$module."\\Service\\".$method;
		if(class_exists($className)) {
			$serviceIns = new $className();
			$data = $serviceIns->exectue($basePageInfo);
		} else {
			echo "调用文件不存在";die;
		}
		if($basePageInfo->format === 'json'){
			echo json_encode($data,JSON_UNESCAPED_UNICODE);
			die;
		} else {
			$this->view->render(lcfirst($module), lcfirst($method), $data);
		}
	}

}
