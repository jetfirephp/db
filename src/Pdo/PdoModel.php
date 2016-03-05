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
        $this->table = (!isset($this->options['prefix'])?:$this->options['prefix']) . String::pluralize(strtolower($end = end($class)));
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
     * @param $limit
     * @param null $first
     * @param bool $single
     * @internal param bool $array
     * @return mixed
     */
    public function take($limit,$first = null, $single = false)
    {
        if(is_null($this->sql))
            $this->sql = 'SELECT * FROM ' . $this->table . ' '. $this->alias;
        $this->sql .= ' LIMIT '.$limit;
        if(!is_null($first))$this->sql .= ' OFFSET '.$first;
        $result = $this->execQuery($this->sql,$this->params);
        $result = $result->fetchAll(PDO::FETCH_OBJ);
        $this->sql = null;
        $this->params = [];
        return ($single && count($result) == 1)
            ? new PdoSingleResult($result[0],function(){return $this;})
            : new IteratorResult($result);
    }

    /**
     * @param bool $single
     * @return mixed
     */
    public function get($single = false)
    {
        if(is_null($this->sql))return new PdoSingleResult($this->getInstance(),function(){return $this;});
        $this->sql = (substr($this->sql, 0, 6) !== 'SELECT') ? 'SELECT * FROM ' . $this->table . ' ' . $this->alias .$this->sql : $this->sql;
        $result = $this->execQuery($this->sql,$this->params);
        $this->sql = null;$this->params = [];
        $result = $result->fetchAll(PDO::FETCH_OBJ);
        return ($single || count($result) == 1) ? new PdoSingleResult($result[0],function(){return $this;}) : new IteratorResult($result);
    }

    /**
     * @return mixed
     */
    public function count()
    {
        $this->sql = 'SELECT COUNT(*) FROM ' . $this->table . ' ' .$this->alias. $this->sql;
        $result = $this->execQuery($this->sql,$this->params);
        $this->sql = null;$this->params = [];
        return $result->fetch()[0];
    }

    /**
     * @param int|string $id
     * @param null $contents
     * @return mixed
     */
    public function update($id, $contents = null)
    {
        $this->sql = 'WHERE id = :id';
        $this->params['id'] = $id;
        return (is_null($contents))?$this:$this->add($contents);
    }

    /**
     * @param $contents
     * @return mixed
     */
    public function with($contents)
    {
        return (substr($this->sql, 0, -5) == 'WHERE')
            ? $this->add($contents)
            : $this->insert($contents);
    }

    /**
     * @param $contents
     * @return mixed
     */
    public function set($contents)
    {
        $sql = '';
        foreach($contents as $key => $value)
            $sql .= $key.' = :'.$key.',';
        $result = $this->pdo->prepare('UPDATE '.$this->table.' SET '.substr($sql, 0, -1).$this->sql);
        $this->params = array_merge($contents,$this->params);
        foreach($this->params as $key => $value)
            $result->bindValue($key,$value);
        $this->sql = null;
        $this->params = [];
        return $result->execute();
    }

    /**
     * @param null $contents
     * @return mixed
     */
    public function create($contents = null)
    {
        return is_null($contents)?$this:$this->insert($contents);
    }

    /**
     * @return mixed
     */
    public function delete()
    {
        $this->sql = 'DELETE FROM' . $this->table . ' '. $this->sql;
        $query = $this->execQuery($this->sql, $this->params);
        $this->sql = null;
        $this->params = [];
        return $query;
    }

    /**
     * @return mixed
     */
    public function destroy()
    {
        $ids = func_get_args();
        $sql = 'DELETE FROM '.$this->table.' WHERE id IN (';
        $params = [];
        foreach ($ids as $key => $id) {
            $sql .= '?,';
            $params[] = $id;
        }
        $result = $this->pdo->prepare(substr($sql,0,-1).')');
        foreach($params as $key => $value)
            $result->bindValue($key,$value,PDO::PARAM_INT);
        return $result->execute();
    }

    public function clear(){
        return $this->pdo->exec('TRUNCATE TABLE '.$this->table);
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

    public function add($contents){
        $sql = '';
        foreach($contents as $key => $value)
            $sql .= $key.' = :'.$key.',';
        $result = $this->pdo->prepare('UPDATE '.$this->table.' SET '.substr($sql, 0, -1).' '.$this->sql);
        foreach($contents as $key => $value)
            $result->bindValue($key,$value);
        $result->bindValue('id',$this->params['id'],PDO::PARAM_INT);
        $this->sql = '';
        $this->params = [];
        return $result->execute();
    }

    public function insert($contents){
        $values = '';
        foreach($contents as $key => $value) {
            $this->sql .= $key . ',';
            $values .= ':'.$key.',';
        }
        $result = $this->pdo->prepare('INSERT INTO '.$this->table.'('.substr($this->sql, 0, -1).') VALUES ('.substr($values,0,-1).')');
        foreach($contents as $key => $value)
            $result->bindValue($key,$value);
        return $result->execute();
    }

    private function execQuery($sql,$params = []){
        $query = $this->pdo->prepare($sql);
        foreach($params as $key => $value) {
            if(is_int($value))
                $query->bindValue($key, $value,PDO::PARAM_INT);
            elseif(is_string($value))
                $query->bindValue($key, $value,PDO::PARAM_STR);
            else
                $query->bindValue($key, $value);
        }
        $query->execute();
        return $query;
    }
}