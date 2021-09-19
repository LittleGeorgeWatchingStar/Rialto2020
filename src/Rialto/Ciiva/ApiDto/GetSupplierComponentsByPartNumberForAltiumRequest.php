<?php


namespace Rialto\Ciiva\ApiDto;


use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Get supplier components by part number for Altium with combined data.
 * @see https://api.ciiva.com/api/json/metadata?op=GetSupplierComponentsByPartNumberForAltiumRequest
 */
final class GetSupplierComponentsByPartNumberForAltiumRequest implements RequestDto
{
    /** @var string */
    private $supplierName;

    /** @var string */
    private $supplierPartNumber;

    /** @var bool */
    private $exactMatch;

    /** @var string[] */
    private $include;

    /** @var int */
    private $limit;

    public function __construct(string $supplierName,
                                string $supplierPartNumber,
                                bool $exactMatch = false,
                                array $include = [],
                                int $limit = 5)
    {
        $this->supplierName = $supplierName;
        $this->supplierPartNumber = $supplierPartNumber;
        $this->exactMatch = $exactMatch;
        $this->include = $include;
        $this->limit = $limit;
    }

    public function getEndpoint(): string
    {
        return '/5A2FA350-F291-43D4-98A9-C8E1D47CD0D1';
    }

    public function responseClass(): string
    {
        return GetSupplierComponentsByPartNumberForAltiumResponse::class;
    }

    /**
     * @Groups("payload")
     */
    public function getSupplierName(): string
    {
        return $this->supplierName;
    }

    /**
     * @Groups("payload")
     */
    public function getSupplierPartNumber(): string
    {
        return $this->supplierPartNumber;
    }

    /**
     * @Groups("payload")
     */
    public function isExactMatch(): bool
    {
        return $this->exactMatch;
    }

    /**
     * @Groups("payload")
     */
    public function getInclude(): array
    {
        return $this->include;
    }

    /**
     * @Groups("payload")
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
