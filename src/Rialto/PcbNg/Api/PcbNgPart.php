<?php

namespace Rialto\PcbNg\Api;


class PcbNgPart
{
    /** @var string */
    private $identifier;

    /**
     * Mil
     * @var int
     */
    private $x;

    /**
     * Mil
     * @var int
     */
    private $y;

    /** @var string */
    private $side;


    /** @var float */
    private $rotation;

    /** @var string|null */
    private $digiKeySku;

    public function __construct(string $identifier,
                                int $x,
                                int $y,
                                string $side,
                                float $rotation,
                                ?string $digiKeySku)
    {
        $this->identifier = $identifier;
        $this->x = $x;
        $this->y = $y;
        $this->side = $side;
        $this->rotation = $rotation;
        $this->digiKeySku = $digiKeySku;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getSide(): string
    {
        return $this->side;
    }

    public function getRotation(): float
    {
        return $this->rotation;
    }

    public function getDigiKeySku(): ?string
    {
        return $this->digiKeySku;
    }
}