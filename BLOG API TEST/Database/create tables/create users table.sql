CREATE TABLE `users` (
    `userId` INT PRIMARY KEY AUTO_INCREMENT,
    `accountId` INT NOT NULL,
    `firstName` VARCHAR(255) NOT NULL,
    `lastName` VARCHAR(255) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `isdeleted` tinyint(1) NOT NULL,
    FOREIGN KEY (accountId) REFERENCES accounts (accountId)
);

SELECT * FROM users;

