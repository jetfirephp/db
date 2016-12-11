<?php

namespace JetFire\Db\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use JetFire\Db\IteratorResult;
use JetFire\Db\ModelInterface;
use JetFire\Db\TextTransform;

/**
 * Class DoctrineModel
 * @package JetFire\Db\Doctrine
 */
class DoctrineModel extends DoctrineConstructor implements ModelInterface
{

    /**
     * @description called class name
     * @var
     */
    public $class;
    /**
     * @description the table name in the database
     * @var
     */
    private $table;
    /**
     * @description table alias
     * @var
     */
    private $alias;
    /**
     * @description sql query
     * @var
     */
    private $sql;
    /**
     * @description sql parameters
     * @var array
     */
    private $params = [];
    /**
     * @description called class instance
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
        $this->table = isset($this->options['prefix'])?$this->options['prefix'] . TextTransform::pluralize(strtolower($class)):TextTransform::pluralize(strtolower($class));
        $this->alias = strtolower(substr($class, 0, 1));
        return $this;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

//|---------------------------------------------------------------------------------|
//| Getters are managed here                                                        |
//|---------------------------------------------------------------------------------|

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getOrm()
    {
        return $this->em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo()
    {
        return $this->em->getRepository($this->class);
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     */
    public function sql($sql, $params = [])
    {
        $rsm = new ResultSetMapping();
        $query = $this->em->createNativeQuery($sql, $rsm);
        if (!empty($params))
            foreach ($params as $key => $param)
                $query->setParameter($key + 1, $param);

        return $query->getResult();
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|


    /**
     * @return array
     */
    public function all()
    {
        return new IteratorResult($this->repo($this->class)->findAll(),'doctrine');
    }

    /**
     * @param $id
     * @return null|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     */
    public function find($id)
    {
        $this->instance = $this->em->find($this->class, $id);
        return new DoctrineSingleResult($this->instance,function(){return $this->em();});
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
            $this->sql .= ' ' . $this->alias . '.' . $arg . ',';
        $this->sql = substr($this->sql, 0, -1) . ' FROM ' . $this->class . ' ' . $this->alias;
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
        if (!is_null($this->sql) && substr($this->sql, 0, 6) == 'SELECT' && strpos($this->sql, 'WHERE') === false) $this->sql .= ' WHERE';
        if (is_null($this->sql)) $this->sql = ' WHERE';
        if (is_null($value) || $boolean == 'OR') list($key, $operator, $value) = array($key, '=', $operator);
        // if we update or delete the entity
        if (!is_null($this->sql) && strpos($this->sql, 'WHERE') === false) {
            if (is_null($this->sql->getParameter($key)))
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key")->setParameter($key, $value);
            else
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key" . '_' . $value)->setParameter($key . '_' . $value, $value);
            return $this;
        }

        //if we read the entity
        $param = $key;
        if (strpos($this->sql, ':' . $key) !== false) $key = $param . '_' . uniqid();
        $sql_key = ($operator == 'IN' || $operator == 'NOT IN') ? '(:'.$key.')' : ':'.$key;
        $this->sql .= (substr($this->sql, -6) == ' WHERE')
            ? ' ' . $this->alias . '.' . "$param $operator $sql_key"
            : ' ' . $boolean . ' ' . $this->alias . '.' . "$param $operator $sql_key";
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return DoctrineModel
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
        if (!is_null($this->sql) && substr($this->sql, 0, 6) == 'SELECT') $this->sql .= ' WHERE ';
        if (is_null($this->sql)) $this->sql = ' WHERE ';
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
        $this->sql .= ' ORDER BY ' . $this->alias . '.' . $value . ' ' . $order;
        return $this;
    }

    /**
     * @param $limit
     * @param null $first
     * @param bool $single
     * @return array|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function take($limit,$first = null,$single = false)
    {
        $this->sql = (substr($this->sql, 0, 6) != 'SELECT') ? 'SELECT ' . $this->alias . ' FROM ' . $this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $result = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $result->setParameter($key + 1, $param);
                else
                    $result->setParameter($key, $param);
            }
        $result->setMaxResults($limit);
        if(!is_null($first))$result->setFirstResult($first);
        $this->sql = $this->table = null;
        $this->params = [];
        return ($limit == 1 && $single)
            ? new DoctrineSingleResult($result->getSingleResult(),function(){return $this->em();})
            : new IteratorResult($result->getResult(),'doctrine');
    }


    /**
     * @param bool $single
     * @return array|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get($single = false)
    {
        // create a new instance of the table
        if(is_null($this->sql))
            return new DoctrineSingleResult(new $this->class,function(){return $this->em();});
        $this->sql = (substr($this->sql, 0, 6) != 'SELECT') ? 'SELECT ' . $this->alias . ' FROM ' . $this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $query = $this->query($this->sql);
        foreach ($this->params as $key => $param) {
            if (is_numeric($key))
                $query->setParameter($key + 1, $param);
            else
                $query->setParameter($key, $param);
        }
        $this->sql = $this->table = null;
        $this->params = [];
        $result = $query->getResult();
        return ($single && count($result) ==  1)
            ? new DoctrineSingleResult($result[0],function(){return $this->em();})
            : new IteratorResult($result,'doctrine');
    }

    /**
     * @return $this
     */
    public function count()
    {
        $this->sql = 'SELECT COUNT(' . $this->alias . ') FROM ' . $this->class . ' ' . $this->alias . ' ' . $this->sql;
        $query = $this->query($this->sql);
        foreach ($this->params as $key => $param) {
            if (is_numeric($key))
                $query->setParameter($key + 1, $param);
            else
                $query->setParameter($key, $param);
        }
        $this->sql = $this->table = null;
        $this->params = [];
        return $query->getSingleResult()[1];
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param $id
     * @param null $contents
     * @return bool|DoctrineModel
     */
    public function update($id, $contents = null)
    {
        $qb = $this->queryBuilder();
        $this->sql = $qb->update($this->class, $this->alias);
        $this->sql = $this->sql->where($qb->expr()->eq($this->alias . '.id', ':id'))->setParameter('id', $id);
        return (is_null($contents)) ? $this : $this->with($contents);
    }

    /**
     * @param $contents
     * @return bool
     */
    public function with($contents)
    {
        foreach ($contents as $key => $content) {
            if (property_exists($this->class, $key)) {
                if (is_null($this->sql->getParameter($key)))
                    $this->sql = $this->sql->set($this->alias . '.' . $key, ':' . $key)->setParameter($key, $content);
                else
                    $this->sql = $this->sql->set($this->alias . '.' . $key, ':' . $key . '_' . $content)->setParameter($key . '_' . $content, $content);
            }
        }
        $this->sql->getQuery()->execute();
        $this->instance = $this->sql = null;
        $this->params = [];
        return true;
    }

    /**
     * @param $contents
     * @return bool
     */
    public function set($contents)
    {
        $update = 'UPDATE ' . $this->class . ' ' . $this->alias . ' SET';
        foreach ($contents as $key => $content) {
            $param = $key;
            if (strpos($update, ':' . $key) !== false || strpos($this->sql, ':' . $key) !== false) $key = $param . '_' . uniqid();
            $update .= ' ' . $this->alias . '.' . $param . ' = :' . $key . ',';
            $this->params[$key] = $content;
        }
        $this->sql = substr($update, 0, -1) . $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $query->setParameter($key + 1, $param);
                else
                    $query->setParameter($key, $param);
            }
        $this->sql = null;
        $this->params = [];
        return (is_null($query->execute())) ? false : true;
    }


//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $contents
     * @return bool
     */
    public function create($contents = null)
    {
        $this->instance = new $this->class;
        if (is_null($contents)) return $this->instance;
        $replace = ['-', '_', '.'];
        foreach ($contents as $key => $content) {
            $key = str_replace($replace, ' ', $key);
            $key = str_replace(' ', '', ucwords($key));
            $method = 'set' . $key;
            if (method_exists($this->instance, $method))
                $this->instance->$method($content);
        }
        $this->em->persist($this->instance);
        $this->em->flush();
        $this->instance = null;
        return true;
    }

//|---------------------------------------------------------------------------------|
//| Delete methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @return bool
     */
    public function delete()
    {
        $this->sql = 'DELETE FROM ' . $this->class . ' ' . $this->alias . $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $query->setParameter($key + 1, $param);
                else
                    $query->setParameter($key, $param);
            }
        $this->sql = null;
        $this->params = [];
        return (is_null($query->execute())) ? false : true;
    }

    /**
     * @return bool
     */
    public function destroy()
    {
        $ids = func_get_args();
        if(func_num_args() == 1 && is_array($ids[0]))$ids = $ids[0];
        $qb = $this->queryBuilder();
        foreach ($ids as $id)
            $qb->delete($this->class, $this->alias)->where($qb->expr()->eq($this->alias . '.id', ':id'))->setParameter('id', $id)->getQuery()->execute();
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
        if (method_exists($this, $name))
            return call_user_func_array([$this, $name], $args);
        return call_user_func_array([$this->repo(), $name], $args);
    }

//|---------------------------------------------------------------------------------|
//| Custom methods                                                                  |
//|---------------------------------------------------------------------------------|
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function em()
    {
        return $this->em;
    }

    /**
     * @param $id
     * @return bool|\Doctrine\Common\Proxy\Proxy|null|object
     * @throws \Doctrine\ORM\ORMException
     */
    public function reference($id){
        return $this->em->getReference($this->class,$id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function queryBuilder()
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param $query
     * @return Query
     */
    public function query($query)
    {
        return $this->em->createQuery($query);
    }

    /**
     * @param null $entity
     * @return DoctrineModel
     */
    public function instance($entity)
    {
        $this->instance = $entity;
        return $this;
    }

    /**
     * @param null $entity
     * @return bool
     */
    public function watch($entity = null)
    {
        if (!is_null($entity)) $this->instance = $entity;
        $this->em->persist($this->instance);
        $this->instance = null;
        return true;
    }

    /**
     * @param null $entity
     * @return bool
     */
    public function watchAndSave($entity = null)
    {
        if (!is_null($entity)) $this->instance = $entity;
        $this->em->persist($this->instance);
        $this->em->flush();
        $this->instance = null;
        return true;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->instance = null;
        $this->em->flush();
        return true;
    }

    /**
     * @param $content
     * @return bool
     */
    public function remove($content)
    {
        $this->em->remove($content);
        $this->em->flush();
        return true;
    }

    /**
     * @param array $contents
     */
    public function store($contents = []){
        $replace = ['-', '_', '.'];
        foreach ($contents as $key => $content) {
            $key = str_replace($replace, ' ', $key);
            $key = str_replace(' ', '', ucwords($key));
            $method = 'set' . $key;
            if (method_exists($this->instance, $method))
                $this->instance->$method($content);
        }
    }

} 