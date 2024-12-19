CREATE TABLE `accounts` (
  `accountId` INT PRIMARY KEY AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `token` text DEFAULT NULL,
  `isdeleted` tinyint(1) NOT NULL
);
