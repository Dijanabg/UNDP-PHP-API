CREATE DATABASE database_name;

USE database_name;

CREATE TABLE product (
  product_id INT PRIMARY KEY,
  product_name VARCHAR(50) NOT NULL,
  product_description VARCHAR(255),
  price DECIMAL(10, 2) NOT NULL,
  quantity INT NOT NULL
);


INSERT INTO product (product_id, product_name, product_description, price, quantity)
VALUES (1, 'Widget A', 'A high-quality widget for everyday use', 19.99, 100),
       (2, 'Widget B', 'A premium widget for advanced users', 49.99, 50);


CREATE TABLE users (
  id INT(11) NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  type ENUM('admin', 'user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
);

INSERT INTO users (username, password, type) VALUES
  ('admin', '123', 'admin'),
  ('user1', '456', 'user'),
  ('user2', '789', 'user');


