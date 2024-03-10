<?php
// Database connection code
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

function query($query, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

$request_method = $_SERVER['REQUEST_METHOD'];

switch ($request_method) {
    case 'GET':
        // GET End Point For USER API
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
        // POST End Point For USER API
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['email']) || !isset($data['password']) || !isset($data['username'])) {
            header("Bad Request");
            echo json_encode(['error' => 'Missing ']);
            exit;
        }
        $email = $data['email'];
        $password = $data['password'];
        $username = $data['username'];
        $purchase_history = isset($data['purchase_history']) ? $data['purchase_history'] : '';
        $shipping_address = isset($data['shipping_address']) ? $data['shipping_address'] : '';

        // Validating the email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            header("Bad Request");
            echo json_encode(['error' => 'not valid']);
            exit;
        }

        $query = "INSERT INTO `User` (email, password, username, purchase_history, shipping_address) VALUES (?, ?, ?, ?, ?)";
        query($query, [$email, $password, $username, $purchase_history, $shipping_address]);
        echo json_encode(['message' => 'User created ']);
        break;
    case 'PUT':
        // PUT End Point For USER API
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id']) || !isset($data['email']) || !isset($data['password']) || !isset($data['username'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing  fields']);
            exit;
        }
        $user_id = $data['id'];
        $email = $data['email'];
        $password = $data['password'];
        $username = $data['username'];
        $purchase_history = isset($data['purchase_history']) ? $data['purchase_history'] : '';
        $shipping_address = isset($data['shipping_address']) ? $data['shipping_address'] : '';

        // Validating the email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid format']);
            exit;
        }

        $query = "UPDATE `User` SET email = ?, password = ?, username = ?, purchase_history = ?, shipping_address = ? WHERE id = ?";
        query($query, [$email, $password, $username, $purchase_history, $shipping_address, $user_id]);
        echo json_encode(['message' => ' updated successfully']);
        break;

    case 'DELETE':
        // DELETE End Point For USER API
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT COUNT(*) FROM cart WHERE user_id = ?";
            $stmt = query($query, [$user_id]);
            $count = $stmt->fetchColumn();
            if ($count > 0) {
                header("Bad Request");
                echo json_encode(['error' => 'Cannot delete user']);
                exit;
            }
            $query = "DELETE FROM `User` WHERE id = ?";
            query($query, [$user_id]);
            echo json_encode(['message' => 'User deleted ']);
        } else {
            header("Bad Request");
            echo json_encode(['error' => 'Missing user_id']);
        }
        break;
    default:
        header("Method Not Allowed");
        break;
}
