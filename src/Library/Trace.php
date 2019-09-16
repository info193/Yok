<?php
/**
 * Created by PhpStorm.
 * User: ghupeng
 * Date: 19-9-12
 * Time: 下午4:59
 */

namespace Yok\Library;
class Trace {
    private static $_instance = null;
    private static $_debug    = false;
    private static $_docTrace = [];
    public function __construct()
    {
        self::$_debug=false;
    }

    /**
     * @param $debug
     */
    public function setDebug($debug) {
        if($debug === 1 || $debug === "true" || $debug === true) {
           self::$_debug = true;
        } else {
            self::$_debug = false;
        }
    }

    /**
     * @param $str
     * @param null $paramArr
     * @param null $resultAll
     * @param int $depth
     * @return mixed
     */
    private function _getStr($str,$paramArr=null,$resultAll=null,$depth=0){
        $trace = debug_backtrace();
        $traceCount = count($trace);
        if($depth >= $traceCount) {
            $depth = $traceCount - 1;
        }
        $output['file'] = $trace[$depth]['file'];
        $output['line'] = $trace[$depth]['line'];
        $output['msg']  = $str;
        if(!empty($paramArr)){
            $output['param'] = $paramArr;
        }
        if(!empty($resultAll) ){
            $output['result'] = $resultAll;
        }
        return $output;
    }

    /**
     * @return bool
     */
    public function getValid() {
        return self::$_debug;
    }

    /**
     * @return null|Trace
     */
    public static function getInstance()
    {
        if( self::$_instance == null ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param $str
     * @param null $paramArr
     * @param null $retAll
     * @param int $level
     * @return bool
     */
    public function add($str,$paramArr=null,$retAll=null,$level =1){
        if(self::$_debug === false) {
            return false;
        }
        $md5 = md5($str);
        $dataArr = $this->_getStr($str,$paramArr,$retAll,$level);
        $dataArr['analysis']['createTime']=time();
        self::$_docTrace[$md5]=$dataArr;
    }

    /**
     * @param $str
     * @param null $retAll
     * @return bool
     */
    public function attach($str,$retAll=null){
        if(self::getTrace() === false) {
            return false;
        }
        if(is_string($retAll)) {
            $data = json_decode($retAll,true);
            if(json_last_error() == JSON_ERROR_NONE) {
                $retAll = $data;
            }
        }
        $md5 = md5($str);
        self::$_docTrace[$md5]['analysis']['result']  = $retAll;
        self::$_docTrace[$md5]['analysis']['endTime'] = time();
        self::$_docTrace[$md5]['analysis']['timeCost'] = (self::$_docTrace[$md5]['analysis']['endTime'] - self::$_docTrace[$md5]['analysis']['createTime']);
        self::$_docTrace[$md5]['analysis']['endTime']  = date('Y-m-d H:i:s',self::$_docTrace[$md5]['analysis']['endTime']);
        self::$_docTrace[$md5]['analysis']['createTime'] = date('Y-m-d H:i:s', self::$_docTrace[$md5]['analysis']['createTime']);
    }

    /**
     * @return array|bool
     */
    public function getTrace(){
        if (self::$_debug === false) {
            return false;
        }
        return self::$_docTrace;
    }
}