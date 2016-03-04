<?php

namespace JetFire\Db\Pdo;

use ArrayAccess;
use JetFire\Db\ResultInterface;
use PDO;

class PdoSingleResult implements ResultInterface,ArrayAccess {

    private $table;
    private $orm;

    private $type = 'create';

    public function __construct($table,$orm = null){
        $this->table = $table;
        if(isset($this->table->id))$this->type = 'update';
        if(!is_null($orm)) $this->orm = $orm;
    }

    public function save(){
        if($this->type == 'update')
            $result = $this->update();
        elseif($this->type == 'create')
            $result = $this->create();
        $result->execute();
    }

    private function update(){
        $orm = call_user_func($this->orm);
        foreach($orm->params as $key => $value)
            $orm->sql .= $key.' = :'.$key.',';
        $result = $orm->getOrm()->prepare('UPDATE '.$orm->table.' SET '.substr($orm->sql, 0, -1).' WHERE id = :id');
        foreach($orm->params as $key => $value)
            $result->bindValue($key,$value);
        $result->bindValue('id',$this->table->id,PDO::PARAM_INT);
        return $result;
    }

    private function create(){
        $orm = call_user_func($this->orm);
        $values = '';
        foreach($orm->params as $key => $value) {
            $orm->sql .= $key . ',';
            $values .= ':'.$key.',';
        }
        $result = $orm->getOrm()->prepare('INSERT INTO '.$orm->table.'('.substr($orm->sql, 0, -1).') VALUES ('.substr($values,0,-1).')');
        foreach($orm->params as $key => $value)
            $result->bindValue($key,$value);
        return $result;
    }

    public function delete(){

    }

    public function __set($offset,$value){
        $this->orm->params[$offset] = $value;
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
            $this->orm->params[$offset] = $args[0];
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
        $this->orm->params[$offset] = $value;
    }

    public function offsetUnset($offset)
    {

    }
}