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
        // GET End Point For Cart API
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $query = "SELECT * FROM cart WHERE user_id = ?";
            $result = query($query, [$user_id]);
            $cart = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($cart);
        } else {
            header("Bad Request");
            echo json_encode(['error' => 'User ID is missing']);
        }
        break;
    case 'POST':
        // POST End Point For Cart API
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['user_id']) || !isset($data['products']) || !isset($data['quantities'])) {
            header("Bad Request");
            echo json_encode(['error' => 'Missing fields']);
            exit;
        }
        $user_id = $data['user_id'];
        $products = $data['products'];
        $quantities = $data['quantities'];


        $query = "INSERT INTO cart (user_id, products, quantities) VALUES (?, ?, ?)";
        query($query, [$user_id, $products, $quantities]);
        echo json_encode(['message' => 'Cart created successfully']);
        break;
    case 'PUT':
        // PUT End Point For CART API
        $data = json_decode(file_get_contents("php://input"), true);
        $missingFields = [];

        if (!isset($data['cart_id'])) {
            $missingFields[] = 'cart_id';
        }
        if (!isset($data['user_id'])) {
            $missingFields[] = 'user_id';
        }
        if (!isset($data['products'])) {
            $missingFields[] = 'products';
        }
        if (!isset($data['quantities'])) {
            $missingFields[] = 'quantities';
        }

        if (!empty($missingFields)) {
            header("Bad Request");
            echo json_encode(['error' => 'Missing field(s): ' . implode(', ', $missingFields)]);
            exit;
        }
        $cart_id = $data['cart_id'];
        $user_id = $data['user_id'];
        $products = $data['products'];
        $quantities = $data['quantities'];

        $query = "UPDATE cart SET user_id = ?, products = ?, quantities = ? WHERE id = ?";
        query($query, [$user_id, json_encode($products), json_encode($quantities), $cart_id]);


        echo json_encode(['message' => 'Cart updated successfully']);
        break;
    case 'DELETE':
        // DELETE End Point For Cart API
        if (isset($_GET['cart_id'])) {
            $cart_id = $_GET['cart_id'];
            $query = "DELETE FROM cart WHERE id = ?";
            query($query, [$cart_id]);
            echo json_encode(['message' => 'Cart deleted successfully']);
        } else {
            header("Bad Request");
            echo json_encode(['error' => 'Missing cart_id']);
        }
        break;
    default:
        header(" Not Allowed");
        break;
}
