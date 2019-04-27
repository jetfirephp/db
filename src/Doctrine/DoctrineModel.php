<?php

namespace JetFire\Db\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
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
     * @var string|QueryBuilder
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
    public function setTable($table): self
    {
        $this->class = $table;
        $class = explode('\\', $table);
        $class = end($class);
        $this->table = isset($this->options['prefix']) ? $this->options['prefix'] . TextTransform::pluralize(strtolower($class)) : TextTransform::pluralize(strtolower($class));
        $this->alias = strtolower(substr($class, 0, 1));
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

//|---------------------------------------------------------------------------------|
//| Getters are managed here                                                        |
//|---------------------------------------------------------------------------------|

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getOrm(): \Doctrine\ORM\EntityManager
    {
        return $this->em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function repo(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository($this->class);
    }

    /**
     * @param $sql
     * @param array $params
     * @return array
     */
    public function sql($sql, $params = []): array
    {
        $rsm = new ResultSetMapping();
        $query = $this->em->createNativeQuery($sql, $rsm);
        if (!empty($params)) {
            foreach ($params as $key => $param) {
                $query->setParameter($key + 1, $param);
            }
        }

        return $query->getResult();
    }


//|---------------------------------------------------------------------------------|
//| Reading method are managed here                                                 |
//|---------------------------------------------------------------------------------|


    /**
     * @return IteratorResult
     */
    public function all(): IteratorResult
    {
        return new IteratorResult($this->repo()->findAll(), 'doctrine');
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
        return new DoctrineSingleResult($this->instance, function () {
            return $this->em();
        });
    }


    /**
     * @return $this
     */
    public function select(): self
    {
        $this->sql = 'SELECT';
        $args = func_get_args();
        if (count($args) === 0) {
            $this->sql .= ' *,';
        }
        foreach ($args as $arg) {
            $this->sql .= ' ' . $this->alias . '.' . $arg . ',';
        }
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
    public function where($key, $operator = null, $value = null, $boolean = 'AND'): self
    {
        if ($this->sql !== null && strpos($this->sql, 'SELECT') === 0 && strpos($this->sql, 'WHERE') === false) {
            $this->sql .= ' WHERE';
        }
        if ($this->sql === null) {
            $this->sql = ' WHERE';
        }
        if ($value === null && $operator !== 'IS NULL' && $operator !== 'IS NOT NULL') {
            [$key, $operator, $value] = array($key, '=', $operator);
        }

        // if we update or delete the entity
        if ($this->sql !== null && strpos($this->sql, 'WHERE') === false) {
            if ($this->sql->getParameter($key) === null) {
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key")->setParameter($key, $value);
            } elseif ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
                $this->sql = $this->sql->where($this->alias . ".$key $operator");
            } else {
                $this->sql = $this->sql->where($this->alias . ".$key $operator :$key" . '_' . $value)->setParameter($key . '_' . $value, $value);
            }
            return $this;
        }

        //if we read the entity
        $param = $key;
        if (strpos($this->sql, ':' . $key) !== false) {
            $key = $param . '_' . uniqid('', true);
        }
        $sql_key = ($operator === 'IN' || $operator === 'NOT IN') ? '(:' . $key . ')' : ':' . $key;
        if ($operator === 'IS NULL' || $operator === 'IS NOT NULL') {
            $sql_key = '';
        } else {
            $this->params[$key] = $value;
        }
        $this->sql .= (substr($this->sql, -6) === ' WHERE')
            ? ' ' . $this->alias . '.' . "$param $operator $sql_key"
            : ' ' . $boolean . ' ' . $this->alias . '.' . "$param $operator $sql_key";
        return $this;
    }

    /**
     * @param $key
     * @param null $operator
     * @param null $value
     * @return $this
     */
    public function orWhere($key, $operator = null, $value = null): self
    {
        return $this->where($key, $operator, $value, 'OR');
    }

    /**
     * @param $sql
     * @param null $value
     * @return $this
     */
    public function whereRaw($sql, $value = null): self
    {
        if ($this->sql !== null && strpos($this->sql, 'SELECT') === 0) {
            $this->sql .= ' WHERE ';
        }
        if ($this->sql === null) {
            $this->sql = ' WHERE ';
        }
        $this->sql .= $sql;
        if ($value !== null) {
            $this->params = array_merge($this->params, $value);
        }
        return $this;
    }

    /**
     * @param $value
     * @param string $order
     * @return $this
     */
    public function orderBy($value, $order = 'ASC'): self
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
    public function take($limit, $first = null, $single = false)
    {
        $this->sql = (strpos($this->sql, 'SELECT') !== 0) ? 'SELECT ' . $this->alias . ' FROM ' . $this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $result = $this->query($this->sql);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $param) {
                if (is_numeric($key)) {
                    $result->setParameter($key + 1, $param);
                } else {
                    $result->setParameter($key, $param);
                }
            }
        }
        $result->setMaxResults($limit);
        if ($first !== null) {
            $result->setFirstResult($first);
        }
        $this->sql = $this->table = null;
        $this->params = [];
        return ($limit === 1 && $single)
            ? new DoctrineSingleResult($result->getSingleResult(), function () {
                return $this->em();
            })
            : new IteratorResult($result->getResult(), 'doctrine');
    }


    /**
     * @param bool $single
     * @return array|mixed
     */
    public function get($single = false)
    {
        // create a new instance of the table
        if ($this->sql === null) {
            return new DoctrineSingleResult(new $this->class, function () {
                return $this->em();
            });
        }
        $this->sql = (strpos($this->sql, 'SELECT') !== 0) ? 'SELECT ' . $this->alias . ' FROM ' . $this->class . ' ' . $this->alias . $this->sql : $this->sql;
        $query = $this->query($this->sql);
        foreach ($this->params as $key => $param) {
            if (is_numeric($key)) {
                $query->setParameter($key + 1, $param);
            } else {
                $query->setParameter($key, $param);
            }
        }
        $this->sql = $this->table = null;
        $this->params = [];
        $result = $query->getResult();
        if (count($result) < 1) {
            return null;
        }
        return ($single && count($result) === 1)
            ? new DoctrineSingleResult($result[0], function () {
                return $this->em();
            })
            : new IteratorResult($result, 'doctrine');
    }

    /**
     * @return int
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function count(): int
    {
        $this->sql = 'SELECT COUNT(' . $this->alias . ') FROM ' . $this->class . ' ' . $this->alias . ' ' . $this->sql;
        $query = $this->query($this->sql);
        foreach ($this->params as $key => $param) {
            if (is_numeric($key)) {
                $query->setParameter($key + 1, $param);
            } else {
                $query->setParameter($key, $param);
            }
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
        return $contents === null ? $this : $this->with($contents);
    }

    /**
     * @param $contents
     * @return bool
     */
    public function with($contents): bool
    {
        foreach ($contents as $key => $content) {
            if (property_exists($this->class, $key)) {
                if ($this->sql->getParameter($key) === null) {
                    $this->sql = $this->sql->set($this->alias . '.' . $key, ':' . $key)->setParameter($key, $content);
                } else {
                    $this->sql = $this->sql->set($this->alias . '.' . $key, ':' . $key . '_' . $content)->setParameter($key . '_' . $content, $content);
                }
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
    public function set($contents): bool
    {
        $update = 'UPDATE ' . $this->class . ' ' . $this->alias . ' SET';
        foreach ($contents as $key => $content) {
            $param = $key;
            if (strpos($update, ':' . $key) !== false || strpos($this->sql, ':' . $key) !== false) {
                $key = $param . '_' . uniqid('', true);
            }
            $update .= ' ' . $this->alias . '.' . $param . ' = :' . $key . ',';
            $this->params[$key] = $content;
        }
        $this->sql = substr($update, 0, -1) . $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $param) {
                if (is_numeric($key)) {
                    $query->setParameter($key + 1, $param);
                } else {
                    $query->setParameter($key, $param);
                }
            }
        }
        $this->sql = null;
        $this->params = [];
        return $query->execute() !== null;
    }


//|---------------------------------------------------------------------------------|
//| Create methods are managed here                                                 |
//|---------------------------------------------------------------------------------|

    /**
     * @param null $contents
     * @return bool|object
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create($contents = null)
    {
        $this->instance = new $this->class;
        if ($contents === null) {
            return $this->instance;
        }
        $replace = ['-', '_', '.'];
        foreach ($contents as $key => $content) {
            $key = str_replace($replace, ' ', $key);
            $key = str_replace(' ', '', ucwords($key));
            $method = 'set' . $key;
            if (method_exists($this->instance, $method)) {
                $this->instance->$method($content);
            }
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
    public function delete(): bool
    {
        $this->sql = 'DELETE FROM ' . $this->class . ' ' . $this->alias . $this->sql;
        $query = $this->query($this->sql);
        if (!empty($this->params)) {
            foreach ($this->params as $key => $param) {
                if (is_numeric($key)) {
                    $query->setParameter($key + 1, $param);
                } else {
                    $query->setParameter($key, $param);
                }
            }
        }
        $this->sql = null;
        $this->params = [];
        return $query->execute() !== null;
    }

    /**
     * @return bool
     */
    public function destroy(): bool
    {
        $ids = func_get_args();
        if (is_array($ids[0]) && func_num_args() === 1) {
            $ids = $ids[0];
        }
        $qb = $this->queryBuilder();
        $qb->delete($this->class, $this->alias)->where($qb->expr()->in($this->alias . '.id', ':ids'))->setParameter('ids', $ids)->getQuery()->execute();
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
        return call_user_func_array([$this->repo(), $name], $args);
    }

//|---------------------------------------------------------------------------------|
//| Custom methods                                                                  |
//|---------------------------------------------------------------------------------|
    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function em(): \Doctrine\ORM\EntityManager
    {
        return $this->em;
    }

    /**
     * @param $id
     * @return bool|\Doctrine\Common\Proxy\Proxy|null|object
     * @throws \Doctrine\ORM\ORMException
     */
    public function reference($id)
    {
        return $this->em->getReference($this->class, $id);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function queryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param $query
     * @return Query
     */
    public function query($query): Query
    {
        return $this->em->createQuery($query);
    }

    /**
     * @param null $entity
     * @return DoctrineModel
     */
    public function instance($entity): DoctrineModel
    {
        $this->instance = $entity;
        return $this;
    }

    /**
     * @param null $entity
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function watch($entity = null): bool
    {
        if ($entity !== null) {
            $this->instance = $entity;
        }
        $this->em->persist($this->instance);
        $this->instance = null;
        return true;
    }

    /**
     * @param null $entity
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function watchAndSave($entity = null): bool
    {
        if ($entity !== null) {
            $this->instance = $entity;
        }
        $this->em->persist($this->instance);
        $this->em->flush();
        $this->instance = null;
        return true;
    }

    /**
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function save(): bool
    {
        $this->instance = null;
        $this->em->flush();
        return true;
    }

    /**
     * @param $content
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function remove($content): bool
    {
        $this->em->remove($content);
        $this->em->flush();
        return true;
    }

    /**
     * @param $content
     * @return bool
     * @throws \Doctrine\ORM\ORMException
     */
    public function removeWatch($content): bool
    {
        $this->em->remove($content);
        return true;
    }

    /**
     * @param array $contents
     * @param null $instance
     * @return $this
     */
    public function store($contents = [], $instance = null): self
    {
        $replace = ['-', '_', '.'];
        if ($instance === null) {
            $instance = $this->instance;
        }
        foreach ($contents as $key => $content) {
            $key = str_replace($replace, ' ', $key);
            $key = str_replace(' ', '', ucwords($key));
            $method = 'set' . $key;
            if (method_exists($instance, $method)) {
                $instance->$method($content);
            }
        }
        return $this;
    }
} 