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

        //GET End Point For CART API

        if (isset($_GET['cart_id'])) {
            $cart_id = $_GET['cart_id'];
            $query = "SELECT * FROM Cart WHERE id = ?";
            $result = query($query, [$cart_id]);
            $cart = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($cart);
        } else {
            $query = "SELECT * FROM Cart";
            $result = query($query);
            $carts = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($carts);
        }
        break;
    case 'POST':

        //POST End Point For CART API

        if (isset($_POST['user_id'])) {
            $user_id = $_POST['user_id'];
            $query = "INSERT INTO Cart (user_id) VALUES (?)";
            query($query, [$user_id]);
            echo json_encode(['message' => 'Successfully Created']);
        } else {
            header("Bad Request");
            echo json_encode(['error' => 'User ID is missing']);
        }
        break;
    case 'PUT':

        //PUT End Point For CART API

        parse_str(file_get_contents("php://input"), $put_vars);
        $cart_id = $put_vars['cart_id'];
        $product_id = $put_vars['product_id'];
        $quantity = $put_vars['quantity'];

        $query = "SELECT * FROM CartItems WHERE cart_id = ? AND product_id = ?";
        $result = query($query, [$cart_id, $product_id]);
        $existing_item = $result->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            $new_quantity = $existing_item['quantity'] + $quantity;
            $query = "UPDATE CartItems SET quantity = ? WHERE cart_id = ? AND product_id = ?";
            query($query, [$new_quantity, $cart_id, $product_id]);
            echo json_encode(['message' => 'Quantity Updated']);
        } else {
            $query = "INSERT INTO CartItems (cart_id, product_id, quantity) VALUES (?, ?, ?)";
            query($query, [$cart_id, $product_id, $quantity]);
            echo json_encode(['message' => 'Item added to cart']);
        }
        break;
    case 'DELETE':

        //DELETE End Point For CART API

        parse_str(file_get_contents("php://input"), $delete_vars);
        $cart_id = $delete_vars['cart_id'];
        $product_id = $delete_vars['product_id'];

        if ($product_id) {
            $query = "DELETE FROM CartItems WHERE cart_id = ? AND product_id = ?";
            query($query, [$cart_id, $product_id]);
            echo json_encode(['message' => 'Removed Item']);
        } else {
            $query = "DELETE FROM CartItems WHERE cart_id = ?";
            query($query, [$cart_id]);
            echo json_encode(['message' => 'Cleared']);
        }
        break;
    default:
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
