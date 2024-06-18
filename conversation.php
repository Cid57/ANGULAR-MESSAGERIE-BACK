<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'database.php';

$database = new Database();
$db = $database->getConnection();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id == 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid user ID."));
    exit();
}

$query = "SELECT c.id, c.name 
          FROM conversations c
          JOIN conversation_users cu ON c.id = cu.conversation_id
          WHERE cu.user_id = :user_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

$conversations_arr = array();
$conversations_arr["records"] = array();

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $conversation_item = array(
            "id" => $id,
            "name" => $name
        );
        array_push($conversations_arr["records"], $conversation_item);
    }
    http_response_code(200);
} else {
    http_response_code(200);
    $conversations_arr["records"] = array();
}

echo json_encode($conversations_arr);
