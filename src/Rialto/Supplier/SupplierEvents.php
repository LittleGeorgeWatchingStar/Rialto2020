<?php

namespace Rialto\Supplier;


final class SupplierEvents
{
    const SUPPLIER_REFERENCE = 'rialto_supplier.supplier_reference';

    const COMMITMENT_DATE = 'rialto_supplier.commitment_date';

    /**
     * When the manufacturer audits a PO for shortages.
     */
    const AUDIT = 'rialto_supplier.audit';

    const ISSUE_WORK_ORDER = 'rialto_supplier.issue_work_order';

    /**
     * When the manufacturer requests an additional part for a work order.
     */
    const ADDITIONAL_PART = 'rialto_supplier.additional_part';
}
