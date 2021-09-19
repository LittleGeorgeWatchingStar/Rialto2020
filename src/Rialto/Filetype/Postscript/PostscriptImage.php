<?php

namespace Rialto\Filetype\Postscript;


/**
 * An image file on disk that is intended to be loaded into a postscript document
 * when it is rendered.
 */
final class PostscriptImage
{
    /**
     * Valid types to load into a postscript document.
     * @see https://www.php.net/manual/en/function.ps-open-image-file.php
     */
    const PNG = 'png';
    const JPEG = 'jpeg';
    const EPS = 'eps';

    /** @var string */
    private $path;

    /** @var string */
    private $type;

    private function __construct(string $path, string $type)
    {
        assertion(in_array($type, [self::PNG, self::JPEG, self::EPS]));
        $this->path = $path;
        $this->type = $type;
    }

    public static function png(string $path): PostscriptImage
    {
        return new self($path, self::PNG);
    }

    public static function jpeg(string $path): PostscriptImage
    {
        return new self($path, self::JPEG);
    }

    public static function eps(string $path): PostscriptImage
    {
        return new self($path, self::EPS);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type;
    }
}
