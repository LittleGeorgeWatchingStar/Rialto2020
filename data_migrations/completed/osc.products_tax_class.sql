update products set products_tax_class_id = 1;

alter table products
modify `products_tax_class_id` int(11) NOT NULL default 1;
