<?php

namespace Rialto\Geography\Address\Web;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Geography\Address\Address;
use Rialto\Geography\Address\Orm\AddressRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Converts Address entities to arrays and vice-versa.
 *
 * AddressEntityType requires this because Address entities are immutable.
 */
class AddressToArrayTransformer implements DataTransformerInterface
{
    /** @var AddressRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(Address::class);
    }

    public function transform($address)
    {
        if (null === $address ) {
            return [];
        } else if (! $address instanceof Address ) {
            throw new UnexpectedTypeException($address, "Address");
        }
        return $address->toArray();
    }

    public function reverseTransform($array)
    {
        if ($this->isEmpty($array) ) {
            return null;
        } else if (! is_array($array) ) {
            throw new UnexpectedTypeException($array, 'array');
        }
        $address = Address::fromArray($array);
        return $this->repo->findOrCreate($address);
    }

    private function isEmpty(array $array = null)
    {
        return (null === $array)
            || (count(array_filter($array)) == 0);
    }
}
