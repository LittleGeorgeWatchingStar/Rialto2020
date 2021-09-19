<?php

namespace Rialto\Summary\Menu\Main;

use Rialto\Security\Role\Role;
use Rialto\Summary\Menu\SummaryLink;
use Symfony\Component\Routing\RouterInterface;

/**
 * The "Receive PO" link on the side menu.
 */
class ReceivePurchaseOrderSummary extends SummaryLink
{
    public function __construct(RouterInterface $router)
    {
        $uri = $router->generate('purchase_order_list', [
            'printed' => 'yes',
            'completed' => 'no',
        ]);
        $label = 'Receive PO';
        $id = 'ReceivePurchaseOrder';
        parent::__construct($id, $uri, $label);
    }

    public function getAllowedRoles(): array
    {
        return [
            Role::WAREHOUSE,
            Role::RECEIVING,
        ];
    }

}
