<?php

namespace Rialto\Accounting\Ledger\Entry\Web;


use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class GLEntryController extends RialtoController
{
    /**
     * @Route("/accounting/gl-entry/", name="gl_entry_list")
     * @Method("GET")
     * @Template("accounting/entry/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $repo = $this->getRepository(GLEntry::class);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $list = new EntityList($repo, $form->getData());
        if ($request->get('csv')) {
            $csv = GLEntryCsv::create($list);
            return FileResponse::fromData($csv->toString(), 'gl entries.csv', 'text/csv');
        }
        return [
            'list' => $list,
            'form' => $form->createView(),
            'reportsLinks' => $this->getReportLinks(),
        ];
    }

    private function getReportLinks()
    {
        $reportLinks = ["Balance sheet" => $this->generateUrl('Accounting_BalanceSheet'),
                        "Profit and loss statement" => $this->generateUrl('Accounting_ProfitAndLoss')];
        return $reportLinks;
    }
}
