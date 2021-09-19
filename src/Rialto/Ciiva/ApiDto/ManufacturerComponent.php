<?php


namespace Rialto\Ciiva\ApiDto;


final class ManufacturerComponent
{
    // TODO: Add fields as they are needed.

    /** @var string */
    private $manufacturerPartImageUrl;

    /** @var string */
    private $manufacturerPartUrl;

    public function getManufacturerPartImageUrl(): string
    {
        return $this->manufacturerPartImageUrl;
    }

    public function setManufacturerPartImageUrl(string $manufacturerPartImageUrl)
    {
        $this->manufacturerPartImageUrl = $manufacturerPartImageUrl;
    }

    public function getManufacturerPartUrl(): string
    {
        return $this->manufacturerPartUrl;
    }

    public function setManufacturerPartUrl(string $manufacturerPartUrl)
    {
        $this->manufacturerPartUrl = $manufacturerPartUrl;
    }
}
