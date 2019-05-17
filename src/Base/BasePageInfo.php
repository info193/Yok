<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/13
 * Time: 18:06:02
 * By: BasePageInfo.php
 */
namespace Yok\Base;

class BasePageInfo {
	public $requestType = 'get';
	public $format  = "json";
	public $runMode = 'online';
	public $login   = true;
	public $formCheck = true;
	public $module;
	public $method;
	public $sessionInfo;
	public $params;
}

