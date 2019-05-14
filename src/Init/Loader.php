<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2017/05/13
 * Time: 18:06:15
 * By: Loader.php
 */

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */

$loader->registerDirs(
	[
		APP_PATH . $config->application->controllersDir,
		APP_PATH . $config->application->modelsDir
	]
)->register();

