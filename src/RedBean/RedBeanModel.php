<?php

namespace JetFire\Db\RedBean;

use JetFire\Db\ModelInterface;
use JetFire\Db\String;
use RedBeanPHP\R;

/**
 * Class RedBeanModel
 * @package JetFire\Db\RedBean
 */
class RedBeanModel extends RedBeanConstructor implements ModelInterface
{

    /**
     * @var
     */
    private $table;
    /**
     * @var
     */
    private $calledTable;
    /**
     * @var
     */
    private $sql;
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $class = explode('\\', $table);
        $this->table = $this->prefix . String::pluralize(strtolower(end($class)));
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return R
     */
    public function getOrm()
    {
        return new R;
    }

    /**
     * @param $sql
     * @param array $params
     * @return array|int
     */
    public function sql($sql, $params = [])
    {
        if (strpos('?', $sql) === false) {
            $result = [];
            foreach ($params as $key => $param)
                $result[':' . $key] = $param;
            $params = $result;
        }
        return $this->execQuery($sql, $params);
    }

    /**
     * @param $sql
     * @param $params
     * @return array|int
     */
    private function execQuery($sql, $params)
    {
        return (strtolower(substr($sql, 0, 6)) === 'select')
            ? R::getAll($sql, $params)
            : R::exec($sql, $params);
    }

    /**
     * @param $query
     * @return array|int
     */
    public function query($query)
    {
        return (substr(strtolower($query), 0, 6) === 'select')
            ? R::getAll($query)
            : R::exec($query);
    }

    /**
     * @return array
     */
    public function all()
    {
        return R::findAll($this->table);
    }

    /**
     * @param $id
     * @return \RedBeanPHP\OODBBean
     */
    public function find($id)
    {
        return R::load($this->table, $id);
    }

    /**
     * @return $this
     */
    public function select()
    {
        $this->sql = 'SELECT';
        $args = func_get_args();
        if (count($args) == 0) $this->sql .= ' *,';
        foreach ($args as $arg)
            $this->sql .= ' ' . $arg . ',';
        $this->sql = substr($this->sql, 0, -1) . ' FROM ' . $this->table;
        return $this;
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @param string $boolean
     * @return $this
     */
    public function where($key, $operator = null, $value = null, $boolean = "AND")
    {
        if (!empty($this->sql) && substr($this->sql, 0, 6) == 'SELECT' && strpos($this->sql, 'WHERE') === false) $this->sql .= ' WHERE';
        if (empty($this->sql)) $this->sql = ' WHERE';
        if (is_null($value) || $boolean == 'OR') list($key, $operator, $value) = array($key, '=', $operator);
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

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return RedBeanModel
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        return $this->where($key, $operator, $value, 'OR');
    }

    /**
     * @param $sql
     * @param null $value
     * @return $this
     */
    public function whereRaw($sql, $value = null)
    {
        if (!empty($this->sql) && substr($this->sql, 0, 6) == 'SELECT') $this->sql .= ' WHERE ';
        if (empty($this->sql)) $this->sql = ' WHERE ';
        $this->sql .= $sql;
        if (!is_null($value)) $this->params = array_merge($this->params, $value);
        return $this;
    }

    /**
     * @param $value
     * @param string $order
     * @return $this
     */
    public function orderBy($value, $order = 'ASC')
    {
        $this->sql .= ' ORDER BY ' . $value . ' ' . $order;
        return $this;
    }

    /**
     * @param $value
     * @param bool $single
     */
    public function take($value, $single = false)
    {
        // TODO: Implement take() method.
    }

    /**
     * @param bool $single
     * @return array|int|object
     */
    public function get($single = false)
    {
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' ' . $this->sql : $this->sql;
        $query = $this->execQuery($this->sql, $this->params);
        $this->sql = '';
        $this->params = [];
        return $single ? (object)$query[0] : $query;
    }

    /**
     * @param bool $single
     * @return array|int
     */
    public function getArray($single = false)
    {
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' ' . $this->sql : $this->sql;
        $query = $this->execQuery($this->sql, $this->params);
        $this->sql = '';
        $this->params = [];
        return $single ? $query[0] : $query;
    }

    /**
     *
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $id
     * @param null $contents
     * @return bool|RedBeanModel
     */
    public function update($id = null, $contents = null)
    {
        if (!is_null($id))
            $this->calledTable = R::load($this->table, $id);
        return (is_null($contents)) ? $this : $this->with($contents);
    }

    /**
     * @param $contents
     * @return bool
     */
    public function with($contents)
    {
        if (!is_null($this->calledTable)) {
            foreach ($contents as $key => $content)
                $this->calledTable[$key] = $content;
            R::store($this->calledTable);
            return true;
        }
        return false;
    }

    /**
     * @param $contents
     * @return bool
     */
    public function set($contents)
    {
        $update = 'UPDATE ' . $this->table . ' SET';
        foreach ($contents as $key => $content) {
            $update .= ' ' . $key . ' = :' . $key . ',';
            $this->params[':' . $key] = $content;
        }
        $this->sql = substr($update, 0, -1) . $this->sql;
        return (R::exec($this->sql, $this->params) >= 1) ? true : false;
    }

//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|


    /**
     * @param null $contents
     * @return int|string
     */
    public function create($contents = null)
    {
        $table = R::xdispense($this->table);
        if (!is_null($contents))
            foreach ($contents as $key => $content) {
                $table[$key] = $content;
            }
        return R::store($table);
    }


//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|


    /**
     *
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * @param $content
     * @return bool
     */
    public function remove($content)
    {
        R::trash($content);
        return true;
    }

    /**
     *
     */
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
        if (method_exists($this, $name))
            return call_user_func_array([$this, $name], $args);
        return call_user_func_array([$this->getOrm(), $name], $args);
    }

//|---------------------------------------------------------------------------------|
//| Custom methods                                                                  |
//|---------------------------------------------------------------------------------|


}