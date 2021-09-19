<?php

class SymfonySerializerAdapter_0141a64 extends \FOS\RestBundle\Serializer\SymfonySerializerAdapter implements \ProxyManager\Proxy\VirtualProxyInterface
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

    public function serialize($data, $format, \FOS\RestBundle\Context\Context $context)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'serialize', array('data' => $data, 'format' => $format, 'context' => $context), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->serialize($data, $format, $context);
    }

    public function deserialize($data, $type, $format, \FOS\RestBundle\Context\Context $context)
    {
        $this->initializerdc4b1 && ($this->initializerdc4b1->__invoke($valueHolder9efb9, $this, 'deserialize', array('data' => $data, 'type' => $type, 'format' => $format, 'context' => $context), $this->initializerdc4b1) || 1) && $this->valueHolder9efb9 = $valueHolder9efb9;

        return $this->valueHolder9efb9->deserialize($data, $type, $format, $context);
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

        \Closure::bind(function (\FOS\RestBundle\Serializer\SymfonySerializerAdapter $instance) {
            unset($instance->serializer);
        }, $instance, 'FOS\\RestBundle\\Serializer\\SymfonySerializerAdapter')->__invoke($instance);

        $instance->initializerdc4b1 = $initializer;

        return $instance;
    }

    public function __construct(\Symfony\Component\Serializer\SerializerInterface $serializer)
    {
        static $reflection;

        if (! $this->valueHolder9efb9) {
            $reflection = $reflection ?: new \ReflectionClass('FOS\\RestBundle\\Serializer\\SymfonySerializerAdapter');
            $this->valueHolder9efb9 = $reflection->newInstanceWithoutConstructor();
        \Closure::bind(function (\FOS\RestBundle\Serializer\SymfonySerializerAdapter $instance) {
            unset($instance->serializer);
        }, $this, 'FOS\\RestBundle\\Serializer\\SymfonySerializerAdapter')->__invoke($this);

        }

        $this->valueHolder9efb9->__construct($serializer);
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
        \Closure::bind(function (\FOS\RestBundle\Serializer\SymfonySerializerAdapter $instance) {
            unset($instance->serializer);
        }, $this, 'FOS\\RestBundle\\Serializer\\SymfonySerializerAdapter')->__invoke($this);
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
