<?php

namespace Rialto\Summary\Menu\Main;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\LazySummaryLink;
use Rialto\Summary\Menu\SummaryLink;
use Rialto\Summary\Menu\SummaryNode;
use Symfony\Component\Routing\RouterInterface;

/**
 * Summary node for the "Admin" side menu group.
 */
class AdminSummary implements SummaryNode
{
    /** @var ObjectManager */
    private $om;

    /** @var RouterInterface */
    private $router;

    public function __construct(
        ObjectManager $om,
        RouterInterface $router)
    {
        $this->om = $om;
        $this->router = $router;
    }

    public function getId(): string
    {
        return 'Admin';
    }

    public function getLabel(): string
    {
        return 'Admin';
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::ADMIN,
        ];
    }

    public function getChildren(): array
    {
        return [
            $this->emailLink(),
            $this->link('Sales gauges', $this->router->generate('Sales_Gauge_show')),
            $this->invoicesLink(),
            $this->link('Payables', $this->router->generate('Creditor_InvoiceHolds')),
            $this->link('Payments', $this->router->generate('Creditor_PaymentRun')),
            $this->link('Checks', $this->router->generate('Accounting_printCheques')),
            $this->link('Sweeps', $this->router->generate('Payment_CardTransaction_sweep', [
                'date' => date('Y-m-d'),
            ])),
            $this->link('Banking', $this->router->generate('Accounting_BankStatement_load')),
            $this->link('Mobile dashboard', $this->router->generate('admin_dashboard')),
        ];
    }

    private function link($label, $uri)
    {
        return new SummaryLink($label, $uri, $label);
    }

    private function emailLink()
    {
        return new LazySummaryLink(
            'supplier-emails',
            $this->router->generate('supplier_email_list'),
            'Read emails',
            'count_supplier_email');
    }

    private function invoicesLink()
    {
        $count = $this->getNumUnapprovedInvoices();
        $label = sprintf('Invoices (%s)', number_format($count));
        $uri = $this->router->generate(
            'Purchasing_SupplierInvoice_approvalList'
        );
        return new SummaryLink("Admin_Invoices", $uri, $label);
    }

    private function getNumUnapprovedInvoices()
    {
        $filters = ['approved' => 'no'];
        $invoices = $this->om->getRepository(SupplierInvoice::class)
            ->findByFilters($filters);
        return count($invoices);
    }
}
