alter table ProductFeature
change column image iconUri varchar(250) not null default '';

UPDATE ProductFeature SET iconUri = CONCAT('https://d3iwea566ns1n1.cloudfront.net/images/feature/', iconUri);
UPDATE ProductFeature SET iconUri = NULL WHERE iconUri = 'https://d3iwea566ns1n1.cloudfront.net/images/feature/';
