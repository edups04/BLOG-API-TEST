<?php

class Image {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function uploadFile($file, $post_id) {
        // Define the target directory where files will be uploaded
        $targetDir = "uploads/";
        $targetFile = $targetDir . basename($file["name"]);
        $uploadOk = 1;

        // Get the file extension
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

        // Check if the file is an actual image or fake image
        $check = getimagesize($file["tmp_name"]);
        if ($check !== false) {
            // File is an image
            $uploadOk = 1;
        } else {
            echo "File is not an image.";
            $uploadOk = 0;
        }

        // Check if file already exists
        if (file_exists($targetFile)) {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }

        // Check the file size (maximum size: 500KB)
        if ($file["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow only specific image formats (JPG, PNG, JPEG, GIF)
        if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // If upload validation passes, move the uploaded file
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
        } else {
            if (move_uploaded_file($file["tmp_name"], $targetFile)) {
                // File was successfully uploaded, now save the file details to the database
                echo "The file " . htmlspecialchars(basename($file["name"])) . " has been uploaded.";

                // Save the file to the database with associated post_id (either insert or update)
                $this->saveImageToDatabase($post_id, $targetFile);
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }

    public function saveImageToDatabase($post_id, $file) {
        // Check if the post_id already exists in the database
        $stmt = $this->db->prepare("SELECT post_id FROM post WHERE post_id = :post_id");
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
        $stmt->execute();

        // If the post_id exists, update the image path
        if ($stmt->rowCount() > 0) {
            // Update the existing record
            $updateStmt = $this->db->prepare("UPDATE post SET image = :file_path WHERE post_id = :post_id");
            $updateStmt->bindValue(':file_path', $file, PDO::PARAM_STR);
            $updateStmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
            if ($updateStmt->execute()) {
                echo "Image updated in the database.";
            } else {
                echo "Error updating image in the database: " . $updateStmt->errorInfo()[2];
            }
        } else {
            // If the post_id does not exist, insert a new record
            $insertStmt = $this->db->prepare("INSERT INTO post (post_id, image) VALUES (:post_id, :file_path)");
            $insertStmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);
            $insertStmt->bindValue(':file_path', $file, PDO::PARAM_STR);
            if ($insertStmt->execute()) {
                echo "Image inserted into the database.";
            } else {
                echo "Error inserting image into the database: " . $insertStmt->errorInfo()[2];
            }
        }
    }

    // Function to display image by post_id
    public function getImagePostId($post_id) {
        // Prepare SQL query to get the image file path by post_id
        $stmt = $this->db->prepare("SELECT image FROM post WHERE post_id = :post_id");
        $stmt->bindValue(':post_id', $post_id, PDO::PARAM_INT);

        // Execute the query
        if ($stmt->execute()) {
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                // Return the file path or URL to the image
                return $result['image'];
            } else {
                echo "No image found for the specified blog.";
            }
        } else {
            echo "Error fetching image from the database.";
        }
    }
}
?>
