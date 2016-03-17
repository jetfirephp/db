<?php

namespace JetFire\Db\RedBean;

use ArrayAccess;
use JetFire\Db\ResultInterface;
use RedBeanPHP\R;

/**
 * Class RedBeanSingleResult
 * @package JetFire\Db\RedBean
 */
class RedBeanSingleResult implements ResultInterface,ArrayAccess {

    /**
     * @var
     */
    private $table;

    /**
     * @param $table
     */
    public function __construct($table){
        $this->table = $table;
    }

    /**
     *
     */
    public function save(){
        R::store($this->table);
    }

    /**
     *
     */
    public function delete(){
        R::trash($this->table);
    }

    /**
     * @param $offset
     * @param $value
     * @return mixed|void
     */
    public function __set($offset,$value){
        $this->table->$offset = $value;
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function __get($offset){
        return $this->table[$offset];
    }

    /**
     * @param $offset
     * @param $args
     * @return null
     */
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

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->table[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->table[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->table[$offset] = $value;
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->table[$offset]);
    }
}