<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2017-05-20
 * Time: 16:27
 */

namespace Yok\Library;

class ConfigLibrary
{
	/**
	 * @param string $sid
	 * @param string $module
	 * @param string|null $key
	 * @return null
	 */
	public static function get(string $sid, string $module, string $key=null){
		$ruMode = Runmode::get();
		$configFile = CONFIG_PATH."/$ruMode/$sid.ini";
		$configModule = self::getFromConfigFile( $configFile, $module);
		if( $configModule == null ) {
			$configFile = CONFIG_PATH."/share/$sid.ini";
			$configModule = self::getFromConfigFile( $configFile, $module);
		}

		if( $key == null) {
			return $configModule;
		}else {
			return $configModule->$key;
		}
	}

	/**
	 * @param string $configFile
	 * @param string $module
	 * @return null
	 * @throws BaseException
	 */
	public static function getFromConfigFile(string $configFile, string $module) {
		if( !file_exists($configFile)) {
			return null;
		}
		try{
			$iniReader = new \Phalcon\Config\Adapter\Ini($configFile);
			if( $iniReader == null || !isset($iniReader->$module)) {
				return null;
			}else {
				return $iniReader->$module;
			}
		}catch (\Exception $e){
			echo "异常".$e->getMessage();die;
		}
	}

	/**
	 * @param string $configFile
	 * @param string $module
	 * @param string $key
	 * @return null
	 * @throws BaseException
	 */
	public static function geFromConfigFileByKey(string $configFile, string $module, string $key){
		if( !file_exists($configFile)) {
			echo "null";die;
			return null;
		}

		try{
			$iniReader = new \Phalcon\Config\Adapter\Ini($configFile);
			if( $iniReader == null || !isset($iniReader->$module) || !isset($iniReader->$module->$key) ) {
				return null;
			}else {
				return $iniReader->$module->$key;
			}
		}catch (\Exception $e){
			echo "异常".$e->getMessage();die;
		}
	}
}
