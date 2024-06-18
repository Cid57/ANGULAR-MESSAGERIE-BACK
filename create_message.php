<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once 'database.php';
include_once 'message.php';

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    http_response_code(503);
    echo json_encode(array("message" => "Database connection failed."));
    exit();
}

$data = json_decode(file_get_contents("php://input"));

if (is_null($data)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid JSON input."));
    exit();
}

$conversation_id = $data->conversation_id ?? 0;

$query = "SELECT id FROM conversations WHERE id = :conversation_id";
$stmt = $db->prepare($query);
$stmt->bindParam(":conversation_id", $conversation_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid conversation ID."));
    exit();
}

$message = new Message($db);
$message->content = $data->content ?? '';
$message->timestamp = date('Y-m-d H:i:s');
$message->conversation_id = $conversation_id;

try {
    if ($message->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "Message was created."));
    } else {
        throw new Exception("Unable to create message.");
    }
} catch (Exception $e) {
    http_response_code(503);
    echo json_encode(array("message" => $e->getMessage(), "error" => $e->getTraceAsString()));
}
