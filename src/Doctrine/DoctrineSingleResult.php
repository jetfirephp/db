<?php

namespace JetFire\Db\Doctrine;

use ArrayAccess;
use JetFire\Db\ResultInterface;

/**
 * Class DoctrineSingleResult
 * @package JetFire\Db\Doctrine
 */
class DoctrineSingleResult implements ResultInterface, ArrayAccess
{

    /**
     * @var
     */
    private $table;
    /**
     * @var null
     */
    private $em;

    /**
     * @param $table
     * @param null $em
     */
    public function __construct($table, $em = null)
    {
        $this->table = $table;
        if ($em !== null) {
            $this->em = $em;
        }
    }

    /**
     * @return mixed
     */
    public function _getTable()
    {
        return $this->table;
    }

    /**
     * @return mixed
     */
    public function _serialize()
    {
        return json_decode(json_encode($this->table), true);
    }

    /**
     *
     */
    public function save()
    {
        $em = call_user_func($this->em);
        $em->persist($this->table);
        $em->flush();
    }

    /**
     *
     */
    public function persist()
    {
        $em = call_user_func($this->em);
        $em->persist($this->table);
    }

    /**
     *
     */
    public function flush()
    {
        $em = call_user_func($this->em);
        $em->flush();
    }

    /**
     *
     */
    public function delete()
    {
        $em = call_user_func($this->em);
        $em->remove($this->table);
        $em->flush();
    }

    /**
     * @param $offset
     * @param $value
     * @return void
     */
    public function __set($offset, $value)
    {
        $offset = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $offset)));
        $this->table->$offset($value);
    }

    /**
     * @param $offset
     * @return mixed
     */
    public function __get($offset)
    {
        if (is_array($this->table)) {
            return $this->table[$offset];
        }
        $offset = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $offset)));
        return $this->table->$offset();
    }

    /**
     * @param $offset
     * @param $args
     * @return mixed
     */
    public function __call($offset, $args)
    {
        if (is_array($this->table)) {
            $offset = strtolower(preg_replace('/\B([A-Z])/', '_$1', str_replace('get', '', $offset)));
            return $this->table[$offset];
        }
        return $this->table->$offset($args);
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        if (is_array($this->table)) {
            return isset($this->table[$offset]);
        }
        $offset = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $offset)));
        return $this->table->$offset() !== null;
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (is_array($this->table)) {
            return $this->table[$offset];
        }
        $offset = 'get' . str_replace(' ', '', ucwords(str_replace('_', ' ', $offset)));
        return $this->table->$offset();
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $offset = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $offset)));
        $this->table->$offset($value);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        $offset = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $offset)));
        $this->table->$offset(NULL);
    }
}