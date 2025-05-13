<?php
// Prevent direct access to this file
if (!defined('BASEPATH')) {
    define('BASEPATH', true);
}

// Set headers for JSON API response
header('Content-Type: application/json');

// Include database connection
require_once '../../config/databade.php';

// Get request data
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Check if the request contains valid JSON
if ($request_body && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid JSON data'
    ]);
    exit;
}

// Default action if none provided
$action = isset($data['action']) ? $data['action'] : '';

// Process based on action
switch ($action) {
    case 'add':
        addRoute($conn, $data);
        break;
    case 'list':
        listRoutes($conn);
        break;
    case 'delete':
        deleteRoute($conn, $data);
        break;
    default:
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid action specified'
        ]);
        break;
}

/**
 * Add a new route
 */
function addRoute($conn, $data) {
    // Validate required fields
    if (!isset($data['name']) || empty(trim($data['name']))) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Route name is required'
        ]);
        return;
    }

    // Sanitize input
    $name = mysqli_real_escape_string($conn, trim($data['name']));

    // Check if route already exists
    $checkQuery = "SELECT COUNT(*) as count FROM routes WHERE name = '$name'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if ($checkResult) {
        $row = mysqli_fetch_assoc($checkResult);
        if ($row['count'] > 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Route with this name already exists'
            ]);
            return;
        }
    }

    // Insert route
    $query = "INSERT INTO routes (name) VALUES ('$name')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Route added successfully',
            'id' => mysqli_insert_id($conn)
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to add route: ' . mysqli_error($conn)
        ]);
    }
}

/**
 * List all routes
 */
function listRoutes($conn) {
    $query = "SELECT id, name FROM routes ORDER BY name ASC";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        $routes = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $routes[] = [
                'id' => $row['id'],
                'name' => $row['name']
            ];
        }
        
        echo json_encode([
            'status' => 'success',
            'data' => $routes
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to fetch routes: ' . mysqli_error($conn)
        ]);
    }
}

/**
 * Delete a route
 */
function deleteRoute($conn, $data) {
    // Validate required fields
    if (!isset($data['id']) || empty($data['id'])) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Route ID is required'
        ]);
        return;
    }

    // Sanitize input
    $id = mysqli_real_escape_string($conn, $data['id']);

    // Check if route exists
    $checkQuery = "SELECT COUNT(*) as count FROM routes WHERE id = '$id'";
    $checkResult = mysqli_query($conn, $checkQuery);
    
    if ($checkResult) {
        $row = mysqli_fetch_assoc($checkResult);
        if ($row['count'] == 0) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Route not found'
            ]);
            return;
        }
    }

    // Delete route
    $query = "DELETE FROM routes WHERE id = '$id'";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Route deleted successfully'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete route: ' . mysqli_error($conn)
        ]);
    }
}
?>
