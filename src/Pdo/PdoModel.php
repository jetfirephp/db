<?php

namespace JetFire\Db\Pdo;


use JetFire\Db\IteratorResult;
use JetFire\Db\ModelInterface;
use JetFire\Db\String;
use PDO;

/**
 * Class PdoModel
 * @package JetFire\Db\Pdo
 */
class PdoModel extends PdoConstructor implements ModelInterface
{

    /**
     * @var
     */
    public $class;
    /**
     * @var
     */
    public $table;
    /**
     * @var
     */
    public $alias;
    /**
     * @var
     */
    public $sql;
    /**
     * @var array
     */
    public $params = [];

    /**
     * @var
     */
    public $instance;

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
     * @return mixed
     */
    public function getOrm()
    {
        return $this->pdo;
    }

    /**
     * @param $sql
     * @param array $params
     * @return mixed
     */
    public function sql($sql, $params = [])
    {
        if(substr(strtolower($sql), 0, 6) !== 'select')
            return $this->pdo->exec($sql);
        $results = $this->pdo->query($sql);
        return $results->fetchAll(PDO::FETCH_OBJ);
    }

    /**
     * @return mixed
     */
    public function all()
    {
        $results = $this->pdo->query('SELECT * FROM '.$this->table);
        return new IteratorResult($results->fetchAll(PDO::FETCH_OBJ));
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id)
    {
        $result = $this->pdo->prepare('SELECT * FROM '.$this->table.' WHERE id = :id');
        $result->bindValue('id',$id,PDO::PARAM_INT);
        $result->execute();
        return new PdoSingleResult($result->fetch(PDO::FETCH_OBJ),function(){return $this;});
    }

    /**
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function orWhere($key, $operator = null, $value = null)
    {
        return $this->where($key, $operator, $value, 'OR');
    }

    /**
     * @param $sql
     * @param null $value
     * @return mixed
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
     * @return mixed
     */
    public function orderBy($value, $order = 'ASC')
    {
        $this->sql .= ' ORDER BY ' . $value . ' ' . $order;
        return $this;
    }

    /**
     * @param $value
     * @param bool $single
     * @internal param bool $array
     * @return mixed
     */
    public function take($value, $single = false)
    {
        // TODO: Implement take() method.
    }

    /**
     * @param bool $single
     * @return mixed
     */
    public function get($single = false)
    {
        if(is_null($this->sql))return new PdoSingleResult($this->getInstance(),function(){return $this;});
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' ' . $this->alias .$this->sql : $this->sql;
        $query = $this->pdo->prepare($this->sql);
        foreach($this->params as $key => $value) {
            if(is_int($value))
                $query->bindValue($key, $value,PDO::PARAM_INT);
            elseif(is_string($value))
                $query->bindValue($key, $value,PDO::PARAM_STR);
            else
                $query->bindValue($key, $value);
        }
        $query->execute();
        $this->sql = '';
        $this->params = [];
        $result = $query->fetchAll(PDO::FETCH_OBJ);
        return ($single || count($result) == 1) ? new PdoSingleResult($result[0],function(){return $this;}) : new IteratorResult($result);
    }

    /**
     * @return mixed
     */
    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @param null $id
     * @param null $contents
     * @return mixed
     */
    public function update($id = null, $contents = null)
    {
        // TODO: Implement update() method.
    }

    /**
     * @param $contents
     * @return mixed
     */
    public function with($contents)
    {
        // TODO: Implement with() method.
    }

    /**
     * @param $contents
     * @return mixed
     */
    public function set($contents)
    {
        // TODO: Implement set() method.
    }

    /**
     * @param null $contents
     * @return mixed
     */
    public function create($contents = null)
    {
        // TODO: Implement create() method.
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        // TODO: Implement delete() method.
    }

    /**
     * @return mixed
     */
    public function destroy()
    {
        // TODO: Implement destroy() method.
    }

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
}