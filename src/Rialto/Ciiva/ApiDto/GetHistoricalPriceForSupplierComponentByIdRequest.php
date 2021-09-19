<?php


namespace Rialto\Ciiva\ApiDto;


use Symfony\Component\Serializer\Annotation\Groups;

final class GetHistoricalPriceForSupplierComponentByIdRequest implements RequestDto
{
    /** @var string */
    private $supplierComponentId;

    public function __construct(string $supplierComponentId)
    {
        $this->supplierComponentId = $supplierComponentId;
    }

    public function getEndpoint(): string
    {
        return '/CB2874E3-63A2-4B22-BE10-569A9FBE5003';
    }

    /**
     * @Groups("payload")
     */
    public function getSupplierComponentId(): string
    {
        return $this->supplierComponentId;
    }

    public function responseClass(): string
    {
        return RequestDto::ASSOCIATIVE_ARRAY;
    }
}
