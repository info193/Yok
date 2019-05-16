<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2017/05/13
 * Time: 18:06:15
 * By: Loader.php
 */
namespace Yok\Init;

class Loader {

	public function init(&$loader,$config) {

		/**
		 * We're a registering a set of directories taken from the configuration file
		 */

		$loader->registerDirs(
			[
				BASE_PATH . $config->controllersDir,
				BASE_PATH . $config->modelsDir
			]
		)->register();
	}
}
