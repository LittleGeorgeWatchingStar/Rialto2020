update UserRole set roleId = 'ROLE_API_CLIENT' where roleId = 'ROLE_OSCOMMERCE';

replace into UserRole values
('gumstix_com', 'ROLE_SALES'),
('madison', 'ROLE_API_CLIENT'),
('knkt', 'ROLE_WAREHOUSE'),
('theresalk', 'ROLE_WAREHOUSE'),
('gordon', 'ROLE_WAREHOUSE'),
('ianfp', 'ROLE_WAREHOUSE');