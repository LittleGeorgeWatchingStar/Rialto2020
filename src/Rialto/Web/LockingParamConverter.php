<?php

namespace Rialto\Web;


use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\OptimisticLockException;
use Rialto\Entity\LockingEntity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * A ParamConverterInterface that uses Doctrine's optimistic locking
 * feature to convert a request parameter to an entity in a concurrecy-safe
 * way.
 *
 * For more info about ParamConverters:
 * @see http://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html
 *
 * For more info about Doctrine's optimistic locking capability:
 * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/transactions-and-concurrency.html#optimistic-locking
 *
 * Rialto entities that want to leverage this feature must implement:
 * @see LockingEntity
 */
class LockingParamConverter implements ParamConverterInterface
{
    /**
     * The request parameter that should be used to pass the edit version no.
     */
    const PARAM_NAME = 'editNo';

    /** @var RegistryInterface */
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Stores the object in the request.
     *
     * @param Request $request The request
     * @param ParamConverter $configuration Contains the name, class and options of the object
     *
     * @throws OptimisticLockException If the requested entity is not at
     *      the requested version.
     *
     * @return bool True if the object has been successfully set, else false
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $name = $configuration->getName();
        $class = $configuration->getClass();
        $em = $this->getManager($class);
        $id = $request->attributes->get($name);
        if (! $id) {
            return false;
        }
        $editNo = $request->get(self::PARAM_NAME);
        $entity = $editNo
            ? $em->find($class, $id, LockMode::OPTIMISTIC, $editNo)
            : $em->find($class, $id); // no locking
        if (!$entity) {
            throw new NotFoundHttpException(sprintf('%s object not found.', $class));
        }
        $request->attributes->set($name, $entity);
        return true;
    }

    /**
     * Checks if the object is supported.
     *
     * @param ParamConverter $configuration Should be an instance of ParamConverter
     *
     * @return bool True if the object is supported, else false
     */
    public function supports(ParamConverter $configuration)
    {
        $class = $configuration->getClass();
        if (!$class) {
            return false;
        }
        $em = $this->getManager($class);
        return $em && $this->isLockingEntity($em->getClassMetadata($class));
    }

    /** @return EntityManager|ObjectManager */
    private function getManager($class)
    {
        return $this->registry->getManagerForClass($class);
    }

    private function isLockingEntity(ClassMetadata $class)
    {
        $ref = $class->getReflectionClass();
        return $ref->implementsInterface(LockingEntity::class);
    }
}
