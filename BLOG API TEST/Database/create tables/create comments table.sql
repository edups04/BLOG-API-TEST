CREATE TABLE `comments` (
    `commentId`int(11),
    `postId` INT NOT NULL,
    `authorUsername` VARCHAR(50) NOT NULL,
    `content` TEXT NOT NULL,
    `createdAt` datetime DEFAULT current_timestamp(),
    `updatedAt` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `email` VARCHAR(100),
    `isdeleted` ```
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `comments`
  ADD PRIMARY KEY (`commentId`),
  ADD KEY `comments_ibfk_1` (`postId`);
  
  ALTER TABLE `comments`
  MODIFY `commentId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
  
  ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`postId`) REFERENCES `blogpost` (`psotId`) ON DELETE CASCADE;
  
  ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`postId`) REFERENCES `blogpost` (`postId`) ON DELETE CASCADE;

