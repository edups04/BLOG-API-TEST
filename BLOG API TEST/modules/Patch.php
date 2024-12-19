<?php
class Patch{

    protected $pdo;

    public function __construct(\PDO $pdo){
        $this -> pdo = $pdo;
    }

    public function PatchPosts($body, $post_id){
        $values = [];
        $errmsg = "";
        $code = 0;

        foreach($body as $value){
            array_push($values, $value);
        }

        array_push($values, $post_id);

        try{
            $sqlString = "UPDATE post SET title=?, content=? WHERE post_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array("data"=>$data, "code"=>$code); 
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }

        
        return array("errmsg"=>$errmsg, "code"=>$code); 
    }

    public function archivePosts($post_id){
        $errmsg = "";
        $code = 0;

        try{
            $sqlString = "UPDATE post SET is_deleted=1 WHERE post_id = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute([$post_id]);

            $code = 200;
            $data = null;

            return array("data"=>$data, "code"=>$code); 
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $code = 400;
        }

        
        return array("errmsg"=>$errmsg, "code"=>$code); 
    }
}

?>