<?php

namespace Rialto\Stock\Item\Web;

use OutOfBoundsException;
use Rialto\Security\Role\Role;
use Rialto\Stock\Item\StockCodeGenerator;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates new SKUs for the client.
 */
class SkuController extends RialtoController
{
    /**
     * Generates a list of available SKUs to match the pattern
     * given by the "sku" query parameter.
     *
     * @Route("/stock/new-sku/", name="stock_generate_sku",
     *     options={"expose"=true})
     */
    public function generateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $generator = new StockCodeGenerator($this->dbm);
        $pattern = $request->get('sku');
        if ($generator->isValid($pattern)) {
            try {
                $next = $generator->generateNext($pattern);
                $options = $this->createOptions([$next]);
            } catch (OutOfBoundsException $ex) {
                return JsonResponse::fromException($ex, 400);
            }
        } else {
            $options = [];
        }
        return new Response(json_encode($options));
    }

    private function createOptions(array $suggestions)
    {
        $options = [];
        foreach ($suggestions as $sugg) {
            $options[] = ['sku' => $sugg];
        }
        return $options;
    }
}
