<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/13
 * Time: 18:06:37
 * By: BaseException.php
 */
namespace Yok\Base;

class BaseException extends \Exception {
	const PARAM_ERROR       = 9999;
	const INTER_ERROR       = 5000;
	const SERVER_PROXY_ERROR= 5001;
	const PARTNER_ERROR     = 5002;
	public static $msg = [
		self::PARAM_ERROR           => '参数有误',
		self::INTER_ERROR           => '内部错误',
		self::SERVER_PROXY_ERROR    => '存管服务中断或异常',
		self::PARTNER_ERROR         => '合作方服务中断或异常'
	];

	/**
	 * BaseException constructor.
	 * @param string $code
	 * @param string $message
	 */
	public function __construct($code,$message = "") {
		$this->code = $code;
		$this->message = self::getErrorMsg( $code );
		if($this->code !== intval(self::PARAM_ERROR)) {
			if( $message != "") {
				$this->message .= $message;
			}
		} else {
			// 记录参数有误日志
		}
	}

	/**
	 * @param $exception
	 */
	public function errorMsg($exception){
		$code = $exception->getCode();
		if(empty(self::$msg[$code])){
			$className = PJS_NAMESPACE."\\Errno\\Errno";
			if(class_exists($className)) {
				if(!empty($className::$msg[$code])){
					$msg  = $className::$msg[$code];
				}
			} else {
				$code = self::INTER_ERROR;
				$msg  = self::$msg[$code];
			}
		} else {
			$msg = self::$msg[$code];
		}
		$data = [];
		$data['errno'] = $code;
		$data['msg']   = $msg;
		$data['data']  = [];
		// 记录日志
		echo json_encode($data,JSON_UNESCAPED_UNICODE);
		exit;

	}

	/**
	 * @param $errorCode
	 * @return mixed
	 * @throws \Exception
	 */
	public static function getErrorMsg($errorCode){
		if(isset(self::$msg[$errorCode])){
			return self::$msg[$errorCode];
		}
		$className = PJS_NAMESPACE."\\Errno\\Errno";
		if(class_exists($className)) {
			return $className::$msg[$errorCode];
		} else {
			throw new  \Exception("not Errno Class.",self::INTER_ERROR);
		}
	}

}
