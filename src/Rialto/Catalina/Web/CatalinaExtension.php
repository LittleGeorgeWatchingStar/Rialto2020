<?php

namespace Rialto\Catalina\Web;


use Twig\Extension\AbstractExtension;

class CatalinaExtension extends AbstractExtension
{
    /** @var string */
    private $catalinaUrl;

    public function __construct($catalinaUrl)
    {
        $this->catalinaUrl = rtrim($catalinaUrl, '/');
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_Function('catalina_job_results', [$this, 'jobResults']),
        ];
    }

    public function jobResults($jobId)
    {
        assertion(is_numeric($jobId));
        return "{$this->catalinaUrl}/testing/$jobId/";
    }
}
