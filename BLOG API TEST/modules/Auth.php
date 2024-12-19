<?php
include_once "Common.php";
class Authentication extends Common{

    protected $pdo;

    public function __construct(\PDO $pdo){
        $this -> pdo = $pdo;
    }

    public function isAuthorized() {
        //compare request token to database token
        $headers = getallheaders();
        return $headers['Authorization'] == $this->getToken();
    }

    private function getToken(){
        //retrieve token from database
        $headers = getallheaders();
        
        $sqlString = "SELECT token FROM users WHERE username=?";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$headers['X-Auth-User']]);
            $result = $stmt->fetchAll()[0];

            return $result['token'];
        }
            
    private function generateHeader() {
            $header = [
                "typ" => "JWT",
                "alg" => "HS256",
                "app" => "Bloggers",
                "dev" => "Los Pollos Hermanos"
            ];
            return base64_encode(json_encode($header));
    }

    private function generatePayload($user_id, $username) {
        $payload = [
            "uid" => $user_id,
            "uc" => $username,
            "email" => "tedbatallones2004@gmail.com",
            "date" => date_create(),
            "exp" => date("Y-m-d H:i:s")
        ];
        return base64_encode(json_encode($payload));
    }

    private function generateToken($user_id, $username) {
        $header = $this->generateHeader();
        $payload = $this->generatePayload($user_id, $username);
        $signature = hash_hmac("sha256", "$header.$payload", TOKEN_KEY);
        return "$header.$payload." . base64_encode($signature);
    }

    private function isSamePassword($inputPassword, $existingHash){
        $hash = crypt($inputPassword, $existingHash);
        return $hash == $existingHash;
    }

    private function encryptPassword($password){
        $hashFormat = "$2y$10$";
        $saltLength = 22;
        $salt = $this->generateSalt($saltLength);
        return crypt ($password, $hashFormat . $salt);
    }

    private function generateSalt($length){
        $urs = md5(mt_rand(), true);
        $b64String = base64_encode($urs);
        $mb64String = str_replace("+", ".", $b64String);
        return substr($mb64String, 0, $length);
    }

    public function saveToken ($token, $username){
        $errmsg = "";
        $code = 0;

        try{
            $sqlString = "UPDATE users SET token=? WHERE username = ?";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute( [$token, $username] );

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
    
    public function login($body){
        
        $username = $body->username;
        $password = $body->password;

        $code = 0;
        $payload = "";
        $remarks = "";
        $message = "";

        try{
            $sqlString = "SELECT user_id, username, password, token FROM users WHERE username=?";
            $stmt = $this->pdo->prepare($sqlString);
            $stmt->execute([$username]);

            if ($stmt->rowCount() >0 ){
                $result = $stmt->fetchAll()[0];
                if ($this->isSamePassword($password, $result['password'])){
                        $code = 200;
                        $remarks = "Success";
                        $message = "Logged in successfully";

                        $token = $this->generateToken($result['user_id'], $result['username']);
                        $token_arr = explode('.', $token);
                        $this->saveToken($token_arr[2], $result['username']);

                        $payload = array ("user_id"=>$result['user_id'], "username"=>$result['username'], "token"=>$token_arr[2]);
                }
                else{
                $code = 401;
                $payload = null;
                $remarks = "failed";
                $message = "Incorrect Password.";
                }
            }
            else{
                $code = 401;
                $payload = null;
                $remarks = "failed";
                $message = "Username does not exist.";
            }
        }
        catch(\PDOException $e){
            $errmsg = $e->getMessage();
            $remarks = "failed";
            $code = 400;
        }
        return array ("payload"=>$payload, "remarks"=>$remarks, "message"=>$message, "code"=>$code);

    }

    public function addAccount($body){
        $values = [];
        $errmsg = "";
        $code = 0;

        $body->password = $this->encryptPassword($body->password);
        //$body['password'] = $this->encryptPassword($body['password']);

        foreach ($body as $value) {
            array_push($values, $value);
        }

        try {
            $sqlString = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
            $sql = $this->pdo->prepare($sqlString);
            $sql->execute($values);

            $code = 200;
            $data = null;

            return array("data" => $data, "code" => $code);
        }
        catch (\PDOException $e) {
            $errmsg = $e->getMessage();
            $code = 400;
        }
        
        return array("errmsg" => $errmsg, "code" => $code);
    }
}

?>