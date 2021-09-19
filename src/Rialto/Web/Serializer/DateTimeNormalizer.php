<?php

namespace Rialto\Web\Serializer;


use DateTimeInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Allows Rialto to consistently serialize DateTime instances.
 */
class DateTimeNormalizer implements NormalizerInterface
{
    /**
     * Checks whether the given class is supported for normalization by this normalizer.
     *
     * @param mixed $data Data to normalize.
     * @param string $format The format being (de-)serialized from or into.
     *
     * @return bool
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof DateTimeInterface;
    }

    /**
     * Normalizes an object into a set of arrays/scalars.
     *
     * @param DateTimeInterface $object object to normalize
     * @param string $format format the normalization result will be encoded as
     * @param array $context Context options for the normalizer
     *
     * @return string
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->format(DATE_ISO8601);
    }
}
