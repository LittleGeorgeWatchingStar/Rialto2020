<?php

namespace Rialto\PcbNg\Api;


use Psr\Http\Message\ResponseInterface;

class PcbPriceQuote
{
    const SERVICE_ECONO = 'eco';
    const SERVICE_STANDARD = 'std';
    const SERVICE_EXPRESS = 'exp';

    const ASSEMBLY_SINGLE_SIDED = 'ss';
    const ASSEMBLY_DOUBLE_SIDED = 'ds';

    /** @var array */
    private $batchesSvcQtySidesUnitPrices;

    /** @var array */
    private $batchesSidesUnitEconoPrices;

    /** @var array */
    private $batchesSvcQtySidesSqinchPrices;

    /** @var array */
    private $batchesSidesUnitPrices;

    /** @var array */
    private $svcMinimumPcbaPrices;

    /** @var array */
    private $batchesFabricationUnitPrices;

    /** @var array */
    private $batchesFabricationLayersSqinchPrices;

    /** @var array */
    private $batchesFabricationSqinchPrices;

    /** @var array */
    private $batchesSidesSqinchEconoPrices;

    /** @var array */
    private $batchesSidesSqinchPrices;

    /** @var array */
    private $batchesFabricationLayersUnitPrices;

    /** @var float */
    private $boardHeightMm;

    /** @var float */
    private $boardWidthMm;

    /** @var string */
    private $id;

    /** @var int */
    private $time;

    /** @var int */
    private $expiresAt;

    public static function fromResponse(ResponseInterface $response): self
    {
        $body = json_decode($response->getBody(), true);

        return new self($body['batches_svc_qty_sides_unit_prices'],
                        $body['batches_sides_unit_econo_prices'],
                        $body['batches_svc_qty_sides_sqinch_prices'],
                        $body['batches_sides_unit_prices'],
                        $body['svc_minimum_pcba_prices'],
                        $body['batches_fabrication_unit_prices'],
                        $body['batches_fabrication_layers_sqinch_prices'],
                        $body['batches_fabrication_sqinch_prices'],
                        $body['batches_sides_sqinch_econo_prices'],
                        $body['batches_sides_sqinch_prices'],
                        $body['batches_fabrication_layers_unit_prices'],
                        $body['board_height_mm'],
                        $body['board_width_mm'],
                        $body['id'],
                        $body['time'],
                        $body['expires_at']);
    }

    public function __construct(array $batchesSvcQtySidesUnitPrices,
                                array $batchesSidesUnitEconoPrices,
                                array $batchesSvcQtySidesSqinchPrices,
                                array $batchesSidesUnitPrices,
                                array $svcMinimumPcbaPrices,
                                array $batchesFabricationUnitPrices,
                                array $batchesFabricationLayersSqinchPrices,
                                array $batchesFabricationSqinchPrices,
                                array $batchesSidesSqinchEconoPrices,
                                array $batchesSidesSqinchPrices,
                                array $batchesFabricationLayersUnitPrices,
                                float $boardHeightMm,
                                float $boardWidthMm,
                                string $id,
                                int $time,
                                int $expiresAt)
    {
        $this->batchesSvcQtySidesUnitPrices = $batchesSvcQtySidesUnitPrices;
        $this->batchesSidesUnitEconoPrices = $batchesSidesUnitEconoPrices;
        $this->batchesSvcQtySidesSqinchPrices = $batchesSvcQtySidesSqinchPrices;
        $this->batchesSidesUnitPrices = $batchesSidesUnitPrices;
        $this->svcMinimumPcbaPrices = $svcMinimumPcbaPrices;
        $this->batchesFabricationUnitPrices = $batchesFabricationUnitPrices;
        $this->batchesFabricationLayersSqinchPrices = $batchesFabricationLayersSqinchPrices;
        $this->batchesFabricationSqinchPrices = $batchesFabricationSqinchPrices;
        $this->batchesSidesSqinchEconoPrices = $batchesSidesSqinchEconoPrices;
        $this->batchesSidesSqinchEconoPrices = $batchesSidesSqinchPrices;
        $this->batchesSidesSqinchPrices = $batchesSidesSqinchPrices;
        $this->batchesFabricationLayersUnitPrices = $batchesFabricationLayersUnitPrices;
        $this->boardHeightMm = $boardHeightMm;
        $this->boardWidthMm = $boardWidthMm;
        $this->id = $id;
        $this->time = $time;
        $this->expiresAt = $expiresAt;
    }

    public function getBatchesSvcQtySidesUnitPrices(): array
    {
        return $this->batchesSvcQtySidesUnitPrices;
    }

    public function getSvcMinimumPcbaPrices(): array
    {
        return $this->svcMinimumPcbaPrices;
    }

    public function getBoardHeightMm(): float
    {
        return $this->boardHeightMm;
    }

    public function getBoardWidthMm(): float
    {
        return $this->boardWidthMm;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTime(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->time);
        return $dateTime;
    }

    public function getExipresAt(): \DateTime
    {
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->expiresAt);
        return $dateTime;
    }
}




