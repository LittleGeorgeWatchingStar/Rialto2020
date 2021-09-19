<?php

namespace Rialto\Printing\Printer\Web;

use Rialto\Filetype\Postscript\InstructionLabel;
use Rialto\Printing\Printer\LabelPrinter;
use Rialto\Printing\Printer\PrintServer;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

class LabelPrinterController extends RialtoController
{
    /** @var PrintServer */
    private $printServer;

    protected function init(ContainerInterface $container)
    {
        $this->printServer = $this->get(PrintServer::class);
    }

    /**
     * @Route("/Util/LabelPrinter/printInstructions",
     *   name="Util_LabelPrinter_printInstructions")
     * @Method("POST")
     */
    public function printInstructionsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $instructions = $request->get('instructions');
        if (empty($instructions)) {
            return JsonResponse::fromErrors([
                'No instructions selected to print.'
            ]);
        }
        /** @var $printer LabelPrinter */
        $printer = $this->printServer->getPrinter('instructions');
        $label = new InstructionLabel($instructions);
        $printer->printLabel($label);
        return JsonResponse::fromMessages([
            'Instructions printed successfully.'
        ]);
    }
}
