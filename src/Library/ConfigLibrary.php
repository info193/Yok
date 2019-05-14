<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/13
 * Time: 18:06:39
 * By: ConfigLibrary.php
 */
namespace Yok\Library;

class ConfigLibrary {
    /**
     * @$param string $filename 文件名
     * @$param string $module   模块
     * @$param string $key      键
     */
    public static function get(string $filename,string $module,string $key='') {
        $runmode = \Yok\Library\Runmode::get();
        $filename = CONF_PATH . '/' . $runmode . '/' . $filename . '.ini';
        $config = self::getConfigFile($filename,$module);
        if($config === null) {
            echo "文件不存在";die;
        } else if(isset($config[$key])){
            return $config[$key];
        } else {
            return $config;
        }
    }
    /**
     * @param string $filename
     * @param string $module
     * @return null
     */
    public static function getConfigFile(string $filename='',string $module='') {
        if(!file_exists($filename)) {
            return null;
        }
        $config = new \Phalcon\Config\Adapter\Ini($filename);
        if(empty($config) || !isset($config->$module)) {
            return null;
        } else {
            return $config->$module;
        }
    }
}
