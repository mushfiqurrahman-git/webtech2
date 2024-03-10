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

function query($query, $params = []) {
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt;
}

$request_method = $_SERVER['REQUEST_METHOD'];

// GET End Point For ORDER API
switch ($request_method) {
    case 'GET':
        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $query = "SELECT * FROM `order` WHERE id = ?";
            $result = query($query, [$order_id]);
            $order = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($order);
        } else {
            $query = "SELECT * FROM `order`";
            $result = query($query);
            $orders = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($orders);
        }
        break;

    // POST End Point For ORDER API
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['user_id', 'total_amount'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                header("Bad Request");
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
        }

        $user_id = $data['user_id'];
        $total_amount = $data['total_amount'];

        // if total_amount is valid 
        if (!is_numeric($total_amount) || $total_amount <= 0) {
            header("Bad Request");
            echo json_encode(['error' => 'Invalid amount']);
            exit;
        }

        $query = "INSERT INTO `order` (user_id, total_amount) VALUES (?, ?)";
        query($query, [$user_id, $total_amount]);
        echo json_encode(['message' => 'Successfully Created']);
        break;

    // PUT End Point For ORDER API
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['id', 'user_id', 'total_amount'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                header("Bad Request");
                echo json_encode(['error' => 'Missing fields']);
                exit;
            }
        }

        $order_id = $data['id'];
        $user_id = $data['user_id'];
        $total_amount = $data['total_amount'];

        // if total_amount is valid
        if (!is_numeric($total_amount) || $total_amount <= 0) {
            header("Bad Request");
            echo json_encode(['error' => 'Invalid amount']);
            exit;
        }

        $query = "UPDATE `order` SET user_id = ?, total_amount = ? WHERE id = ?";
        query($query, [$user_id, $total_amount, $order_id]);
        echo json_encode(['message' => 'Successfully Updated']);
        break;

    // DELETE End Point For ORDER API
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        $order_id = $data['id'];

        $query = "DELETE FROM `order` WHERE id = ?";
        query($query, [$order_id]);
        echo json_encode(['message' => 'Successfully Deleted']);
        break;

    default:
        header("Not Allowed");
        break;
}
?>