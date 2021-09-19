create table UserSubscription (
    userID varchar(20) not null,
    topic varchar(100) not null,
    primary key (userID, topic),
    constraint UserSubscription_fk_userID
    foreign key (userID) references WWW_Users (UserID)
    on delete cascade
) engine=InnoDB default character set=utf8;

alter table WWW_Users modify column Email varchar(55) not null default '';