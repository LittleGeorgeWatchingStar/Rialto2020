alter table RecurringGLInvoices
modify column ID serial,
modify column RecurringID bigint unsigned not null default 0,
modify column Account int unsigned not null default 0;

alter table RecurringGLInvoices
add constraint RecurringGLInvoices_fk_RecurringID
foreign key (RecurringID) references RecurringInvoices (RecurringID)
on delete cascade;

alter table RecurringGLInvoices
add constraint RecurringGLInvoices_fk_Account
foreign key (Account) references ChartMaster (AccountCode)
on delete restrict;