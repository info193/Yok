<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/14
 * Time: 09:09:02
 * By: Runmode.php
 */
namespace framing\Library;

class Runmode {
	public static function get() {
		$mode = get_cfg_var("run.mode");
		if($mode !== false) {
			return $mode;
		} else {
			return "online";
		}
	}
}
