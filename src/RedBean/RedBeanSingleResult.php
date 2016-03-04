<?php

namespace JetFire\Db\RedBean;

use ArrayAccess;
use JetFire\Db\ResultInterface;
use RedBeanPHP\R;

class RedBeanSingleResult implements ResultInterface,ArrayAccess {

    private $table;

    public function __construct($table){
        $this->table = $table;
    }

    public function save(){
        R::store($this->table);
    }

    public function delete(){
        R::trash($this->table);
    }

    public function __set($offset,$value){
        $this->table->$offset = $value;
    }

    public function __get($offset){
        return $this->table[$offset];
    }

    public function __call($offset,$args){
        if(substr( $offset, 0, 3 ) == 'get') {
            $offset = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('get', '', $offset)));
            return $this->table[$offset];
        }elseif(substr( $offset, 0, 3 ) == 'set') {
            $offset = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('set', '', $offset)));
            $this->table->$offset = $args[0];
        }
        return null;
    }

    public function offsetExists($offset)
    {
        return isset($this->table[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->table[$offset];
    }

    public function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    public function offsetUnset($offset)
    {

    }
}