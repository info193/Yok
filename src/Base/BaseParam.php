<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/13
 * Time: 18:06:37
 * By: BaseParam.php
 */
namespace Yok\Base;

use Yok\Library\Annotations;
class BaseParam {
	public function vaild(&$classIns,$di,$basePageInfo) {
		$request = $di->getRequest();
		$className = get_called_class();
		$reflection = new \ReflectionClass ( $className );

		foreach ($reflection->getProperties() as $property) {
			$propertyName = $property->name;
			$reflectionProperty = new \ReflectionProperty($className, $propertyName);
			$comment = $reflectionProperty->getDocComment();
			$defaultValue = $reflectionProperty->getValue($classIns);
			$name       = Annotations::getCommentValue($comment,'name');
			$name       = empty($name) ? $propertyName : $name;
			if(strtoupper($basePageInfo->requestType) === 'POST') {
				$value = $request->getPost($name);
			} else if(strtoupper($basePageInfo->requestType) === 'GET') {
				$value = $request->get($name);
			} else {
				$value = $request->getQuery($name);
			}

			$type       = Annotations::getCommentValue($comment,'type');
			$length     = Annotations::getCommentValue($comment,'length');
			$optional   = Annotations::getCommentValue($comment,'optional');
			$regex      = Annotations::getCommentValue($comment,'regex');
			$filter     = Annotations::getCommentValue($comment,'filter');
			$maxMin     = Annotations::getCommentValue($comment,'max_min');
			if( $optional == "" && ($length == "" && $maxMin == "") && $regex == "") {
				$optional = (($length == "" && $maxMin == "") && $regex == "") ? true : false;
			}
			$optional = ($optional == 'true' || $optional === true) ? true : false;
			$value = $this->getRequestParam($name, $value, $defaultValue, $type, $length, $regex, $filter, $optional,$maxMin);
			$classIns->$propertyName = $value;
		}
	}

	/**
	 * @param $name
	 * @param $value
	 * @param $defaultValue
	 * @param $type
	 * @param $length
	 * @param $regex
	 * @param $filter
	 * @param $optional
	 * @param $maxMin
	 * @return int|string
	 * @throws \Dai\Framework\Base\BaseException
	 */
	private function getRequestParam($name, $value, $defaultValue, $type, $length, $regex, $filter, $optional,$maxMin) {
		if( $value == null ){
			if( $optional == true || $defaultValue != null) {
				return $defaultValue;
			} else {
				throw new BaseException( BaseException::PARAM_ERROR, $name);
			}
		}

		if( $type == "Int" ) {
			$value = intval($value);
		} elseif( $type == "String") {
			$value = strval($value);
		} elseif($type == "Double") {
			$value = (double)$value;
		} else if($type == "Float"){
			$value = floatval($value);
		}

		if( $regex != "") {
			if (! preg_match("/$regex/", $value)) {
				throw new BaseException(BaseException::PARAM_ERROR,"$name,$regex,$value");
			}
		}

		if( $length != ""){
			$lengthArr = explode(",", $length);
			if( count($lengthArr) == 1 &&  mb_strlen($value) != $lengthArr[0] ) {
				throw new BaseException(BaseException::PARAM_ERROR,$name.",".$lengthArr[0].",".strlen($value));
			}elseif( count($lengthArr) == 2 ){
				if( mb_strlen($value) < $lengthArr[0] || mb_strlen($value)> $lengthArr[1] ){
					throw new BaseException( BaseException::PARAM_ERROR, $name.",".$lengthArr[0].",".$lengthArr[1].",".mb_strlen($value));
				}
			}
		}
		if($maxMin != "") {
			$maxMinArr = explode(",", $maxMin);
			if($value < intval($maxMinArr[0])) {
				throw new BaseException( BaseException::MIN_LIMIT_FAIL, $name.",".$maxMinArr[0].",".$maxMinArr[1].",".$value);
			}
			if($value > intval($maxMinArr[1])) {
				throw new BaseException( BaseException::MAX_LIMIT_FAIL, $name.",".$maxMinArr[0].",".$maxMinArr[1].",".$value);
			}
		}

		if(!empty($filter)) {
			$value = $filter($value);
		}

		return $value;
	}
}

