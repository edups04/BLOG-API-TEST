<?php

//import get and post files
require_once "./config/database.php";
require_once "./modules/Get.php";
require_once "./modules/Post.php";
require_once "./modules/Patch.php";
//require_once "./modules/Delete.php";
require_once "./modules/Auth.php";
require_once "./modules/Image.php";

$db = new Connection();
$pdo = $db->connect();

//instantiate post, get class
$post = new Post($pdo);
$patch = new Patch($pdo);
$get = new Get($pdo);
//$delete = new Delete($pdo);
$auth = new Authentication($pdo);
$img = new Image($pdo);

//retrieved and endpoints and split
if(isset($_REQUEST['request'])){
    $request = explode("/", $_REQUEST['request']);
}
else{
    echo "URL does not exist.";
}

//get post put patch delete etc
//Request method - http request methods you will be using

switch($_SERVER['REQUEST_METHOD']){

    case "GET":
        if($auth->isAuthorized()){
        switch($request[0]){

            case "blogs":
                echo json_encode($get->getPosts($request[1] ?? null));
            break;

            case "comments":
                echo json_encode($get->getComments($request[1] ?? null));
            break;

            case "log";
                echo json_encode($get->getLogs($request[1] ?? date("Y-m-d")));
            break;

            case "fullblogs":
                if (count($request) > 1) {
                    // You can add parameters to get specific data if needed
                    echo json_encode($get->getData($request[1])); // If there are additional parameters
                } else {
                    echo json_encode($get->getData()); // If no specific parameters are passed
                }
                break;    

                case "allpostID":
                    // Check if additional parameters are passed (like a specific ID or filter)
                    if (count($request) > 1) {
                        // For example, if the second parameter is an ID, you can pass it to getJoinedSecond
                        echo json_encode($get->getDataID($request[1])); // if you want a specific record
                    }
                    break;
            

            default:
                http_response_code(401);
                echo "This is invalid endpoint";
            break;
        }
    }
    else {
        echo "Unauthorized";
    }

    break;


    case "POST":
        $body = json_decode(file_get_contents("php://input"));
        switch($request[0]){
            case "login":
                echo json_encode($auth->login($body));
            break;
            
            case "create":
                echo json_encode($auth->addAccount($body));
            break;

            case "blog":
                echo json_encode($post->postBlogs($body));
            break;

            case "comment":
                echo json_encode($post->postComments($body));
            break;

            case "tag":
                echo json_encode($post->postTags($body));
            break;

            case "image":
                if (isset($_FILES['filetoupload']) && isset($_POST['blogId'])) {
                    $file = $_FILES['filetoupload'];  // The uploaded file
                    $blogId = $_POST['blogId'];      // The blogId from the form data
            
                    // Instantiate the Image class
                    $image = new Image($pdo);  // Pass the database connection to the Image class
            
                    // Call the uploadFile method to handle the upload and save it to the database
                    $image->uploadFile($file, $blogId);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'No file uploaded or invalid request.']);
                }
                break;
            
            default:
                http_response_code(401);
                echo "This is invalid endpoint";
            break;
        }
    break;


    case "PATCH":
        $body = json_decode(file_get_contents("php://input"));
        switch($request[0]){
            case "blogs":
                echo json_encode($patch->patchPosts($body, $request[1]));
                break;
        }
    break;

    case "DELETE":
        switch($request[0]){
            case "blogs":
                echo json_encode($patch->archivePosts($request[1]));
            break;
        }
       // case "destroyshows":
         //   echo json_encode($delete->deleteShows($request[1]));
           // break;
    //break;    

    default:
        http_response_code(400);
        echo "Invalid Request Method.";
    break;
}



?>