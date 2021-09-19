SELECT products_model, weberpcode FROM products WHERE products_model != weberpcode;
ALTER TABLE products MODIFY products_model varchar(20);
UPDATE products SET products_model = weberpcode WHERE products_model != weberpcode;
