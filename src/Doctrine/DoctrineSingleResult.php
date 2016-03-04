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
        $offset = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        return $this->table->$offset();
    }

    public function __call($offset,$args){
        return $this->table->$offset($args);
    }

    public function offsetExists($offset)
    {
        $offset = 'get'.str_replace(' ','',ucwords(str_replace('_',' ',$offset)));
        return !is_null($this->table->$offset());
    }

    public function offsetGet($offset)
    {
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

    }
}