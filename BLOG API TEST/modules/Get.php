<?php
include_once "Common.php";
class Get extends Common{

    protected $pdo;

    public function __construct(\PDO $pdo){
        $this -> pdo = $pdo;
    }

    public function getLogs($date = "2024-12-07") {
        $filename = "./logs/$date" . ".log";
        $logs = array();
        
        try {
            $file = new SplFileObject($filename);
            while (!$file->eof()) {
                array_push($logs, $file->fgets());
            }
            $remarks = "success";
            $message = "Successfully retrieved logs";
        } 
        catch (Exception $e) {
            $remarks = "failed";
            $message = $e->getMessage();
        }

        return $this->generateResponse(array("logs"=>$logs), $remarks, $message, 200);        
    }
  
    public function getPosts($blogId = null){
        $condition = "isdeleted = 0";
        if($blogId != null){
            $condition .= " AND blogId=" . $blogId;
        }

        $result = $this->getDataByTable('blogpost', $condition, $this->pdo);

        if($result['code'] == 200){
            if ($blogId !== null) {
                // Use the PATCH logic (incrementViewCount) here
                $patchResult = $this->incrementViewCount($blogId);
    
                if ($patchResult['code'] !== 200) {
                    // If increment fails, append error but still return the fetched data
                    return $this->generateResponse(
                        $result['data'], 
                        "partial_success", 
                        "Successfully retrieved records, but view count update failed: " . $patchResult['errmsg'], 
                        206
                    );
                }
            }   
    
            return $this->generateResponse(
                $result['data'], 
                "success", 
                "Successfully retrieved records.", 
                $result['code']
            );
        }

        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);
    }

    private function incrementViewCount($blogId) {
        $sql = "UPDATE blogpost SET views = views + 1 WHERE blogId = :blogId";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':blogId', $blogId, \PDO::PARAM_INT);
            $stmt->execute();
            return array("code" => 200, "message" => "View count incremented successfully.");
        } catch (\PDOException $e) {
            return array("code" => 500, "errmsg" => $e->getMessage());
        }
    }

    public function getComments($id = null){
        $condition = "isdeleted = 0";
        if($id != null){
            $condition .= " AND commentId=" . $id;
        }

        $result = $this->getDataByTable('comments', $condition, $this->pdo);
        if($result['code'] == 200){
            return $this->generateResponse($result['data'], "success", "successfully retrieved comments.", $result['code']);
        }
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);

    }

    public function getData() {
        $sql = "SELECT blogpost.author, blogpost.title, blogpost.views, blogpost.tagName, blogpost.image, blogpost.content, blogpost.createdAt, blogpost.updatedAt, comments.commentAuthor, comments.comment, comments.comment_createdAt, comments.comment_updatedAt 
                FROM blogpost INNER JOIN comments ON blogpost.blogId = comments.blogId 
                WHERE blogpost.isdeleted = 0 AND comments.isdeleted = 0";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            return $this->generateResponse($data, "success", "Successfully retrieved Blogs.", 200);
        } catch (\PDOException $e) {
            return $this->generateResponse(null, "failed", $e->getMessage(), 500);
        }
    }

    public function getDataID() {
        $sql = "SELECT blogpost.author, blogpost.title, blogpost.views, blogpost.tagName, blogpost.image, blogpost.content, blogpost.createdAt, blogpost.updatedAt, comments.commentAuthor, comments.comment, comments.comment_createdAt, comments.comment_updatedAt 
                FROM blogpost INNER JOIN comments ON blogpost.blogId = comments.blogId 
                WHERE blogpost.isdeleted = 0 AND comments.isdeleted = 0
                AND blogpost.blogId = :blogId";
        try {
            // Prepare and execute the SQL statement
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':blogId', $blogId, \PDO::PARAM_INT);  // Bind the id parameter
            $stmt->execute();
            
            // Fetch the result
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);  // Use fetch() to get one row
            
            // If data is found, return success; otherwise, handle no data case
            if ($data) {
            // Increment view count for the record
                $patchResult = $this->incrementViewCount($blogId);
        
                if ($patchResult['code'] !== 200) {
                // If increment fails, return partial success
                return $this->generateResponse(
                        $data,
                        "partial_success",
                        "Successfully retrieved the record, but view count update failed: " . $patchResult['errmsg'],
                        206
                    );
                }
            return $this->generateResponse($data, "success", "Successfully retrieved the record.", 200);
            } else {
            return $this->generateResponse(null, "failed", "No record found.", 404);  // Handle the case if no records are returned
            }
        } catch (\PDOException $e) {
            // Return error response if there is an issue with the query
            return $this->generateResponse(null, "failed", $e->getMessage(), 500);
        }
    }


}

?>