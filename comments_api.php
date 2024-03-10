<?php

$host = 'localhost';
$dbname = 'mystore';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

function query($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        if (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            $query = "SELECT * FROM Comments WHERE product_id = ?";
            $result = query($query, [$product_id]);
            $comments = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($comments);
        } elseif (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT * FROM Comments WHERE user_id = ?";
            $result = query($query, [$user_id]);
            $comments = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($comments);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['error' => 'Invalid request']);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $product_id = $data['product_id'];
        $user_id = $data['user_id'];
        $rating = $data['rating'];
        $image = $data['image'];
        $text = $data['text'];

        $query = "INSERT INTO Comments (product_id, user_id, rating, image, text) VALUES (?, ?, ?, ?, ?)";
        query($query, [$product_id, $user_id, $rating, $image, $text]);
        echo json_encode(['message' => 'Comment created successfully']);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

?>
