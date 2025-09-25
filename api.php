<?php
/**
 * api.php
 *
 * This file serves as the backend API for the Lost & Found web application.
 * It handles all CRUD operations for items and comments, processing AJAX requests
 * from script.js and returning JSON responses.
 *
 * It uses PDO with prepared statements for database interaction to prevent SQL injection.
 * Image uploads are handled by moving the file to an 'uploads/' directory.
 */

// 1. Include the database connection
require_once 'db.php'; // Ensures $pdo is available

// 2. Set headers for JSON response and allow CORS (for development, consider stricter policies in production)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin (for development)
header('Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With');

// Handle OPTIONS requests (pre-flight checks for CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// 3. Define the uploads directory
$uploads_dir = 'uploads/';
if (!is_dir($uploads_dir)) {
    mkdir($uploads_dir, 0777, true); // Create directory if it doesn't exist
}

// 4. Get the action from the request
$action = $_REQUEST['action'] ?? ''; // Using $_REQUEST to handle both GET and POST for action param

// 5. Route requests based on action
switch ($action) {
    case 'getItems':
        getItems($pdo, $uploads_dir);
        break;
    case 'getItem':
        getItem($pdo);
        break;
    case 'addItem':
        addItem($pdo, $uploads_dir);
        break;
    case 'updateItem':
        updateItem($pdo, $uploads_dir);
        break;
    case 'deleteItem':
        deleteItem($pdo, $uploads_dir);
        break;
    case 'addComment':
        addComment($pdo);
        break;
    default:
        // Invalid action requested
        echo json_encode(['success' => false, 'message' => 'Invalid API action.']);
        http_response_code(400); // Bad Request
        break;
}

/**
 * Fetches all items, optionally filtered by search query and category.
 * Also fetches associated comments for each item.
 *
 * @param PDO $pdo The PDO database connection object.
 */
function getItems(PDO $pdo, $uploads_dir) {
    $query = $_GET['query'] ?? '';
    $category = $_GET['category'] ?? '';

    $sql = "SELECT id, title, description, image, status, created_at FROM items WHERE 1=1";
    $params = [];

    if (!empty($query)) {
        $sql .= " AND (title LIKE :query OR description LIKE :query)";
        $params[':query'] = '%' . $query . '%';
    }

    if (!empty($category)) {
        $sql .= " AND status = :category";
        $params[':category'] = $category;
    }

    $sql .= " ORDER BY created_at DESC";

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll();

        // Fetch comments for each item
        foreach ($items as &$item) {
            $stmtComments = $pdo->prepare("SELECT user_name, comment, created_at FROM comments WHERE item_id = :item_id ORDER BY created_at ASC");
            $stmtComments->execute([':item_id' => $item['id']]);
            $item['comments'] = $stmtComments->fetchAll();
            // Ensure image path is correct for frontend (though frontend script.js already handles UPLOADS_FOLDER)
            // if ($item['image']) {
            //     $item['image'] = $uploads_dir . $item['image'];
            // }
        }

        echo json_encode(['success' => true, 'items' => $items]);
    } catch (\PDOException $e) {
        error_log("Error fetching items: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error fetching items.', 'error' => $e->getMessage()]);
        http_response_code(500); // Internal Server Error
    }
}

/**
 * Fetches a single item by its ID.
 * Also fetches associated comments.
 *
 * @param PDO $pdo The PDO database connection object.
 */
function getItem(PDO $pdo) {
    $id = $_GET['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID.']);
        http_response_code(400); // Bad Request
        return;
    }

    try {
        // Fetch item details
        $stmt = $pdo->prepare("SELECT id, title, description, image, status, created_at FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $item = $stmt->fetch();

        if ($item) {
            // Fetch comments for the item
            $stmtComments = $pdo->prepare("SELECT user_name, comment, created_at FROM comments WHERE item_id = :item_id ORDER BY created_at ASC");
            $stmtComments->execute([':item_id' => $item['id']]);
            $item['comments'] = $stmtComments->fetchAll();
            echo json_encode(['success' => true, 'item' => $item]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Item not found.']);
            http_response_code(404); // Not Found
        }
    } catch (\PDOException $e) {
        error_log("Error fetching single item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error fetching item.', 'error' => $e->getMessage()]);
        http_response_code(500); // Internal Server Error
    }
}

/**
 * Adds a new item to the database. Handles image upload.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $uploads_dir The directory for image uploads.
 */
function addItem(PDO $pdo, $uploads_dir) {
    // Expecting POST data from FormData, so use $_POST and $_FILES
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? ''; // This is 'category' from frontend

    if (empty($title) || empty($description) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Title, description, and status are required.']);
        http_response_code(400);
        return;
    }

    $image_filename = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['image']['tmp_name'];
        $file_name = uniqid() . '_' . basename($_FILES['image']['name']); // Generate unique filename
        $file_destination = $uploads_dir . $file_name;

        if (move_uploaded_file($file_tmp_name, $file_destination)) {
            $image_filename = $file_name;
        } else {
            // Log error, but don't stop item creation if image upload fails
            error_log("Failed to move uploaded image: " . $_FILES['image']['name']);
            // Optionally: echo json_encode(['success' => false, 'message' => 'Failed to upload image.']); return;
        }
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO items (title, description, image, status) VALUES (:title, :description, :image, :status)"
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image' => $image_filename,
            ':status' => $status
        ]);

        echo json_encode(['success' => true, 'message' => 'Item added successfully.', 'id' => $pdo->lastInsertId()]);
    } catch (\PDOException $e) {
        error_log("Error adding item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error adding item.', 'error' => $e->getMessage()]);
        http_response_code(500);
    }
}

/**
 * Updates an existing item in the database. Handles image upload/replacement.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $uploads_dir The directory for image uploads.
 */
function updateItem(PDO $pdo, $uploads_dir) {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $status = $_POST['status'] ?? '';

    if (!$id || !is_numeric($id) || empty($title) || empty($description) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Invalid data or item ID for update.']);
        http_response_code(400);
        return;
    }

    try {
        // First, get existing item to know current image if any
        $stmt = $pdo->prepare("SELECT image FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $existing_item = $stmt->fetch();
        $current_image_filename = $existing_item['image'] ?? null;

        $image_filename = $current_image_filename; // Default to current image

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Delete old image if it exists
            if ($current_image_filename && file_exists($uploads_dir . $current_image_filename)) {
                unlink($uploads_dir . $current_image_filename);
            }

            $file_tmp_name = $_FILES['image']['tmp_name'];
            $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $file_destination = $uploads_dir . $file_name;

            if (move_uploaded_file($file_tmp_name, $file_destination)) {
                $image_filename = $file_name;
            } else {
                error_log("Failed to move uploaded image during update: " . $_FILES['image']['name']);
                // Item will be updated, but image might not be. This is a design choice.
            }
        } elseif (isset($_POST['image']) && empty($_POST['image']) && $current_image_filename) {
             // If frontend explicitly sent empty 'image' and there was one, delete it
             if ($current_image_filename && file_exists($uploads_dir . $current_image_filename)) {
                unlink($uploads_dir . $current_image_filename);
            }
            $image_filename = null; // Set image to null in DB
        }
        // If image field was not sent or sent with existing value, $image_filename remains current_image_filename


        $stmt = $pdo->prepare(
            "UPDATE items SET title = :title, description = :description, image = :image, status = :status WHERE id = :id"
        );
        $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':image' => $image_filename,
            ':status' => $status,
            ':id' => $id
        ]);

        echo json_encode(['success' => true, 'message' => 'Item updated successfully.']);
    } catch (\PDOException $e) {
        error_log("Error updating item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error updating item.', 'error' => $e->getMessage()]);
        http_response_code(500);
    }
}

/**
 * Deletes an item from the database. Also deletes its associated image file.
 * Comments are deleted automatically due to ON DELETE CASCADE foreign key.
 *
 * @param PDO $pdo The PDO database connection object.
 * @param string $uploads_dir The directory for image uploads.
 */
function deleteItem(PDO $pdo, $uploads_dir) {
    // Expecting POST data from frontend
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    if (!$id || !is_numeric($id)) {
        echo json_encode(['success' => false, 'message' => 'Invalid item ID for deletion.']);
        http_response_code(400);
        return;
    }

    try {
        // Get image filename before deleting item from DB
        $stmt = $pdo->prepare("SELECT image FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $item = $stmt->fetch();

        $pdo->beginTransaction(); // Start transaction

        $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit(); // Commit transaction

        // Delete image file after successful DB deletion
        if ($item && $item['image'] && file_exists($uploads_dir . $item['image'])) {
            unlink($uploads_dir . $item['image']);
        }

        echo json_encode(['success' => true, 'message' => 'Item deleted successfully.']);
    } catch (\PDOException $e) {
        $pdo->rollBack(); // Rollback transaction on error
        error_log("Error deleting item: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error deleting item.', 'error' => $e->getMessage()]);
        http_response_code(500);
    }
}

/**
 * Adds a new comment to an item.
 *
 * @param PDO $pdo The PDO database connection object.
 */
function addComment(PDO $pdo) {
    // Expecting POST data from frontend
    $data = json_decode(file_get_contents('php://input'), true);
    $item_id = $data['item_id'] ?? null;
    $comment_text = $data['comment'] ?? '';
    $user_name = $data['user_name'] ?? 'Anonymous'; // Default to anonymous

    if (!$item_id || !is_numeric($item_id) || empty($comment_text)) {
        echo json_encode(['success' => false, 'message' => 'Item ID and comment text are required.']);
        http_response_code(400);
        return;
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO comments (item_id, user_name, comment) VALUES (:item_id, :user_name, :comment)"
        );
        $stmt->execute([
            ':item_id' => $item_id,
            ':user_name' => $user_name,
            ':comment' => $comment_text
        ]);

        echo json_encode(['success' => true, 'message' => 'Comment added successfully.', 'id' => $pdo->lastInsertId()]);
    } catch (\PDOException $e) {
        error_log("Error adding comment: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error adding comment.', 'error' => $e->getMessage()]);
        http_response_code(500);
    }
}