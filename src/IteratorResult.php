<?php

namespace JetFire\Db;


use Iterator;

/**
 * Class IteratorResult
 * @package JetFire\Db
 */
class IteratorResult implements Iterator {

    /**
     * @var array
     */
    private $results = [];

    /**
     * @var array
     */
    private $orms = [
        'pdo' => 'JetFire\Db\Pdo\PdoSingleResult',
        'doctrine' => 'JetFire\Db\Doctrine\DoctrineSingleResult',
        'redbean' => 'JetFire\Db\RedBean\RedBeanSingleResult',
    ];

    /**
     * @var string
     */
    private $orm;

    /**
     * @param array $results
     * @param string $orm
     */
    public function __construct($results = [],$orm = 'pdo'){
        $this->results = $results;
        $this->orm = $orm;
    }

    /**
     * @return int
     */
    public function getResults(){
        return $this->results;
    }

    /**
     * @return int
     */
    public function count(){
        return count($this->results);
    }

    /**
     * @return mixed
     */
    public function first(){
        return $this->results[0];
    }

    /**
     * @param $key
     * @return mixed
     */
    public function take($key){
        return $this->results[$key];
    }

    /**
     * @return mixed
     */
    public function last(){
        return $this->results[count($this->results)-1];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return new $this->orms[$this->orm](current($this->results));
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->results);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->results);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        $key = key($this->results);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->results);
    }
}