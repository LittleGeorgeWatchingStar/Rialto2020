drop table if exists Security_SsoLink;
CREATE TABLE Security_SsoLink (uuid VARCHAR(255) NOT NULL, userID VARCHAR(20) NOT NULL, INDEX IDX_D48B2A145FD86D04 (userID), PRIMARY KEY(uuid)) DEFAULT CHARACTER SET utf8 ENGINE = InnoDB;
ALTER TABLE Security_SsoLink ADD CONSTRAINT FK_D48B2A145FD86D04 FOREIGN KEY (userID) REFERENCES WWW_Users (UserID);

insert into Security_SsoLink
(uuid, userID)
select uuid, userID
from WWW_Users
where uuid is not null;

alter table WWW_Users drop key `uuid`, drop column uuid;