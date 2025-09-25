<?php
// Include the database connection
require_once 'db.php';

$itemId = $_GET['id'] ?? null;
$item = null;
$comments = [];
$errorMessage = '';
$successMessage = '';

// --- Handle Item Deletion (for Delete and Resolve actions) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_item']) && isset($_POST['item_id_to_delete'])) {
        $idToDelete = $_POST['item_id_to_delete'];
        try {
            // Get image filename before deleting item from DB
            $stmt = $pdo->prepare("SELECT image FROM items WHERE id = :id");
            $stmt->execute([':id' => $idToDelete]);
            $itemToDelete = $stmt->fetch();
            $imageToDelete = $itemToDelete['image'] ?? null;

            $sql = "DELETE FROM items WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $idToDelete]);

            // If item had an image, delete it from the uploads folder
            if ($imageToDelete && file_exists('uploads/' . $imageToDelete)) {
                unlink('uploads/' . $imageToDelete);
            }

            header('Location: index.php?message=Item deleted successfully!');
            exit();
        } catch (PDOException $e) {
            error_log("Database error on item deletion: " . $e->getMessage());
            $errorMessage = "Error deleting item. Please try again.";
        }
    }
}


// --- Fetch Item Details and Comments ---
if ($itemId) {
    try {
        // Fetch item details
        $stmt = $pdo->prepare("SELECT id, title, description, image, status, created_at FROM items WHERE id = :id");
        $stmt->execute([':id' => $itemId]);
        $item = $stmt->fetch();

        if ($item) {
            // Fetch comments for the item
            $stmt = $pdo->prepare("SELECT user_name, comment, created_at FROM comments WHERE item_id = :item_id ORDER BY created_at ASC");
            $stmt->execute([':item_id' => $itemId]);
            $comments = $stmt->fetchAll();
        } else {
            // Item not found, set error and redirect to index after a short delay
            $errorMessage = "Item not found.";
            header('Refresh: 3; URL=index.php'); // Redirect after 3 seconds
        }
    } catch (PDOException $e) {
        error_log("Database error fetching item details or comments: " . $e->getMessage());
        $errorMessage = "Error loading item details. Please try again later.";
        header('Refresh: 3; URL=index.php'); // Redirect after 3 seconds
    }
} else {
    $errorMessage = "No item ID provided.";
    header('Refresh: 3; URL=index.php'); // Redirect after 3 seconds
}

// --- Handle Comment Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    // Only process if item was successfully found to begin with
    if ($item) {
        $commentText = trim($_POST['comment_text'] ?? '');
        // Optional: you can add a field for user name in the form if needed
        $userName = trim($_POST['user_name'] ?? 'Anonymous');

        if (empty($commentText)) {
            $errorMessage = "Comment cannot be empty.";
        } else {
            try {
                $sql = "INSERT INTO comments (item_id, user_name, comment) VALUES (:item_id, :user_name, :comment)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':item_id' => $item['id'], // Use the ID of the fetched item
                    ':user_name' => $userName,
                    ':comment' => $commentText
                ]);
                $successMessage = "Comment added successfully!";
                // Redirect to self to prevent form re-submission and clear POST data
                header('Location: item_detail.php?id=' . htmlspecialchars($item['id']) . '&message=' . urlencode($successMessage) . '#comments-section');
                exit();
            } catch (PDOException $e) {
                error_log("Database error adding comment: " . $e->getMessage());
                $errorMessage = "Error adding comment. Please try again.";
            }
        }
    } else {
         $errorMessage = "Cannot add comment: Item not found.";
    }
}

// Display messages from URL if present (e.g., after adding comment)
if (isset($_GET['message'])) {
    $successMessage = $_GET['message'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Item Details - College Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="detail-page">
    <div class="container">
        <header class="app-header-small">
            <h1 id="detail-page-title"><i class="fas fa-info-circle"></i> Item Details</h1>
            <a href="index.php" class="btn btn-secondary back-button"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </header>

        <?php if (!empty($errorMessage)): ?>
            <p id="item-not-found" class="no-items-message alert-error"><?php echo htmlspecialchars($errorMessage); ?></p>
        <?php elseif (!empty($successMessage)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
        <?php endif; ?>

        <?php if ($item): ?>
            <section id="item-details" class="item-detail-card">
                <div class="detail-header">
                    <h2 id="detail-title"><?php echo htmlspecialchars($item['title']); ?></h2>
                    <span id="detail-category" class="category <?php echo htmlspecialchars($item['status']); ?>"><?php echo htmlspecialchars($item['status']); ?></span>
                </div>
                <div class="detail-content">
                    <div class="detail-image">
                        <img id="detail-image-src" src="<?php echo $item['image'] ? 'uploads/' . htmlspecialchars($item['image']) : 'https://via.placeholder.com/600x450?text=No+Image+Available'; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    </div>
                    <div class="detail-info">
                        <p class="detail-description" id="detail-description"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        <p class="detail-meta">Reported on: <span id="detail-timestamp"><?php echo (new DateTime($item['created_at']))->format('F j, Y, g:i a'); ?></span></p>
                        <div class="detail-actions">
                            <!-- Edit Item Button -->
                            <a href="report.php?id=<?php echo htmlspecialchars($item['id']); ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit Item</a>

                            <!-- Delete Item Form/Button -->
                            <form method="POST" action="item_detail.php" onsubmit="return confirm('Are you sure you want to delete this item? This cannot be undone.');">
                                <input type="hidden" name="item_id_to_delete" value="<?php echo htmlspecialchars($item['id']); ?>">
                                <button type="submit" name="delete_item" class="btn btn-delete"><i class="fas fa-trash-alt"></i> Delete Item</button>
                            </form>

                            <!-- Mark as Resolved Button (only for 'found' items) -->
                            <?php if ($item['status'] === 'found'): ?>
                                <form method="POST" action="item_detail.php" onsubmit="return confirm('Are you sure you want to mark this item as resolved and remove it?');">
                                    <input type="hidden" name="item_id_to_delete" value="<?php echo htmlspecialchars($item['id']); ?>">
                                    <button type="submit" name="delete_item" class="btn btn-resolve"><i class="fas fa-check-circle"></i> Mark as Resolved</button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <div class="contact-info">
                            <h3><i class="fas fa-headset"></i> Contact Reporter</h3>
                            <p>If you have information about this item, please contact the original reporter.</p>
                            <button class="btn btn-contact"><i class="fas fa-envelope"></i> Send Message (Placeholder)</button>
                        </div>
                    </div>
                </div>
            </section>

            <div class="comments-section" id="comments-section">
                <h3><i class="fas fa-comments"></i> Comments</h3>
                <div id="comments-list">
                    <?php if (count($comments) > 0): ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-card">
                                <p><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                                <p class="comment-meta">By <?php echo htmlspecialchars($comment['user_name']); ?> on <?php echo (new DateTime($comment['created_at']))->format('F j, Y, g:i a'); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="font-style: italic; color: #7f8c8d; text-align: center;">No comments yet. Be the first!</p>
                    <?php endif; ?>
                </div>

                <div class="comment-form-container">
                    <h4>Add a Comment</h4>
                    <form method="POST" action="item_detail.php?id=<?php echo htmlspecialchars($item['id']); ?>">
                        <div class="form-group">
                            <label for="user_name">Your Name (Optional)</label>
                            <input type="text" id="user_name" name="user_name" placeholder="Anonymous" value="<?php echo htmlspecialchars($_POST['user_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="comment_text">Your Comment</label>
                            <textarea id="comment_text" name="comment_text" rows="4" placeholder="Type your comment here..." required><?php echo htmlspecialchars($_POST['comment_text'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="submit_comment" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Post Comment</button>
                    </form>
                </div>
            </div>

        <?php else: // This block executes if $item is null (item not found or no ID) ?>
            <!-- Message already handled by $errorMessage alert at the top -->
        <?php endif; ?>
    </div>
    <div id="notification-container" class="notification-container"></div>
    <script src="script.js"></script>
</body>
</html>
