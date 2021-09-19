update PrintJob set error = '' where error is null;
alter table PrintJob modify column error varchar(255) not null default '';
alter table PrintJob add column printerID varchar(20) not null default '' after numCopies;
update PrintJob set printerID = 'standard' where printerID = '';