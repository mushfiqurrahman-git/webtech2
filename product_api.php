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

// GET End Point For PRODUCT API
switch ($request_method) {
    case 'GET':
        if (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            $query = "SELECT * FROM Product WHERE id = ?";
            $result = query($query, [$product_id]);
            $product = $result->fetch(PDO::FETCH_ASSOC);
            echo json_encode($product);
        } else {
            $query = "SELECT * FROM Product";
            $result = query($query);
            $products = $result->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($products);
        }
        break;

    // POST End Point For PRODUCT API
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['description', 'pricing', 'shipping_cost'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                header("Bad Request", true, 400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
        }

        $description = $data['description'];
        $pricing = $data['pricing'];
        $shipping_cost = $data['shipping_cost'];

        // Check pricing and shipping_cost
        if (!is_numeric($pricing) || $pricing <= 0 || !is_numeric($shipping_cost) || $shipping_cost <= 0) {
            header("Bad Request", true, 400);
            echo json_encode(['error' => 'Invalid pricing or shipping cost']);
            exit;
        }

        // Optional image field
        $image = isset($data['image']) ? $data['image'] : null;

        $query = "INSERT INTO Product (description, image, pricing, shipping_cost) VALUES (?, ?, ?, ?)";
        query($query, [$description, $image, $pricing, $shipping_cost]);
        echo json_encode(['message' => 'Product created successfully']);
        break;

    // PUT End Point For PRODUCT API
    case 'PUT':
        $data = json_decode(file_get_contents("php://input"), true);
        $requiredFields = ['id', 'description', 'pricing', 'shipping_cost'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                header("Bad Request", true, 400);
                echo json_encode(['error' => 'Missing required fields']);
                exit;
            }
        }

        $product_id = $data['id'];
        $description = $data['description'];
        $pricing = $data['pricing'];
        $shipping_cost = $data['shipping_cost'];

        // Check pricing and shipping_cost
        if (!is_numeric($pricing) || $pricing <= 0 || !is_numeric($shipping_cost) || $shipping_cost <= 0) {
            header("Bad Request", true, 400);
            echo json_encode(['error' => 'Invalid pricing or shipping cost']);
            exit;
        }

        // Optional image field
        $image = isset($data['image']) ? $data['image'] : null;

        $query = "UPDATE Product SET description = ?, image = ?, pricing = ?, shipping_cost = ? WHERE id = ?";
        query($query, [$description, $image, $pricing, $shipping_cost, $product_id]);
        echo json_encode(['message' => 'Successfully Updated']);
        break;

    // DELETE End Point For PRODUCT API
    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['id'])) {
            header("Bad Request", true, 400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }
        $product_id = $data['id'];

        $query = "DELETE FROM Product WHERE id = ?";
        query($query, [$product_id]);
        echo json_encode(['message' => 'Successfully Deleted']);
        break;

    default:
        header("Method Not Allowed", true, 405);
        break;
}
?>
