<?php
namespace Yok\Base;

class BaseException extends \Exception {
    public function __construct($code,$message = "") {
        $this->code = $code;
        $this->message = self::getErrorMsg( $code );
        if( $message != ""){
            $this->message .= $message;
        }

    }
    public static function getErrorMsg($errorCode){
        $className = PJS_NAMESPACE."\\Errno\\Errno";
        if(class_exists($className)) {
            echo "--------------";die;
        } else {
            echo "参数校验文件未找到";die;
        }
    }

}