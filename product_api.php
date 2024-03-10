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
        // Handle GET requests
        if (isset($_GET['product_id'])) {
            // Retrieve a single product
            $product_id = $_GET['product_id'];
            $query = "SELECT * FROM Product WHERE id = ?";
            $result = query($query, [$product_id]);
            $product = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($product);
        } else {
            // Retrieve all products
            $query = "SELECT * FROM Product";
            $result = query($query);
            $products = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($products);
        }
        break;
    case 'POST':
        // Handle POST requests
        // Retrieve data from request body
        $data = json_decode(file_get_contents("php://input"), true);
        $description = $data['description'];
        $image = $data['image'];
        $pricing = $data['pricing'];
        $shipping_cost = $data['shipping_cost'];
        
        // Insert new product into database
        $query = "INSERT INTO Product (description, image, pricing, shipping_cost) VALUES (?, ?, ?, ?)";
        query($query, [$description, $image, $pricing, $shipping_cost]);
        echo json_encode(['message' => 'Product created successfully']);
        break;
    case 'PUT':
        // Handle PUT requests
        // Retrieve data from request body
        $data = json_decode(file_get_contents("php://input"), true);
        $product_id = $data['id'];
        $description = $data['description'];
        $image = $data['image'];
        $pricing = $data['pricing'];
        $shipping_cost = $data['shipping_cost'];
        
        // Update product in database
        $query = "UPDATE Product SET description = ?, image = ?, pricing = ?, shipping_cost = ? WHERE id = ?";
        query($query, [$description, $image, $pricing, $shipping_cost, $product_id]);
        echo json_encode(['message' => 'Product updated successfully']);
        break;
    case 'DELETE':
        // Handle DELETE requests
        // Retrieve product ID from request body
        $data = json_decode(file_get_contents("php://input"), true);
        $product_id = $data['id'];
        
        // Delete product from database
        $query = "DELETE FROM Product WHERE id = ?";
        query($query, [$product_id]);
        echo json_encode(['message' => 'Product deleted successfully']);
        break;
    default:
        // Invalid request method
        header("HTTP/1.0 405 Method Not Allowed");
        break;
}
?>
