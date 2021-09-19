<?php


namespace Rialto\Ciiva\ApiDto;


use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

final class CamelCaseToPascalCaseNameConverter implements NameConverterInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($propertyName)
    {
        return ucfirst($propertyName);
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($propertyName)
    {
        return lcfirst($propertyName);
    }
}
