<?php

namespace Rialto\Web\Form;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Entity\RialtoEntity;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms instances of RialtoEntity into their IDs and vice-versa.
 *
 * Useful for form types that take IDs and return entities.
 * @see TextEntityType
 */
class RialtoEntityToIdTransformer implements DataTransformerInterface
{
    /** @var ObjectManager */
    private $om;

    /** @var string */
    private $class;

    public function __construct(ObjectManager $om, string $class)
    {
        $this->om = $om;
        $this->class = $class;
    }

    /**
     * @param RialtoEntity $entity
     * @return int|string|array|null The ID of the entity.
     * @throws UnexpectedTypeException
     */
    public function transform($entity)
    {
        if (! $entity ) return null;
        if (! $entity instanceof RialtoEntity ) {
            throw new UnexpectedTypeException($entity, RialtoEntity::class);
        }
        $metadata = $this->om->getClassMetadata(get_class($entity));
        $id = $metadata->getIdentifierValues($entity);
        $id = $this->toScalarArray($id);
        if (count($id) == 1) {
            $id = reset($id);
        }
        assertion( (bool) $id, "no id");
        return is_numeric($id) ? (int) $id : $id;
    }

    private function toScalarArray(array $id)
    {
        $result = [];
        foreach ($id as $key => $value) {
            $result[$key] = $this->toScalar($value);
        }
        return $result;
    }

    private function toScalar($value)
    {
        if ( is_scalar($value) ) {
            return $value;
        } elseif ( is_array($value) ) {
            return $this->toScalarArray($value);
        } elseif ( $value instanceof RialtoEntity ) {
            return $this->transform($value);
        }
        throw new UnexpectedTypeException($value, "RialtoEntity or scalar or array");
    }

    /**
     * @param string|array $id
     * @return RialtoEntity|null The entity whose ID is given.
     */
    public function reverseTransform($id)
    {
        if (! $id ) return null;
        $valid = is_scalar($id) || is_array($id);
        if (! $valid ) {
            throw new UnexpectedTypeException($id, "entity ID");
        }
        $entity = $this->om->find($this->class, $id);
        if (! $entity ) {
            throw new TransformationFailedException(
                "Entity {$this->class} $id not found.");
        }
        return $entity;
    }
}
