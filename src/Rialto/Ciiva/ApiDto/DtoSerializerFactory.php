<?php


namespace Rialto\Ciiva\ApiDto;


use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * A serializer specifically crafted to handle Ciiva API Data Transfer Objects
 */
final class DtoSerializerFactory
{
    public static function create(): Serializer
    {
        $classMetadataFactory = new ClassMetadataFactory(
            new AnnotationLoader(new AnnotationReader()));

        $extractor = new PropertyInfoExtractor([], [
            new PhpDocExtractor(),
            new ReflectionExtractor(),
        ]);

        $denormalizer = new ObjectNormalizer($classMetadataFactory,
            new CamelCaseToPascalCaseNameConverter(), null, $extractor);
        return new Serializer([$denormalizer, new ArrayDenormalizer()],
            ['json' => new JsonEncoder()]);
    }
}