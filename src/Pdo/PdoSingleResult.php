<?php

namespace JetFire\Db\Pdo;

use ArrayAccess;
use JetFire\Db\ResultInterface;
use PDO;

class PdoSingleResult implements ResultInterface,ArrayAccess {

    private $table;
    private $orm;

    private $type;

    public function __construct($table,$orm = null){
        $this->table = $table;
        if(isset($this->table->id))$this->type = 'update';
        if(!is_null($orm)) $this->orm = $orm;
    }

    private function getOrm(){
        if(is_callable($this->orm))
            $this->orm = call_user_func($this->orm);
        return $this->orm;
    }

    public function save(){
        $orm = $this->getOrm();
        if($this->type == 'update'){
            $orm->sql = 'WHERE id = :id';
            $orm->params['id'] = $this->table->id;
            return $orm->add($orm->params);
        }
        return $orm->insert($orm->params);
    }

    public function delete(){
        return $this->getOrm()->destroy($this->table->id);
    }

    public function __set($offset,$value){
        $this->getOrm()->params[$offset] = $value;
    }

    public function __get($offset){
        return $this->table->$offset;
    }

    public function __call($offset,$args){
        if(substr( $offset, 0, 3 ) == 'get') {
            $offset = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('get', '', $offset)));
            return $this->table->$offset;
        }elseif(substr( $offset, 0, 3 ) == 'set') {
            $offset = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('set', '', $offset)));
            $this->getOrm()->params[$offset] = $args[0];
        }
        return null;
    }

    public function offsetExists($offset)
    {
        return !is_null($this->table->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->table->$offset;
    }

    public function offsetSet($offset, $value)
    {
        $this->getOrm()->params[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        $this->getOrm()->params[$offset] = NULL;
    }
}