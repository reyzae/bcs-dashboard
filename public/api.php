<?php
/**
 * API Router
 * Routes API requests to appropriate controllers
 */

// Load bootstrap first
require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/helpers/SecurityMiddleware.php';

// Set JSON response header
header('Content-Type: application/json');

// Enable CORS for development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-CSRF-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Apply security middleware
// RELAXED rate limit for development (500 requests per minute)
// Change to 60 for production
SecurityMiddleware::rateLimit(500, 60);

// DISABLED for development - causing 403 errors with localhost:3000
// SecurityMiddleware::validateOrigin();

// Clean up old rate limit cache files periodically (1 hour)
if (rand(1, 100) === 1) {
    SecurityMiddleware::cleanupCache(3600);
}

// Initialize database connection
try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Database connection failed',
        'message' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? $e->getMessage() : 'Internal server error'
    ]);
    exit;
}

// Get the controller and action from query string
$controller = $_GET['controller'] ?? '';
$action = $_GET['action'] ?? '';

// Map of valid controllers
$validControllers = [
    'auth' => __DIR__ . '/../app/controllers/AuthController.php',
    'dashboard' => __DIR__ . '/../app/controllers/DashboardController.php',
    'product' => __DIR__ . '/../app/controllers/ProductController.php',
    'customer' => __DIR__ . '/../app/controllers/CustomerController.php',
    'transaction' => __DIR__ . '/../app/controllers/TransactionController.php',
    'category' => __DIR__ . '/../app/controllers/CategoryController.php',
    'order' => __DIR__ . '/../app/controllers/OrderController.php',
    'payment' => __DIR__ . '/../app/controllers/PaymentController.php',
    'pos' => __DIR__ . '/../app/controllers/PosController.php',
    'settings' => __DIR__ . '/../app/controllers/SettingsController.php',
];

// Check if controller exists
if (!isset($validControllers[$controller])) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Controller not found']);
    exit;
}

// Load and execute controller
$controllerFile = $validControllers[$controller];

if (!file_exists($controllerFile)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Controller file not found']);
    exit;
}

// Set action in GET for controller to process
$_GET['action'] = $action;

// Include the controller file with error handling
try {
    require_once $controllerFile;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Controller execution failed',
        'message' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? $e->getMessage() : 'Internal server error',
        'trace' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true' ? $e->getTraceAsString() : null
    ]);
    exit;
}

