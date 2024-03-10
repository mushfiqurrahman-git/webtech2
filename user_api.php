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

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT * FROM `User` WHERE id = ?";
            $result = query($query, [$user_id]);
            $user = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user);
        } else {
            $query = "SELECT * FROM `User`";
            $result = query($query);
            $users = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $email = $data['email'];
        $password = $data['password'];
        $username = $data['username'];
        $purchase_history = isset($data['purchase_history']) ? $data['purchase_history'] : '';
        $shipping_address = isset($data['shipping_address']) ? $data['shipping_address'] : '';

        if (!validateEmail($email)) {
            header("HTTP/1.0 400 Bad Request");
            echo json_encode(['error' => 'Invalid email format']);
            exit;
        }

        $query = "INSERT INTO `User` (email, password, username, purchase_history, shipping_address) VALUES (?, ?, ?, ?, ?)";
        query($query, [$email, $password, $username, $purchase_history, $shipping_address]);
        echo json_encode(['message' => 'User created successfully']);
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}

?>
