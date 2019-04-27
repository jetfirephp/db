<?php

namespace JetFire\Db\RedBean;

use JetFire\Db\IteratorResult;
use JetFire\Db\ModelInterface;
use JetFire\Db\TextTransform;
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
    public $class;
    /**
     * @var
     */
    private $table;
    /**
     * @var
     */
    private $alias;
    /**
     * @var
     */
    private $sql;
    /**
     * @var array
     */
    private $params = [];
    /**
     * @var
     */
    private $instance;

    /**
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->class = $table;
        $class = explode('\\', $table);
        $class = end($class);
        $this->table = isset($this->options['prefix']) ? $this->options['prefix'] . TextTransform::pluralize(strtolower($class)) : TextTransform::pluralize(strtolower($class));
        $this->alias = strtolower(substr($class, 0, 1));
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTable()
    {
        return $this->table;
    }

//|---------------------------------------------------------------------------------|
//| Getters are managed here                                                        |
//|---------------------------------------------------------------------------------|
    /**
     * @return R
     */
    public function getOrm()
    {
        return new R;
    }

    /**
     * @return null
     */
    public function repo()
    {
        if (isset($this->options['repositories']) && is_array($this->options['repositories'])) {
            foreach ($this->options['repositories'] as $repo) {
                $class = explode('\\', $this->class);
                $class = end($class);
                if (is_file(rtrim($repo['path'], '/') . '/' . $class . 'Repository.php')) {
                    $class = $repo['namespace'] . $class . 'Repository';
                    return new $class();
                }
            }
        }
        return null;
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
            foreach ($params as $key => $param) {
                $result[':' . $key] = $param;
            }
            $params = $result;
        }
        return $this->execQuery($sql, $params);
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return IteratorResult
     */
    public function all()
    {
        return new IteratorResult(R::findAll($this->table), 'redbean');
    }

    /**
     * @param $id
     * @return RedBeanSingleResult
     */
    public function find($id)
    {
        return new RedBeanSingleResult(R::load($this->table, $id));
    }

    /**
     * @return $this
     */
    public function select()
    {
        $this->sql = 'SELECT';
        $args = func_get_args();
        if (count($args) === 0) {
            $this->sql .= ' *,';
        }
        foreach ($args as $arg) {
            $this->sql .= ' ' . $this->alias . '.' . $arg . ',';
        }
        $this->sql = substr($this->sql, 0, -1) . ' FROM ' . $this->table . ' ' . $this->alias;
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
        if (!empty($this->sql) && strpos($this->sql, 'SELECT') === 0 && strpos($this->sql, 'WHERE') === false) {
            $this->sql .= ' WHERE';
        }
        if (empty($this->sql)) {
            $this->sql = ' WHERE';
        }
        if ($value === null || $boolean === 'OR') {
            list($key, $operator, $value) = array($key, '=', $operator);
        }
        $param = $key;
        if (strpos($this->sql, ':' . $key) !== false) {
            $key = $param . '_' . uniqid('', true);
        }
        $sql_key = ($operator === 'IN' || $operator === 'NOT IN') ? '(:' . $key . ')' : ':' . $key;
        $this->sql .= (substr($this->sql, -6) === ' WHERE')
            ? ' ' . "$this->alias.$param $operator $sql_key"
            : ' ' . $boolean . ' ' . "$this->alias.$param $operator $sql_key";
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
        if (!empty($this->sql) && strpos($this->sql, 'SELECT') === 0) {
            $this->sql .= ' WHERE ';
        }
        if (empty($this->sql)) {
            $this->sql = ' WHERE ';
        }
        $this->sql .= $sql;
        if ($value !== null) {
            $this->params = array_merge($this->params, (array)$value);
        }
        return $this;
    }

    /**
     * @param $value
     * @param string $order
     * @return $this
     */
    public function orderBy($value, $order = 'ASC')
    {
        $this->sql .= ' ORDER BY ' . $this->alias . '.' . $value . ' ' . $order;
        return $this;
    }


    /**
     * @param $limit
     * @param null $first
     * @param bool $single
     * @return IteratorResult|RedBeanSingleResult|mixed
     */
    public function take($limit, $first = null, $single = false)
    {
        if ($this->sql === null) {
            $this->sql = 'SELECT * FROM ' . $this->table . ' ' . $this->alias;
        }
        $this->sql .= ' LIMIT ' . $limit;
        if ($first !== null) {
            $this->sql .= ' OFFSET ' . $first;
        }
        $result = R::getAll($this->sql, $this->params);
        $this->sql = null;
        $this->params = [];
        return ($single && count($result) === 1)
            ? new RedBeanSingleResult($result)
            : new IteratorResult($result, 'redbean');
    }

    /**
     * @param bool $single
     * @return array|int|object
     */
    public function get($single = false)
    {
        if ($this->sql === null) {
            return new RedBeanSingleResult(R::xdispense($this->table));
        }
        $this->sql = (strpos($this->sql, 'SELECT') !== 0) ? 'SELECT * FROM ' . $this->table . ' ' . $this->alias . $this->sql : $this->sql;
        $result = R::getAll($this->sql, $this->params);
        $this->sql = null;
        $this->params = [];
        return ($single && count($result) === 1)
            ? new RedBeanSingleResult($result[0])
            : new IteratorResult($result, 'redbean');
    }

    /**
     * @return int|mixed|string
     */
    public function count()
    {
        if ($this->sql === null) {
            return R::count($this->table);
        }
        $this->sql = 'SELECT COUNT(*) FROM ' . $this->table . ' ' . $this->alias . $this->sql;
        $result = R::getCell($this->sql, $this->params);
        $this->sql = null;
        return $result;
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param int|string $id
     * @param null $contents
     * @return bool|RedBeanModel
     */
    public function update($id, $contents = null)
    {
        $this->instance = R::load($this->table, $id);
        return $contents === null ? $this : $this->with($contents);
    }

    /**
     * @param $contents
     * @return bool
     */
    public function with($contents)
    {
        if ($this->instance !== null) {
            foreach ($contents as $key => $content) {
                $this->instance[$key] = $content;
            }
            R::store($this->instance);
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
        $update = 'UPDATE ' . $this->table . ' ' . $this->alias . ' SET';
        foreach ($contents as $key => $content) {
            $update .= ' ' . $this->alias . '.' . $key . ' = :' . $key . ',';
            $this->params[':' . $key] = $content;
        }
        $this->sql = substr($update, 0, -1) . $this->sql;
        return R::exec($this->sql, $this->params) >= 1;
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
        $this->instance = R::xdispense($this->table);
        return $contents === null ? $this : $this->with($contents);
    }


//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|


    /**
     * @return array|int
     */
    public function delete()
    {
        $this->sql = 'DELETE FROM ' . $this->table . ' ' . $this->alias . ' ' . $this->sql;
        $query = R::exec($this->sql, $this->params);
        $this->sql = null;
        $this->params = [];
        return $query;
    }


    /**
     * @return bool
     */
    public function destroy()
    {
        $ids = func_get_args();
        foreach ($ids as $id) {
            R::trash($this->find($id));
        }
        return true;
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
        if (method_exists($this, $name)) {
            return call_user_func_array([$this, $name], $args);
        }
        return call_user_func_array([$this->getOrm(), $name], $args);
    }

//|---------------------------------------------------------------------------------|
//| Custom methods                                                                  |
//|---------------------------------------------------------------------------------|

    /**
     * @param $table
     * @return bool
     */
    public function remove($table)
    {
        R::trash($table);
        return true;
    }

    /**
     * @param $sql
     * @param $params
     * @return int|IteratorResult
     */
    private function execQuery($sql, $params)
    {
        return (stripos($sql, 'select') === 0)
            ? new IteratorResult(R::getAll($sql, $params), 'redbean')
            : R::exec($sql, $params);
    }
}