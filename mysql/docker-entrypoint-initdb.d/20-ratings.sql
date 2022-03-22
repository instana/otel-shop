CREATE DATABASE ratings DEFAULT CHARACTER SET 'utf8';

USE ratings;

CREATE TABLE ratings (
    sku varchar(80) NOT NULL,
    avg_rating DECIMAL(3, 2) NOT NULL,
    rating_count INT NOT NULL,
    PRIMARY KEY (sku)
) ENGINE=InnoDB;

CREATE USER 'ratings'@'%' IDENTIFIED BY 'iloveit';
CREATE USER 'ratings'@'localhost' IDENTIFIED BY 'iloveit';

GRANT ALL ON ratings.* TO 'ratings'@'%';
GRANT ALL ON ratings.* TO 'ratings'@'localhost';
