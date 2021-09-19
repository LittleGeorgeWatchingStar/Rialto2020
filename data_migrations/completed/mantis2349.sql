select inv.RecurringID, inv.OvAmount, d.Subtotal
from RecurringInvoices inv
join (
    select RecurringID, sum(Amount) as Subtotal
    from RecurringGLInvoices
    group by RecurringID) d
on d.RecurringID = inv.RecurringID;

update RecurringInvoices inv
join (
    select RecurringID, sum(Amount) as Subtotal
    from RecurringGLInvoices
    group by RecurringID) d
on d.RecurringID = inv.RecurringID
set inv.OvAmount = d.Subtotal;