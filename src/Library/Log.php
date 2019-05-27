<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/27
 * Time: 14:02:04
 * By: Log.php
 */
namespace Yok\Library;

use Phalcon\Logger;
use Phalcon\Logger\Adapter\File as FileAdapter;
use Phalcon\Logger\Formatter\Line as LineFormatter;

class Log {
    private static $_instance  = [];
    private static $_ip = '0.0.0.0';

    /**
     * @param string $logType
     * @return mixed
     */
    public static function getInstance($logType = 'default'){
        $di = \Phalcon\DI::getDefault();
        $module = 'default';
        try{
            $basePageInfo = $di->get('basePageInfo');
            if ($basePageInfo !== null) {
                $module = lcfirst($basePageInfo->module);
                self::$_ip = $basePageInfo->requestIP;
            }
        } catch (\Exception $e) {
        }
        if(!isset(self::$_instance[$module][$logType])){
            $config = ConfigLibrary::get('config','log');
            $filePath = BASE_PATH.$config->filePath.'/'.date('Ymd');
            if(!file_exists($filePath)){
                mkdir ($filePath,0777,true);
            }
            if($logType != 'default'){
                $filePath .= '/'.$module.'.'.$logType.'.log';
            } else {
                $filePath .= '/'.$module.'.log';
            }
            self::$_instance[$module][$logType] = new FileAdapter($filePath);
            $format = '[%date%] %type% %message%';
            if($config->format !== null) {
                $format = $config->format;
            }
            $level = empty($config->level) ? 9 : $config->level;
            $formatter = new LineFormatter($format,date('Y-m-d H:i:s'));
            // Changing the logger format
            self::$_instance[$module][$logType]->setFormatter($formatter);
            self::$_instance[$module][$logType]->setLogLevel($level);
        }
        return self::$_instance[$module][$logType];
    }

    /**
     * @param $logType
     * @param $msg
     */
    public static function write($logType,$msg){
        $instance = self::getInstance($logType);
        $instance->$logType('IP:' . self::$_ip . ' ' . $msg);
    }

    /**
     * @param $msg
     */
    public static function error($msg){
        self::write(__FUNCTION__,$msg);
    }

    /**
     * @param $msg
     */
    public static function info($msg){
        self::write(__FUNCTION__,$msg);
    }

    /**
     * @param $msg
     */
    public static function debug($msg){
        self::write(__FUNCTION__,$msg);
    }

    /**
     * @param $msg
     */
    public static function warning($msg){
        self::write(__FUNCTION__,$msg);
    }

    /**
     * @param $msg
     */
    public static function alert($msg){
        self::write(__FUNCTION__,$msg);
    }
}


