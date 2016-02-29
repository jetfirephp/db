<?php

namespace JetFire\Db\RedBean;

use JetFire\Db\ModelInterface;
use JetFire\Db\String;
use RedBeanPHP\R;

class RedBeanModel extends RedBeanConstructor implements ModelInterface{

    private $table;
    private $calledTable;
    private $sql;
    private $params = [];

    public function setTable($table)
    {
        if(is_null($this->table)) {
            $class = explode('\\', $table);
            $this->table = $this->prefix . String::pluralize(strtolower(end($class)));
        }
        return $this;
    }

    public function getOrm()
    {
        return new R;
    }

    public function sql($sql, $params = [])
    {
        if(strpos('?',$sql) === false) {
            $result = [];
            foreach ($params as $key => $param)
                $result[':'.$key] = $param;
            $params = $result;
        }
        return $this->execQuery($sql,$params);
    }

    private function execQuery($sql,$params){
        return (strtolower(substr($sql,0,6 )) === 'select')
            ? R::getAll( $sql, $params)
            : R::exec( $sql, $params);
    }

    public function query($query)
    {
        return (substr(strtolower($query),0,6 ) === 'select')
            ? R::getAll( $query)
            : R::exec( $query);
    }

    public function all()
    {
        return R::findAll($this->table);
    }

    public function find($id)
    {
        return R::load( $this->table, $id);
    }

    public function select()
    {
        $this->sql = 'SELECT';
        $args = func_get_args();
        if(count($args) == 0)$this->sql .= ' *,';
        foreach ($args as $arg)
            $this->sql .= ' ' . $arg . ',';
        $this->sql = substr($this->sql, 0, -1) . ' FROM ' . $this->table;
        return $this;
    }

    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        if (!empty($this->sql) && substr($this->sql, 0, 6) == 'SELECT' && strpos($this->sql, 'WHERE') === false) $this->sql .= ' WHERE';
        if (empty($this->sql)) $this->sql = ' WHERE';
        if (is_null($value)|| $boolean == 'OR') list($key, $operator, $value) = array($key, '=', $operator);
        // if we update or delete the entity
       /* if (!empty($this->sql) && strpos($this->sql, 'WHERE') === false) {
            if (is_null($this->sql->getParameter($key)))
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key")->setParameter($key, $value);
            else
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key" . '_' . $value)->setParameter($key . '_' . $value, $value);
            return $this;
        }*/

        //if we read the entity
        $param = $key;
        if (strpos($this->sql, ':' . $key) !== false) $key = $param . '_' . uniqid();
        $this->sql .= (substr($this->sql, -6) == ' WHERE')
            ? ' ' . "$param $operator :$key"
            : ' ' . $boolean . ' ' . "$param $operator :$key";
        $this->params[$key] = $value;
        return $this;
    }

    public function orWhere($key, $operator = null, $value = null)
    {
        return $this->where($key, $operator, $value, 'OR');
    }

    public function whereRaw($sql, $value = null)
    {
        if (!empty($this->sql) && substr($this->sql, 0, 6) == 'SELECT') $this->sql .= ' WHERE ';
        if (empty($this->sql)) $this->sql = ' WHERE ';
        $this->sql .= $sql;
        if(!is_null($value))$this->params = array_merge($this->params,$value);
        return $this;
    }

    public function orderBy($value, $order = 'ASC')
    {
        $this->sql .= ' ORDER BY '.$value.' '.$order;
        return $this;
    }

    public function take($value, $single = false)
    {
        // TODO: Implement take() method.
    }

    public function get($single = false)
    {
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' ' . $this->sql : $this->sql;
        $query = $this->execQuery($this->sql,$this->params);
        $this->sql = '';
        $this->params = [];
        return $single ? (object)$query[0] :$query;
    }

    public function getArray($single = false)
    {
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' ' . $this->sql : $this->sql;
        $query = $this->execQuery($this->sql,$this->params);
        $this->sql = '';
        $this->params = [];
        return $single ? $query[0] :$query;
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    public function update($id = null, $contents = null)
    {
        if (!is_null($id))
            $this->calledTable = R::load( $this->table, $id );
        return (is_null($contents)) ? $this : $this->with($contents);
    }

    public function with($contents)
    {
        if(!is_null($this->calledTable)) {
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
        return (R::exec($this->sql,$this->params) >= 1)?true:false;
    }

//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|


    public function create($contents = null)
    {
        $table = R::xdispense($this->table);
        if(!is_null($contents))
            foreach($contents as $key => $content){
                $table[$key] = $content;
            }
        return R::store($table);
    }


//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|


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

//|---------------------------------------------------------------------------------|
//| Call Static                                                                     |
//|---------------------------------------------------------------------------------|

    /**
     * @param $name
     * @param $args
     * @return mixed
     */
    public function callStatic($name, $args)
    {
        if(method_exists($this,$name))
            return call_user_func_array([$this,$name],$args);
        return call_user_func_array([$this->getOrm(),$name],$args);
    }

//|---------------------------------------------------------------------------------|
//| Custom methods                                                                  |
//|---------------------------------------------------------------------------------|


}