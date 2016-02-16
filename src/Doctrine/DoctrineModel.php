<?php

namespace JetFire\Db\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use JetFire\Db\ModelInterface;

/**
 * Class DoctrineModel
 * @package JetFire\Db\Doctrine
 */
class DoctrineModel extends DoctrineConstructor implements ModelInterface{

    /**
     * @var
     */
    protected $sql;
    /**
     * @var array
     */
    protected $params = [];

    /**
     * @var
     */
    public $entity;
    /**
     * @var
     */
    public $table;
    /**
     * @var
     */
    public $alias;

    /**
     * @var string
     */
    public $class = '';

    /**
     * @param $class
     * @return $this
     */
    public function setTable($class){
        if(empty($this->class))
            $this->class = $class;
        return $this;
    }

    /**
     * @param bool $without_namespace
     */
    private function get_class_name($without_namespace = true)
    {
        if (empty($this->table)) {
            $class = $this->class;
            if ($without_namespace) {
                $class = explode('\\',$class);
                end($class);
                $last = key($class);
                $class = $class[$last];
            }
            $this->table = $class;
            $this->alias = strtolower(substr($class, 0, 1));
        }
    }

//|---------------------------------------------------------------------------------|
//| Getters are managed here                                                        |
//|---------------------------------------------------------------------------------|

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function orm(){
        return $this->em;
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     */
    public function sql($sql, $params = [])
    {
        $rsm = new ResultSetMapping();
        $query =  $this->em->createNativeQuery($sql, $rsm);
        if (!empty($params))
            foreach ($params as $key => $param)
                $query->setParameter($key + 1, $param);

        return $query->getResult();
    }

    /**
     * @param $query
     * @return Query
     */
    public function query($query)
    {
        return $this->em->createQuery($query);
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|


    /**
     * @return array
     */
    public function all()
    {
        return $this->repo($this->class)->findAll();
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
        $this->entity = $this->em->find($this->class, $id);
        return $this->entity;
    }


    /**
     * @return $this
     */
    public function select()
    {
        $this->get_class_name();
        $this->sql = 'SELECT';
        $args = func_get_args();
        if(count($args) == 0)$this->sql .= ' *,';
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
        $this->get_class_name();
        if (!empty($this->sql) && substr($this->sql, 0, 6) == 'SELECT' && strpos($this->sql, 'WHERE') === false) $this->sql .= ' WHERE';
        if (empty($this->sql)) $this->sql = ' WHERE';
        if (is_null($value)|| $boolean == 'OR') list($key, $operator, $value) = array($key, '=', $operator);
        // if we update or delete the entity
        if (!empty($this->sql) && strpos($this->sql, 'WHERE') === false) {
            if (is_null($this->sql->getParameter($key)))
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key")->setParameter($key, $value);
            else
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key" . '_' . $value)->setParameter($key . '_' . $value, $value);
            return $this;
        }

        //if we read the entity
        $param = $key;
        if (strpos($this->sql, ':' . $key) !== false) $key = $param . '_' . uniqid();
        $this->sql .= (substr($this->sql, -6) == ' WHERE')
            ? ' ' . $this->alias . '.' . "$param $operator :$key"
            : ' ' . $boolean . ' ' . $this->alias . '.' . "$param $operator :$key";
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
        if (!empty($this->sql) && substr($this->sql, 0, 6) == 'SELECT') $this->sql .= ' WHERE ';
        if (empty($this->sql)) $this->sql = ' WHERE ';
        $this->sql .= $sql;
        if(!is_null($value))$this->params = array_merge($this->params,$value);
        return $this;
    }

    /**
     * @param $value
     * @param string $order
     * @return $this
     */
    public function orderBy($value, $order = 'ASC'){
        $this->sql .= ' ORDER BY '.$this->alias.'.'.$value.' '.$order;
        return $this;
    }

    /**
     * @param $value
     * @param bool $single
     * @return array|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function take($value,$single = false){
        $this->get_class_name();
        $this->sql = (substr($this->sql, 0, 6) != 'SELECT') ? 'SELECT ' . $this->alias . ' FROM ' . $this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $query->setParameter($key + 1, $param);
                else
                    $query->setParameter($key, $param);
            }
        $query->setMaxResults($value);
        $this->sql = '';
        $this->params = [];
        $this->table = '';
        return ($value == 1 && $single)?$query->getSingleResult():$query->getResult();
    }


    /**
     * @param bool $single
     * @return array|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function get($single = false)
    {
        $this->get_class_name();
        $this->sql = (substr($this->sql, 0, 6) != 'SELECT') ? 'SELECT ' . $this->alias . ' FROM ' . $this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $query->setParameter($key + 1, $param);
                else
                    $query->setParameter($key, $param);
            }
        $this->sql = '';
        $this->params = [];
        $this->table = '';
        return ($single && count($query->getResult()) == 1) ? $query->getSingleResult() : $query->getResult();
    }

    /**
     * @return $this
     */
    public function count(){
        $this->get_class_name();
        $last = (isset($this->sql))?$this->sql:'';
        $this->sql = 'SELECT COUNT('.$this->alias.') FROM ' . $this->class . ' ' . $this->alias.' '.$last;
        return $this;
    }

//|---------------------------------------------------------------------------------|
//| Update methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $id
     * @param null $contents
     * @return bool|DoctrineModel
     */
    public function update($id = null, $contents = null)
    {
        $this->get_class_name();
        $qb = $this->queryBuilder();
        $this->sql = $qb->update($this->class, $this->alias);
        if (!is_null($id))
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
        $this->entity = null;
        $this->sql = '';
        $this->params = [];
        return true;
    }

    /**
     * @param $contents
     * @return bool
     */
    public function set($contents)
    {
        $this->get_class_name();
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
        $this->sql = '';
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
        $this->entity = new $this->class;
        if(is_null($contents)) return $this->entity;
        $replace = ['-', '_', '.'];
        foreach ($contents as $key => $content) {
            $key = str_replace($replace, ' ', $key);
            $key = str_replace(' ', '', ucwords($key));
            $method = 'set' . $key;
            if (method_exists($this->entity, $method))
                $this->entity->$method($content);
        }
         $this->em->persist($this->entity);
         $this->em->flush();
        $this->entity = null;
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
        $this->get_class_name();
        $this->sql = 'DELETE ' . $this->class . ' ' . $this->alias . $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $query->setParameter($key + 1, $param);
                else
                    $query->setParameter($key, $param);
            }
        $this->sql = '';
        $this->params = [];
        return (is_null($query->execute())) ? false : true;
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
     * @return bool
     */
    public function destroy()
    {
        $ids = func_get_args();
        $this->get_class_name();
        $ids = array_pop($ids);
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
        if(method_exists($this,$name))
            return call_user_func_array([$this,$name],$args);
        return call_user_func_array([$this->repo(),$name],$args);
    }

//|---------------------------------------------------------------------------------|
//| Custom methods                                                                  |
//|---------------------------------------------------------------------------------|


    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo()
    {
        return $this->em->getRepository($this->class);
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function em()
    {
        return $this->em;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function queryBuilder()
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param bool $single
     * @return array|mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getArray($single = false)
    {
        $this->get_class_name();
        $this->sql = (substr($this->sql, 0, 6) != 'SELECT') ? 'SELECT ' . $this->alias . ' FROM ' .$this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params))
            foreach ($this->params as $key => $param) {
                if (is_numeric($key))
                    $query->setParameter($key + 1, $param);
                else
                    $query->setParameter($key, $param);
            }
        $this->sql = '';
        $this->params = [];
        $this->table = '';
        return ($single && count($query->getResult()) == 1) ? $query->getSingleResult(Query::HYDRATE_ARRAY) : $query->getArrayResult();
    }

    /**
     * @param null $entity
     * @return bool
     */
    public function watch($entity = null)
    {
        if (!is_null($entity)) $this->entity = $entity;
        $this->em->persist($this->entity);
        return true;
    }

    /**
     * @param null $entity
     * @return bool
     */
    public function watchAndSave($entity = null)
    {
        if (!is_null($entity)) $this->entity = $entity;
        $this->em->persist($this->entity);
        $this->em->flush();
        return true;
    }

    /**
     * @return bool
     */
    public function save()
    {
        $this->em->flush();
        return true;
    }


} 