<?php

namespace JetFire\Db\RedBean;

use JetFire\Db\IteratorResult;
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
     * @var
     */
    private $instance;

    /**
     * @return mixed
     */
    public function getInstance(){
        if(is_null($this->instance))
            $this->instance = new $this->class;
        return $this->instance;
    }

    /**
     * @param $table
     * @return $this
     */
    public function setTable($table)
    {
        $this->class = $table;
        $class = explode('\\', $table);
        $this->table = $this->prefix . String::pluralize(strtolower($end = end($class)));
        $this->alias = strtolower(substr($end, 0, 1));
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
        if(isset($this->options['repositories']) && is_array($this->options['repositories']))
            foreach($this->options['repositories'] as $repo) {
                $class = explode('\\', $this->class);$class = end($class);
                if (is_file(rtrim($repo['path'], '/') . '/' . $class . 'Repository.php')) {
                    $class = $repo['namespace']  . $class . 'Repository';
                    return new $class();
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
            ? new IteratorResult(R::getAll($sql, $params),'redbean')
            : R::exec($sql, $params);
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return array
     */
    public function all()
    {
        return new IteratorResult(R::findAll($this->table),'redbean');
    }

    /**
     * @param $id
     * @return \RedBeanPHP\OODBBean
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
        if (!is_null($value)) $this->params = array_merge($this->params,(array)$value );
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
        if(is_null($this->sql))return new RedBeanSingleResult(R::xdispense($this->table));
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' '. $this->alias . $this->sql : $this->sql;
        $query = $this->execQuery($this->sql, $this->params);
        $this->sql = '';
        $this->params = [];
        return ($single || count($query) == 1) ? new RedBeanSingleResult($query[0]) : new IteratorResult($query,'redbean');
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

    /**
     * @param $content
     * @return bool
     */
    public function remove($content)
    {
        R::trash($content);
        return true;
    }

}