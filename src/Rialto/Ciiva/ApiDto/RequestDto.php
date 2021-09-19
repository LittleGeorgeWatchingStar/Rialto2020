<?php


namespace Rialto\Ciiva\ApiDto;


/**
 * A Data Transfer Object that corresponds to a specific operation on Ciiva's
 * public Api.
 *
 * @see https://api.ciiva.com/api/metadata
 *
 * Fields to include in the serialized request should be annotated with the
 * "payload" serializer group.
 */
interface RequestDto
{
    const ASSOCIATIVE_ARRAY = '';

    /**
     * The public API endpoint for the operation.
     */
    public function getEndpoint(): string;

    /**
     * The class representing the expected Response DTO.
     *
     * Set to an empty string to deserialize the response to an associative array.
     */
    public function responseClass(): string;
}
