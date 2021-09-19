<?php

namespace Rialto\Web\Serializer;

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Replace the FOS Rest Bundle view handler with one that detects whether
 * a view has a template and response appropriately (no template, no HTML
 * support).
 */
class ViewHandler implements ViewHandlerInterface
{
    /** @var ViewHandlerInterface */
    private $handler;

    /** @var RequestStack */
    private $requestStack;

    private $fallbackFormat = 'json';

    public function __construct(ViewHandlerInterface $handler,
                                RequestStack $requestStack)
    {
        $this->handler = $handler;
        $this->requestStack = $requestStack;
    }

    public function renderTemplate(View $view, $format)
    {
        if ($view->getTemplate()) {
            return $this->handler->renderTemplate($view, $format);
        }
        throw new UnsupportedMediaTypeHttpException("Format $format is not supported");
    }

    public function supports($format)
    {
        return $this->handler->supports($format);
    }

    public function registerHandler($format, $callable)
    {
        $this->handler->registerHandler($format, $callable);
    }

    public function isFormatTemplating($format)
    {
        return $this->handler->isFormatTemplating($format);
    }

    public function handle(View $view, Request $request = null)
    {
        $request = $this->ensureRequest($request);
        $format = $this->getFormat($view, $request);
        if ($this->canHandle($view, $format)) {
            return $this->handler->handle($view, $request);
        }
        if ($format === $this->fallbackFormat) {
            $msg = "Format '$format' not supported";
            throw new NotAcceptableHttpException($msg);
        } else {
            $view->setFormat($this->fallbackFormat);
            $response = $this->handler->handle($view, $request);
            $response->headers->set('Content-Type', $request->getMimeType($this->fallbackFormat));
            return $response;
        }
    }

    private function ensureRequest(Request $request = null): Request
    {
        return $request ?: $this->requestStack->getCurrentRequest();
    }

    private function getFormat(View $view, Request $request): string
    {
        return $view->getFormat() ?: $request->getRequestFormat();
    }

    private function canHandle(View $view, string $format): bool
    {
        return $this->supports($format)
            && $this->templateExistsIfNeeded($view, $format);
    }

    private function templateExistsIfNeeded(View $view, string $format): bool
    {
        if ($this->isFormatTemplating($format)) {
            return !!$view->getTemplate();
        }
        return true;
    }

    public function createRedirectResponse(View $view, $location, $format)
    {
        return $this->handler->createRedirectResponse($view, $location, $format);
    }

    public function prepareTemplateParameters(View $view)
    {
        return $this->handler->prepareTemplateParameters($view);
    }

    public function createResponse(View $view, Request $request, $format)
    {
        return $this->handler->createResponse($view, $request, $format);
    }
}
