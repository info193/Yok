<?php
/**
 * Created by vim.
 * User: huguopeng
 * Date: 2019/05/13
 * Time: 18:06:07
 * By: BaseDao.php
 */
namespace Yok\Base;

use Yok\Library\Annotations;
use Yok\Library\ConfigLibrary;
use Yok\Library\Log;
use Yok\Library\Trace;

class BaseDao extends \Phalcon\Mvc\Model {
    protected $source = 'slave';
    protected function setMaster(){
        $this->source = 'master';
    }
    /**
     * @param $sql
     * @param $param
     * @return mixed
     */
    private function _selectFilter($sql,&$param){
        if(isset($param['groupBy']) && trim($param['groupBy']) != "") {
            $sql = str_replace("%GROUPBY", " GROUP BY ". $param['groupBy'], $sql);
            unset($param['groupBy']);
        } else {
            $sql = str_replace("%GROUPBY", "", $sql);
        }
        if(isset($param['orderBy']) && trim($param['orderBy']) != "") {
            $sql = str_replace("%ORDERBY", " ORDER BY ". $param['orderBy'], $sql);
            unset($param['orderBy']);
        } else {
            $sql = str_replace("%ORDERBY", "", $sql);
        }
        if(isset($param['limit']) && trim($param['limit']) != "") {
            $sql = str_replace("%LIMIT", " LIMIT ". $param['limit'], $sql);
            unset($param['limit']);
        } else {
            $sql = str_replace("%LIMIT", "", $sql);
        }
        return $sql;
    }

    /**
     * @param $sid
     * @param array $param
     * @return mixed
     * @throws BaseException
     */
    public function execute($sid,$param=[]) {
        if(empty($sid)){
            Log::error("sql sid error, not valid");
            throw new BaseException(BaseException::PARAM_ERROR);
        }
        $dirArray = explode(".",$sid);
        $sqlMapConf = CONFIG_PATH.'/sqlMap/'.$dirArray[0].'/'.$dirArray[1].'.ini';
        try {
            $sql = ConfigLibrary::geFromConfigFileByKey($sqlMapConf, $dirArray[2], "sql");
            if ($sql == null || trim($sql) == "") {
                Log::error("sql find error, [$sid] not valid");
                throw new BaseException(BaseException::PARAM_ERROR);
            }
            $table = ConfigLibrary::geFromConfigFileByKey($sqlMapConf, "table", "name");
            $sql = str_replace("%table", $table, $sql);
            if(is_object($param)) {
                $param = (array)$param;
            }
            if (is_array($param) && isset($param['select']) && is_array($param['select'])) {
                $sql = str_replace("%SELECT", implode(",", $param['select']), $sql);
                unset($param['select']);
            } else if (is_array($param) && isset($param['select']) && is_string($param['select'])) {
                $sql = str_replace("%SELECT", $param['select'], $sql);
                unset($param['select']);
            } else {
                $sql = str_replace("%SELECT", '*' , $sql);
            }
            if(is_array($param)) {
                if(isset($param['where']) && trim($param['where']) != "") {
                    $sql = str_replace("%WHERE", " WHERE ". $param['where'], $sql);
                    unset($param['select']);
                } else {
                    $sql = str_replace("%WHERE", "", $sql);
                }
            }
            $functionPre = explode("_", $dirArray[2]);
            $masterSource = ['insert','update','delete','edit'];
            if(in_array(strtolower($functionPre[0]),$masterSource) && $this->source !== 'master') {
                $this->source='master';
            }
            $this->pdo = $this->getDI()->getShared($this->source);
            $function = $functionPre[0]."Base";

            return $this->$function($sql,$param);
        } catch (\Exception $e) {
            Log::error("exec sql fail, $sid, $param, exp "  . $e->getMessage());
            throw new BaseException(BaseException::INTER_ERROR, $e->getMessage());
        }
    }

    /**
     * @param $sql
     * @param array $param
     * @return mixed
     */
    protected function selectBase($sql,$param){
        $sql = $this->_selectFilter($sql,$param);
        $selectData = [];
        if(!empty($param)){
            foreach ($param as $key => $item) {
                if( $item !== null){
                    $selectData["$key"] = $item;
                }
            }
        }
        return $this->querySql($this->pdo,$sql,$selectData);
    }

    /**
     * @param $sql
     * @param $param
     * @return mixed
     */
    protected function totalBase($sql,$param){
        $sql = $this->_selectFilter($sql,$param);
        $selectData = [];
        if(!empty($param)){
            foreach ($param as $key => $item) {
                if( $item !== null){
                    $selectData["$key"] = $item;
                }
            }
        }
        return $this->querySql($this->pdo,$sql,$selectData);
    }

    /**
     * @param $pdo
     * @param $sql
     * @param array $selectData
     * @return mixed
     */
    protected function querySql($pdo,$sql,$selectData = []){
        if(Trace::getInstance()->getValid() === true) {
            $key = $sql . $this->source . time() . rand(1,100000);
            $debugStr = $this->_debugStr($sql,$selectData);
            Trace::getInstance()->add($key,$debugStr);
        }
        try{
            $result = $pdo->query($sql,$selectData);
            $result->setFetchMode(\Phalcon\DB::FETCH_ASSOC);
            $ret = $result->fetch();
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$ret);
            }
            return $ret;
        } catch (\Exception $exception) {
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$exception->getMessage());
            }
            throw new  BaseException(BaseException::DB_ERROR,$exception->getMessage());
        }
    }

    /**
     * @param $sql
     * @param $param
     * @return mixed
     */
    protected function editBase($sql,$param){
        return $this->execSql($this->pdo,$sql,$param);
    }

    /**
     * @param $sql
     * @param $param
     * @return mixed
     */
    protected function deleteBase($sql,$param){
        return $this->execSql($this->pdo,$sql,$param);
    }

    /**
     * @param $sql
     * @param array $param
     * @return bool
     */
    protected function insertBase($sql,$param = []){
        if(empty($param)){
            Log::error("exec insert sql fail, $sql, $param ");
            throw new BaseException(BaseException::INTER_ERROR);
        }
        if(is_array($param)) {
            $tempKey = [];
            $tempKeyPre=[];
            $tempValue =[];
            foreach ($param as $key => $value) {
                if($value !== null){
                    $tempKey[] = $key;
                    $tempKeyPre[] = ":".$key;
                    $tempValue[$key] = $value;
                }
            }
            $insertStr = "(" . implode(',',$tempKey).') VALUES ('.implode(',',$tempKeyPre).')';
            $sql = str_replace("%INSERT", $insertStr, $sql);
        }
        if(Trace::getInstance()->getValid() === true) {
            $key = $sql . ";" . $this->source . time() . rand(1,100000);
            $debugStr = $this->_debugStr($sql,$tempValue);
            Trace::getInstance()->add($key,$debugStr);
        }
        try {
            $insertID = 0;
            $this->pdo->execute($sql, $tempValue);
            $insertID = $this->pdo->lastInsertId();
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$insertID);
            }
            return $insertID;
        } catch (\Exception $exception) {
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$exception->getMessage());
            }
            throw new  BaseException(BaseException::DB_ERROR,$exception->getMessage());
        }
    }

    /**
     * @param $pdo
     * @param $sql
     * @param $param
     * @return mixed
     */
    protected function execSql($pdo,$sql,$param){
        $where=[];
        if(!empty($param)){
            foreach ($param as $key => $item) {
                if( $item !== null){
                    $where["$key"] = $item;
                }
            }
        }
        if(Trace::getInstance()->getValid() === true) {
            $key = $sql . ";" . $this->source . time() . rand(1,100000);
            $debugStr = $this->_debugStr($sql,$where);
            Trace::getInstance()->add($key,$debugStr);
        }
        try{
            $pdo->execute($sql,$where);
            $result = $pdo->affectedRows();
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$result);
            }
        } catch (\Exception $exception) {
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$exception->getMessage());
            }
            throw new BaseException(BaseException::DB_ERROR,$exception->getMessage());
        }
        return $result;
    }

    /**
     * @param $sql
     * @return mixed
     * @throws BaseException
     */
    protected function queryRaw($sql){
        try{
            if(Trace::getInstance()->getValid() === true) {
                $key = $sql . ";" . $this->source . time() . rand(1,100000);
                Trace::getInstance()->add($key,$sql);
            }
            $result = $this->getDI()->getShared($this->source)->query($sql);
            $result->setFetchMode(\Phalcon\DB::FETCH_ASSOC);
            $ret = $result->fetchAll();
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$ret);
            }
            return $ret;
        } catch (\Exception $exception) {
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$exception->getMessage());
            }
            throw new BaseException(BaseException::DB_ERROR,$exception->getMessage());
        }
    }
    /**
     * @param $sql
     * @return mixed
     * @throws BaseException
     */
    protected function execRaw($sql){
        try{
            if(Trace::getInstance()->getValid() === true) {
                $key = $sql . ";" . $this->source . time() . rand(1,100000);
                Trace::getInstance()->add($key,$sql);
            }
            $result = $this->getDI()->getShared($this->source)->execute($sql);
            return $result;
        } catch (\Exception $exception) {
            if(Trace::getInstance()->getValid() === true) {
                Trace::getInstance()->attach($key,$exception->getMessage());
            }
            throw new BaseException(BaseException::DB_ERROR,$exception->getMessage());
        }
    }

    /**
     * @param $sql
     * @param $fields
     * @return mixed
     */
    private function _debugStr($sql,$fields){
        $debugStr = $sql;
        foreach ($fields as $key => $field) {
            $debugStr = str_replace(":$key","'$field'",$debugStr);
        }
        return $debugStr;
    }

    /**
     * @param array $data
     * @return mixed
     */
    protected function trimBase($data=[]){
        $classDaoName = get_called_class();
        $reflectionDao = new \ReflectionClass ( $classDaoName );

        $commentDao = $reflectionDao->getDocComment();
        $className = Annotations::getCommentValue($commentDao,'dataObject');

        $object = new $className();
        $reflection = new \ReflectionClass ( $className );
        foreach ($reflection->getProperties() as $property) {
            $propertyName = $property->name;
            if(empty($data[$propertyName])) {
                continue;
            }
            $value = $data[$propertyName];
            $reflectionProperty = new \ReflectionProperty($className, $propertyName);
            $comment = $reflectionProperty->getDocComment();
            $type = Annotations::getCommentValue($comment, 'type' );
            switch ($type) {
                case 'Int':
                    $object->$propertyName = (int) $value;
                    break;
                case  'Float':
                    $object->$propertyName = floatval($value);
                    break;
                case  'Double':
                    $object->$propertyName = doubleval($value);
                    break;
                case  'String':
                    $object->$propertyName = (string)$value;
                    break;
                case  'Json':
                    $object->$propertyName = json_encode($value);
                    break;
                case  'Date':
                    $object->$propertyName = date('Y-m-d',$value);
                    break;
                case  'DateTime':
                    $object->$propertyName = date('Y-m-d H:i:s',$value);
                    break;
                case  'Time':
                    $object->$propertyName = strtotime($value);
                    break;
                default:
                    $object->$propertyName = trim($value);
            }
        }
        return $object;
    }

    /**
     * @return mixed
     */
    public function begin(){
        return $this->getDI()->getShared('master')->query("begin");
    }

    /**
     * @return mixed
     */
    public function commit(){
        return $this->getDI()->getShared('master')->query("commit");
    }

    /**
     * @return mixed
     */
    public function rollback(){
        return $this->getDI()->getShared('master')->query("rollback");
    }
}

