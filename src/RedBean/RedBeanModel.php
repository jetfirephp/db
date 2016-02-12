<?php

namespace JetFire\Dbal\RedBean;


use JetFire\Dbal\ModelInterface;
use JetFire\Dbal\String;
use RedBeanPHP\R;

class RedBeanModel extends RedBeanConstructor implements ModelInterface{

    private $table;
    private $calledTable;
    private $sql;
    private $params = [];

    public function setTable($table)
    {
        $class = explode('\\',$table);
        $this->table = $this->prefix.String::pluralize(strtolower(end($class)));
        return $this;
    }

    public function repo()
    {
        return new R;
    }

    public function em()
    {
        return new R;
    }

    public function query($query)
    {
        // TODO: Implement query() method.
    }

    public function queryBuilder()
    {
        // TODO: Implement queryBuilder() method.
    }

    public function all()
    {
        return R::findAll($this->table);
    }

    public function find($id)
    {
        return R::load( $this->table, $id);
    }

    public function sql($sql, $params = [])
    {
        if(strpos('?',$sql) === false) {
            $result = [];
            foreach ($params as $param)
                $result[':'.$param] = $param;
            $params = $result;
        }
        return R::getAll( $sql,
            $params
        );
    }

    public function select()
    {
        // TODO: Implement select() method.
    }

    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        // TODO: Implement where() method.
    }

    public function orWhere($key, $operator = null, $value = null)
    {
        // TODO: Implement orWhere() method.
    }

    public function whereRaw($sql, $value = null)
    {
        // TODO: Implement whereRaw() method.
    }

    public function orderBy($value, $order = 'ASC')
    {
        // TODO: Implement orderBy() method.
    }

    public function take($value, $array = false)
    {
        // TODO: Implement take() method.
    }

    public function get($array = false)
    {
        // TODO: Implement get() method.
    }

    public function getArray($array = false)
    {
        // TODO: Implement getArray() method.
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    public function update($id = null, $contents = null)
    {
        if (!is_null($id))
            $this->calledTable = R::load( $this->table, $id );
        return (is_null($contents)) ? $this : $this->with($contents);
    }

    public function with($contents)
    {
        if(!empty($this->calledTable)) {
            foreach ($contents as $key => $content)
                $this->calledTable[$key] = $content;
            R::store($this->calledTable);
            return true;
        }
        return false;
    }

    public function set($contents)
    {
        $update = 'UPDATE '.$this->table.' SET';
        foreach ($contents as $key => $content) {
            $update .= ' ' . $key .' = :' . $key . ',';
            $this->params[':'.$key] = $content;
        }
        $this->sql = substr($update, 0, -1) . $this->sql;
        return R::exec($this->sql,$this->params);
    }

    public function create($contents = null)
    {
        $table = R::xdispense($this->table);
        if(!is_null($contents))
            foreach($contents as $key => $content){
                $table[$key] = $content;
            }
        return R::store($table);
    }

    public function save()
    {
        // TODO: Implement save() method.
    }

    public function watch($entity = null)
    {
        // TODO: Implement watch() method.
    }

    public function watchAndSave($entity = null)
    {
        // TODO: Implement watchAndSave() method.
    }

    public function delete()
    {
        // TODO: Implement delete() method.
    }

    public function remove($content)
    {
        R::trash( $content );
        return true;
    }

    public function destroy()
    {
        // TODO: Implement destroy() method.
    }

    public function callStatic($name, $args)
    {
        // TODO: Implement callStatic() method.
    }
}