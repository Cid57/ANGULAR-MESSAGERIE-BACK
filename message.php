<?php
class Message
{
    private $conn;
    private $table_name = "messages";

    public $id;
    public $content;
    public $timestamp;
    public $conversation_id;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET content=:content, timestamp=:timestamp, conversation_id=:conversation_id";

        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->timestamp = htmlspecialchars(strip_tags($this->timestamp));
        $this->conversation_id = htmlspecialchars(strip_tags($this->conversation_id));

        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":timestamp", $this->timestamp);
        $stmt->bindParam(":conversation_id", $this->conversation_id);

        if ($stmt->execute()) {
            return true;
        }

        return false;
    }

    function read()
    {
        $query = "SELECT id, content, timestamp, conversation_id FROM " . $this->table_name . " WHERE conversation_id = ? ORDER BY timestamp DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->conversation_id);
        $stmt->execute();

        return $stmt;
    }
}
