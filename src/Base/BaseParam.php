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
			$comment = $reflectionProperty->getDocComment()."\n";
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
			if( $optional == "" && $length == "" && $regex == "") {
				$optional = ($length == "" && $regex == "") ? true : false;
			}
			$value = $this->getRequestParam($name, $value, $defaultValue, $type, $length, $regex, $optional);
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
	 * @param $optional
	 * @return int|string
	 * @throws \Dai\Framework\Base\BaseException
	 */
	private function getRequestParam($name, $value, $defaultValue, $type, $length, $regex, $optional)
	{
		// 如果没有传来参数
		if( $value == null ){
			if( $optional == true || $defaultValue != null){
				return $defaultValue;
			}else{
				echo "传入参数有误";die;
				//                throw new BaseException( BaseException::PARAM_ERROR, $name);
			}
		}

		if( $type == "Int"  ){
			$value = intval($value);
		}elseif( $type == "String"){
			$value = strval($value);
		}

		//如果正则不匹配
		if( $regex != "") {
			if (! preg_match("/$regex/", $value)) {
				echo "正则不匹配";die;
				//                throw new BaseException( BaseException::PARAM_ERROR, "$name,$regex,$value");
			}
		}

		if( $length != ""){
			//如果长度不准确
			$lengthArr = explode(",", $length);
			if( count($lengthArr) == 1 &&  mb_strlen($value) != $lengthArr[0] ) {
				echo "长度错误";die;
				//                throw new BaseException( BaseException::PARAM_ERROR, $name.",".$lengthArr[0].",".strlen($value));
			}elseif( count($lengthArr) == 2 ){
				if( mb_strlen($value) < $lengthArr[0] || mb_strlen($value)> $lengthArr[1] ){
					echo "长度错误2";die;
					//                    throw new BaseException( BaseException::PARAM_ERROR, $name.",".$lengthArr[0].",".$lengthArr[1].",".strlen($value));
				}
			}
		}
		return $value;
	}
}
