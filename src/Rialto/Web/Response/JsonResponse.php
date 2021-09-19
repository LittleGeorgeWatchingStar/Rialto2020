<?php

namespace Rialto\Web\Response;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class JsonResponse
{
    /**
     * @param string[] $messages
     * @return Response
     */
    public static function fromMessages(array $messages)
    {
        $messageData = array_map(function ($msg) {
            return ['message' => $msg];
        }, $messages);
        return new Response(json_encode($messageData));
    }

    public static function fromErrors(array $errors)
    {
        $response = self::fromMessages($errors);
        $response->setStatusCode(Response::HTTP_BAD_REQUEST);
        return $response;
    }

    public static function fromException(\Exception $ex, $statusCode = 500)
    {
        $response = self::fromMessages([$ex->getMessage()]);
        $response->setStatusCode($statusCode);
        return $response;
    }

    public static function fromInvalidForm(FormInterface $form)
    {
        $errors = $form->getErrors($deep = true, $flatten = true);
        return self::fromErrors(self::convertValidationErrors($errors));
    }

    public static function fromValidationErrors(ConstraintViolationListInterface $violations)
    {
        return self::fromErrors(self::convertValidationErrors($violations));
    }

    private static function convertValidationErrors(\Traversable $validationErrors)
    {
        $errors = [];
        foreach ($validationErrors as $violation) {
            /** @var $violation ConstraintViolationInterface */
            $errors[] = $violation->getMessage();
        }
        return $errors;
    }

    /**
     * Most browsers handle redirects before the Javascript code can
     * get at them, so we have to implement our own redirect scheme.
     * @param string $uri
     * @return Response
     */
    public static function javascriptRedirect($uri, $target = null)
    {
        $data = ['_redirect' => $uri];
        if ($target) $data['_target'] = $target;
        return new Response(json_encode($data), Response::HTTP_OK, [
            'Content-Type' => 'application/json',
        ]);
    }
}
