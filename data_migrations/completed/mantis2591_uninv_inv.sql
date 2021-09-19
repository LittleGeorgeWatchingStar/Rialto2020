-- PO 6139 has the wrong qty (250 instead of 760), with the diff written
-- off as purch variance. Diff: -4080.00
begin;
delete from GLTrans where Type = 20 and TypeNo = 28191;
delete from SuppTrans where Type = 20 and TransNo = 28191;
update PurchOrderDetails set QtyInvoiced = 250 where PODetailItem = 13857;
update PurchOrderDetails set QtyInvoiced = 0 where PODetailItem = 13858;
update SupplierInvoice set approved = 0 where id = 3187;
update GRNs set QuantityInv = 0 where GRNBatch = 29183;
commit;

-- PO 6644 is over-received. I confirmed that extra parts indeed exist.

-- POs 6201 and 6130 cancel each other out

-- PO 6978 has never been invoiced

-- PO 6293 has invoice 37583652 in the invoice directory that was never imported
-- FIXED MANUALLY 2013-05-09 by Ian

-- PO 6667 has an extra invoice for ICL147, which is not part of the original order

-- PO 6506 received an extra 1000 pcs of ICL116

-- PO 7036 was over-invoiced. Digikey issued a credit (supp trans 16312)
-- but that credit is against purch var instead of uninvoiced inv.
begin;
delete from GLTrans where Type = 21 and TypeNo = 20130;
delete from SuppTrans where Type = 21 and TransNo = 20130;
delete from GLTrans where Type = 20 and TypeNo = 29697;
delete from SuppTrans where Type = 20 and TransNo = 29697;
update GLTrans set Narrative = '3 - GRN Credit Note 11499 - ICL147 x 750 @ 1.45'
where CounterIndex = 538434 and Account = 20100;
update GLTrans set Narrative = '3 - GRN Credit Note 11499 - ICL147 x 750 @ price var 0.29'
where CounterIndex = 538435 and Account = 59000;
commit;


begin;
update GLTrans set Amount = Amount - 610 - 4080 - 2246.40
where Type = 0 and TypeNo = 20053 and Account = 20100;
update GLTrans set Amount = Amount + 610 + 4080 + 2246.40
where Type = 0 and TypeNo = 20053 and Account = 50000;
commit;