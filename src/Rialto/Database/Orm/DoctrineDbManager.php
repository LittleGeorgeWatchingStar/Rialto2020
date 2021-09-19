<?php

namespace Rialto\Database\Orm;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

/**
 * A DbManager implementation using Doctrine 2.x.
 */
class DoctrineDbManager implements DbManager
{
    /** @var EntityManager */
    private $em;
    private $path;

    public function __construct(EntityManager $em, $path)
    {
        $this->em = $em;
        $this->path = $path;
    }

    public function find($className, $id)
    {
        $class = $this->fullClass($className);
        $id = $this->normalizeId($class, $id);
        return $this->em->find($class, $id);
    }

    protected function fullClass($className)
    {
        return $this->path . '\\' . $className;
    }

    private function normalizeId($class, $id)
    {
        if (is_array($id)) {
            /* Prevent the use of old-style id arrays. */
            foreach ($id as $field => $value) {
                assert(!is_numeric($field));
            }
        }
        return $id;
    }

    /**
     * @throws DbKeyException
     *  If a Model object with the given id is not found.
     */
    public function need($className, $id)
    {
        $model = $this->find($className, $id);
        if ($model) return $model;
        throw new DbKeyException($className, $id);
    }

    /**
     * @deprecated Use persist() and flush() instead
     */
    public function save($className, $model)
    {
        $this->em->persist($model);
        $this->em->flush();
    }

    /**
     * @deprecated Use remove() and flush() instead
     */
    public function delete($className, $model)
    {
        $this->em->remove($model);
        $this->em->flush();
    }

    public function persist($entity)
    {
        if ($entity instanceof Persistable) {
            $this->persistEntities($entity);
        } else {
            $this->em->persist($entity);
        }
    }

    private function persistEntities(Persistable $persistable)
    {
        foreach ($persistable->getEntities() as $entity) {
            $this->em->persist($entity);
        }
    }

    public function remove($model)
    {
        $this->em->remove($model);
    }

    public function flush()
    {
        $this->em->flush();
    }

    public function getRepository($className)
    {
        $class = $this->fullClass($className);
        return $this->em->getRepository($class);
    }

    /** @return QueryBuilder */
    public function createQueryBuilder()
    {
        return $this->em->createQueryBuilder();
    }

    public function beginTransaction()
    {
        $this->em->getConnection()->beginTransaction();
    }

    public function flushAndCommit()
    {
        $this->em->flush();
        $this->em->getConnection()->commit();
    }

    public function rollBack()
    {
        $this->em->getConnection()->rollback();
    }

    public function getProfiler()
    {
        return $this->em->getConfiguration()->getSQLLogger();
    }

    /** @return EntityManager */
    public function getEntityManager()
    {
        return $this->em;
    }

    public function getConnection()
    {
        return $this->em->getConnection();
    }

    public function merge($object)
    {
        return $this->em->merge($object);
    }

    public function clear($objectName = null)
    {
        $this->em->clear($objectName);
    }

    public function detach($object)
    {
        $this->em->detach($object);
    }

    public function refresh($object)
    {
        $this->em->refresh($object);
    }

    public function getClassMetadata($className)
    {
        return $this->em->getClassMetadata($className);
    }

    public function getMetadataFactory()
    {
        return $this->em->getMetadataFactory();
    }

    public function initializeObject($obj)
    {
        $this->em->initializeObject($obj);
    }

    public function contains($object)
    {
        return $this->em->contains($object);
    }
}
