<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

include_once 'database.php';
include_once 'message.php';

$database = new Database();
$db = $database->getConnection();

$message = new Message($db);

$conversation_id = isset($_GET['conversation_id']) ? intval($_GET['conversation_id']) : 0;

if ($conversation_id == 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid conversation ID."));
    exit();
}

// Vérifiez si la conversation existe
$query = "SELECT id FROM conversations WHERE id = :conversation_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":conversation_id", $conversation_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid conversation ID."));
    exit();
}

$message->conversation_id = $conversation_id;

$stmt = $message->read();
$num = $stmt->rowCount();

$messages_arr = array();
$messages_arr["records"] = array();

if ($num > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $message_item = array(
            "id" => $id,
            "content" => $content,
            "timestamp" => $timestamp,
            "conversation_id" => $conversation_id
        );
        array_push($messages_arr["records"], $message_item);
    }
    http_response_code(200);
} else {
    http_response_code(200);
    $messages_arr["records"] = array();
}

echo json_encode($messages_arr);
