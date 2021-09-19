<?php

class EntityManager_9a5be93 extends \Doctrine\ORM\EntityManager implements \ProxyManager\Proxy\VirtualProxyInterface
{

    /**
     * @var \Closure|null initializer responsible for generating the wrapped object
     */
    private $valueHolder9efb9 = null;

    /**
     * @var \Closure|null initializer responsible for generating the wrapped object
     */
    private $initializerdc4b1 = null;

    /**
     * @var bool[] map of public properties of the parent class
     */
    private static $publicPropertiesa5619 = [
        
    ];

    public function getConnection()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getConnection', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getConnection();
    }

    public function getMetadataFactory()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getMetadataFactory', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getMetadataFactory();
    }

    public function getExpressionBuilder()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getExpressionBuilder', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getExpressionBuilder();
    }

    public function beginTransaction()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'beginTransaction', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->beginTransaction();
    }

    public function getCache()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getCache', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getCache();
    }

    public function transactional($func)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'transactional', array('func' => $func), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->transactional($func);
    }

    public function commit()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'commit', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->commit();
    }

    public function rollback()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'rollback', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->rollback();
    }

    public function getClassMetadata($className)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getClassMetadata', array('className' => $className), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getClassMetadata($className);
    }

    public function createQuery($dql = '')
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'createQuery', array('dql' => $dql), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->createQuery($dql);
    }

    public function createNamedQuery($name)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'createNamedQuery', array('name' => $name), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->createNamedQuery($name);
    }

    public function createNativeQuery($sql, \Doctrine\ORM\Query\ResultSetMapping $rsm)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'createNativeQuery', array('sql' => $sql, 'rsm' => $rsm), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->createNativeQuery($sql, $rsm);
    }

    public function createNamedNativeQuery($name)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'createNamedNativeQuery', array('name' => $name), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->createNamedNativeQuery($name);
    }

    public function createQueryBuilder()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'createQueryBuilder', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->createQueryBuilder();
    }

    public function flush($entity = null)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'flush', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->flush($entity);
    }

    public function find($entityName, $id, $lockMode = null, $lockVersion = null)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'find', array('entityName' => $entityName, 'id' => $id, 'lockMode' => $lockMode, 'lockVersion' => $lockVersion), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->find($entityName, $id, $lockMode, $lockVersion);
    }

    public function getReference($entityName, $id)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getReference', array('entityName' => $entityName, 'id' => $id), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getReference($entityName, $id);
    }

    public function getPartialReference($entityName, $identifier)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getPartialReference', array('entityName' => $entityName, 'identifier' => $identifier), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getPartialReference($entityName, $identifier);
    }

    public function clear($entityName = null)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'clear', array('entityName' => $entityName), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->clear($entityName);
    }

    public function close()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'close', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->close();
    }

    public function persist($entity)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'persist', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->persist($entity);
    }

    public function remove($entity)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'remove', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->remove($entity);
    }

    public function refresh($entity)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'refresh', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->refresh($entity);
    }

    public function detach($entity)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'detach', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->detach($entity);
    }

    public function merge($entity)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'merge', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->merge($entity);
    }

    public function copy($entity, $deep = false)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'copy', array('entity' => $entity, 'deep' => $deep), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->copy($entity, $deep);
    }

    public function lock($entity, $lockMode, $lockVersion = null)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'lock', array('entity' => $entity, 'lockMode' => $lockMode, 'lockVersion' => $lockVersion), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->lock($entity, $lockMode, $lockVersion);
    }

    public function getRepository($entityName)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getRepository', array('entityName' => $entityName), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getRepository($entityName);
    }

    public function contains($entity)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'contains', array('entity' => $entity), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->contains($entity);
    }

    public function getEventManager()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getEventManager', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getEventManager();
    }

    public function getConfiguration()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getConfiguration', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getConfiguration();
    }

    public function isOpen()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'isOpen', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->isOpen();
    }

    public function getUnitOfWork()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getUnitOfWork', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getUnitOfWork();
    }

    public function getHydrator($hydrationMode)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getHydrator', array('hydrationMode' => $hydrationMode), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getHydrator($hydrationMode);
    }

    public function newHydrator($hydrationMode)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'newHydrator', array('hydrationMode' => $hydrationMode), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->newHydrator($hydrationMode);
    }

    public function getProxyFactory()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getProxyFactory', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getProxyFactory();
    }

    public function initializeObject($obj)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'initializeObject', array('obj' => $obj), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->initializeObject($obj);
    }

    public function getFilters()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'getFilters', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->getFilters();
    }

    public function isFiltersStateClean()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'isFiltersStateClean', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->isFiltersStateClean();
    }

    public function hasFilters()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'hasFilters', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->hasFilters();
    }

    /**
     * Constructor for lazy initialization
     *
     * @param \Closure|null $initializer
     */
    public static function staticProxyConstructor($initializer)
    {
        static $reflection;

        $reflection = $reflection ?? $reflection = new \ReflectionClass(__CLASS__);
        $instance = $reflection->newInstanceWithoutConstructor();

        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $instance, 'Doctrine\\ORM\\EntityManager')->__invoke($instance);

        $instance->initializerdc4b1 = $initializer;

        return $instance;
    }

    protected function __construct(\Doctrine\DBAL\Connection $conn, \Doctrine\ORM\Configuration $config, \Doctrine\Common\EventManager $eventManager)
    {
        static $reflection;

        if (! $this->valueHolder9efb9) {
            $reflection = $reflection ?: new \ReflectionClass('Doctrine\\ORM\\EntityManager');
            $this->valueHolder9efb9 = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $this, 'Doctrine\\ORM\\EntityManager')->__invoke($this);

        }

        $this->valueHolder9efb9->__construct($conn, $config, $eventManager);
    }

    public function & __get($name)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, '__get', ['name' => $name], $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        if (isset(self::$publicPropertiesa5619[$name])) {
            return $this->valueHolder9efb9->$name;
        }

        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder9efb9;

            $backtrace = debug_backtrace(false);
            trigger_error(
                sprintf(
                    'Undefined property: %s::$%s in %s on line %s',
                    get_parent_class($this),
                    $name,
                    $backtrace[0]['file'],
                    $backtrace[0]['line']
                ),
                \E_USER_NOTICE
            );
            return $targetObject->$name;
            return;
        }

        $targetObject = $this->valueHolder9efb9;
        $accessor = function & () use ($targetObject, $name) {
            return $targetObject->$name;
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __set($name, $value)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, '__set', array('name' => $name, 'value' => $value), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder9efb9;

            return $targetObject->$name = $value;
            return;
        }

        $targetObject = $this->valueHolder9efb9;
        $accessor = function & () use ($targetObject, $name, $value) {
            return $targetObject->$name = $value;
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = & $accessor();

        return $returnValue;
    }

    public function __isset($name)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, '__isset', array('name' => $name), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder9efb9;

            return isset($targetObject->$name);
            return;
        }

        $targetObject = $this->valueHolder9efb9;
        $accessor = function () use ($targetObject, $name) {
            return isset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();

        return $returnValue;
    }

    public function __unset($name)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, '__unset', array('name' => $name), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        $realInstanceReflection = new \ReflectionClass(get_parent_class($this));

        if (! $realInstanceReflection->hasProperty($name)) {
            $targetObject = $this->valueHolder9efb9;

            unset($targetObject->$name);
            return;
        }

        $targetObject = $this->valueHolder9efb9;
        $accessor = function () use ($targetObject, $name) {
            unset($targetObject->$name);
        };
        $backtrace = debug_backtrace(true);
        $scopeObject = isset($backtrace[1]['object']) ? $backtrace[1]['object'] : new \ProxyManager\Stub\EmptyClassStub();
        $accessor = $accessor->bindTo($scopeObject, get_class($scopeObject));
        $returnValue = $accessor();

        return $returnValue;
    }

    public function __clone()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, '__clone', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        $this->valueHolder9efb9 = clone $this->valueHolder9efb9;
    }

    public function __sleep()
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, '__sleep', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return array('valueHolder9efb9');
    }

    public function __wakeup()
    {
        \Closure::bind(function (\Doctrine\ORM\EntityManager $instance) {
            unset($instance->config, $instance->conn, $instance->metadataFactory, $instance->unitOfWork, $instance->eventManager, $instance->proxyFactory, $instance->repositoryFactory, $instance->expressionBuilder, $instance->closed, $instance->filterCollection, $instance->cache);
        }, $this, 'Doctrine\\ORM\\EntityManager')->__invoke($this);
    }

    public function setProxyInitializer(\Closure $initializer = null)
    {
        $this->initializerdc4b1 = $initializer;
    }

    public function getProxyInitializer()
    {
        return $this->initializerdc4b1;
    }

    public function initializeProxy() : bool
    {
        return $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'initializeProxy', array(), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;
    }

    public function isProxyInitialized() : bool
    {
        return null !== $this->valueHolder9efb9;
    }

    public function getWrappedValueHolderValue() : ?object
    {
        return $this->valueHolder9efb9;
    }


}
