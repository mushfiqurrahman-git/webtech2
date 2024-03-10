<?php

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
        // Retrieve cart details
        if (isset($_GET['cart_id'])) {
            // Retrieve cart details for a specific cart
            $cart_id = $_GET['cart_id'];
            $query = "SELECT * FROM Cart WHERE id = ?";
            $result = query($query, [$cart_id]);
            $cart = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($cart);
        } else {
            // Retrieve all carts
            $query = "SELECT * FROM Cart";
            $result = query($query);
            $carts = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($carts);
        }
        break;
    case 'POST':
        // Create a new cart
        $query = "INSERT INTO Cart (user_id) VALUES (?)";
        query($query, [$_POST['user_id']]);
        echo json_encode(['message' => 'Cart created successfully']);
        break;
    case 'PUT':
        // Add item to cart or update item quantity
        parse_str(file_get_contents("php://input"), $put_vars); // Get data from PUT request body

        $cart_id = $put_vars['cart_id']; // Assuming 'cart_id' is sent in the request
        $product_id = $put_vars['product_id']; // Assuming 'product_id' is sent in the request
        $quantity = $put_vars['quantity']; // Assuming 'quantity' is sent in the request

        // Check if the item already exists in the cart
        $query = "SELECT * FROM CartItems WHERE cart_id = ? AND product_id = ?";
        $result = query($query, [$cart_id, $product_id]);
        $existing_item = $result->fetch(PDO::FETCH_ASSOC);

        if ($existing_item) {
            // Update item quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $query = "UPDATE CartItems SET quantity = ? WHERE cart_id = ? AND product_id = ?";
            query($query, [$new_quantity, $cart_id, $product_id]);
            echo json_encode(['message' => 'Item quantity updated in cart']);
        } else {
            // Add new item to cart
            $query = "INSERT INTO CartItems (cart_id, product_id, quantity) VALUES (?, ?, ?)";
            query($query, [$cart_id, $product_id, $quantity]);
            echo json_encode(['message' => 'Item added to cart']);
        }
        break;
    case 'DELETE':
        // Remove item from cart or clear entire cart
        parse_str(file_get_contents("php://input"), $delete_vars); // Get data from DELETE request body

        $cart_id = $delete_vars['cart_id']; // Assuming 'cart_id' is sent in the request
        $product_id = $delete_vars['product_id']; // Assuming 'product_id' is sent in the request

        if ($product_id) {
            // Remove specific item from cart
            $query = "DELETE FROM CartItems WHERE cart_id = ? AND product_id = ?";
            query($query, [$cart_id, $product_id]);
            echo json_encode(['message' => 'Item removed from cart']);
        } else {
            // Clear entire cart
            $query = "DELETE FROM CartItems WHERE cart_id = ?";
            query($query, [$cart_id]);
            echo json_encode(['message' => 'Cart cleared']);
        }
        break;
    default:
        // Invalid request method
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?>