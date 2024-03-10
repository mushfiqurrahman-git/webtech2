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
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT * FROM `Order` WHERE user_id = ?";
            $result = query($query, [$user_id]);
            $orders = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
        } else {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['error' => 'Invalid request']);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];
        $total_amount = $data['total_amount'];

        $query = "INSERT INTO `Order` (user_id, total_amount) VALUES (?, ?)";
        query($query, [$user_id, $total_amount]);
        echo json_encode(['message' => 'Order created successfully']);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

?>
