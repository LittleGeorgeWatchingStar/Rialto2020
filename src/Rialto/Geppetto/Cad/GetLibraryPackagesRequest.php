<?php


namespace Rialto\Geppetto\Cad;


final class GetLibraryPackagesRequest
{
    /** @var string */
    private $libraryName;

    /** @var bool|null */
    private $hasThroughHolePackages;


    public function __construct(string $libraryName, $hasThroughHolePackages = null)
    {
        $this->libraryName = $libraryName;
        $this->hasThroughHolePackages = $hasThroughHolePackages;
    }

    public function getLibraryName(): string
    {
        return $this->libraryName;
    }

    /**
     * @return bool|null
     */
    public function hasThroughHolePackages()
    {
        return $this->hasThroughHolePackages;
    }


}
