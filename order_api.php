<?php

// Database connection parameters
$host = 'localhost';
$dbname = 'mystore';
$username = 'root';
$password = '';

// Establish database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Function to handle database queries
function query($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

// API endpoints
$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        // Retrieve orders for a specific user
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT * FROM `Order` WHERE user_id = ?";
            $result = query($query, [$user_id]);
            $orders = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
        } else {
            // Invalid request
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['error' => 'Invalid request']);
        }
        break;
    case 'POST':
        // Create a new order
        $data = json_decode(file_get_contents("php://input"), true);
        $user_id = $data['user_id'];
        $total_amount = $data['total_amount'];

        $query = "INSERT INTO `Order` (user_id, total_amount) VALUES (?, ?)";
        query($query, [$user_id, $total_amount]);
        echo json_encode(['message' => 'Order created successfully']);
        break;
    default:
        // Invalid request method
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

?>
