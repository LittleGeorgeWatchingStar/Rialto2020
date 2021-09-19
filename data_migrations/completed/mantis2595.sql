-- Create one-to-many relationship from invoice item to GRN item.
alter table GRNs add column invoiceItemID bigint unsigned null default null;
alter table GRNs
add constraint GRNs_fk_invoiceItemID
foreign key (invoiceItemID) references SuppInvoiceDetails (SIDetailID)
on delete restrict;

-- Populate the new relationship from the old, "backward" relationship.
update GRNs grn
join SuppInvoiceDetails inv
    on inv.GRNNo = grn.GRNNo
set grn.invoiceItemID = inv.SIDetailID,
grn.QuantityInv = least(inv.Invoicing, grn.QtyRecd);

-- Find all invoice items that are not fully allocated to GRNs.
create temporary table Underallocated
select inv.SIDetailID as ID,
inv.PONumber,
inv.GRNNo,
grn.PODetailItem,
inv.Invoicing - sum(grn.QuantityInv) as QtyUnallocated
from SuppInvoiceDetails inv
join GRNs grn
    on grn.invoiceItemID = inv.SIDetailID
group by inv.SIDetailID
having QtyUnallocated > 0;

-- Update any unallocated GRN items that match the under-allocated invoice items.
update GRNs grn
join Underallocated u
on grn.PODetailItem = u.PODetailItem
set grn.invoiceItemID = u.ID,
grn.QuantityInv = grn.QuantityInv + u.QtyUnallocated
where grn.invoiceItemID is null
and grn.QtyRecd - grn.QuantityInv = u.QtyUnallocated