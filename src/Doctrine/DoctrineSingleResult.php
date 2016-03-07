<?php

namespace JetFire\Db\Doctrine;

use ArrayAccess;
use JetFire\Db\ResultInterface;

class DoctrineSingleResult implements ResultInterface,ArrayAccess {

    private $table;
    private $em;

    public function __construct($table,$em = null){
        $this->table = $table;
        if(!is_null($em))$this->em = $em;
    }

    public function save(){
        $em = call_user_func($this->em);
        $em->persist($this->table);
        $em->flush();
    }

    public function persist(){
        $em = call_user_func($this->em);
        $em->persist($this->table);
    }

    public function flush(){
        $em = call_user_func($this->em);
        $em->flush();
    }

    public function delete(){
        $em = call_user_func($this->em);
        $em->remove($this->table);
        $em->flush();
    }

    public function __set($offset,$value){
        $offset = 'set'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        $this->table->$offset($value);
    }

    public function __get($offset){
        if(is_array($this->table))
            return $this->table[$offset];
        $offset = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        return $this->table->$offset();
    }

    public function __call($offset,$args){
        if(is_array($this->table)) {
            $offset = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('get', '', $offset)));
            return $this->table[$offset];
        }
        return $this->table->$offset($args);
    }

    public function offsetExists($offset)
    {
        if(is_array($this->table))
            return isset($this->table[$offset]);
        $offset = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        return !is_null($this->table->$offset());
    }

    public function offsetGet($offset)
    {
        if(is_array($this->table))
            return $this->table[$offset];
        $offset = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        return $this->table->$offset();
    }

    public function offsetSet($offset, $value)
    {
        $offset = 'set'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        $this->table->$offset($value);
    }

    public function offsetUnset($offset)
    {
        $offset = 'set'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        $this->table->$offset(NULL);
    }
}