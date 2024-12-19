<?php

include_once "Common.php";
class Post extends Common{

    protected $pdo;

    public function __construct(\PDO $pdo){
        $this -> pdo = $pdo;
    }
    public function postBlogs($body){
        $result = $this->postData("blogpost", $body, $this->pdo);
        if($result['code'] == 200){
            $this->logger("User", "POST", "Created a new Blog Post.");
            return $this->generateResponse($result['data'], "Success", "Successfully created a new Blog Post.", $result['code']);
        }
        $this->logger("User", "POST", $result['errmsg']);
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);

    }

    public function postComments($body){
        $result = $this->postData("comments", $body, $this->pdo);
        if($result['code'] == 200){
            $this->logger("User", "POST", "Successfully commented on a Blog.");
            return $this->generateResponse($result['data'], "Success", "Successfully commented on a Blog.", $result['code']);
        }
        $this->logger("User", "POST", $result['errmsg']);
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);    
    }

    public function postTags($body){
        $result = $this->postData("tags", $body, $this->pdo);
        if($result['code'] == 200){
            $this->logger("User", "POST", "Created a new Blog Post.");
            return $this->generateResponse($result['data'], "Success", "Successfully created a new Blog Post.", $result['code']);
        }
        $this->logger("User", "POST", $result['errmsg']);
        return $this->generateResponse(null, "failed", $result['errmsg'], $result['code']);

    }
}

?>