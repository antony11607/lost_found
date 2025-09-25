<?php
require_once 'db.php'; // Include your database connection

$message = '';
$message_type = '';

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? ''; // 'category' in frontend form, 'status' in DB
    $imageFileName = null; // Default to no image

    // Server-side validation (always good to double-check)
    if (empty($title) || empty($description) || empty($status)) {
        $message = "Title, description, and status are required fields.";
        $message_type = "error";
    } elseif (!in_array($status, ['lost', 'found'])) {
        $message = "Invalid status value provided.";
        $message_type = "error";
    } else {
        // Handle image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            // Create uploads directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $imageTmpName = $_FILES['image']['tmp_name'];
            $imageExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array(strtolower($imageExtension), $allowedExtensions)) {
                $message = "Only JPG, JPEG, PNG, and GIF image formats are allowed.";
                $message_type = "error";
            } else {
                $imageFileName = uniqid() . '.' . $imageExtension; // Generate unique filename
                $uploadPath = $uploadDir . $imageFileName;

                if (!move_uploaded_file($imageTmpName, $uploadPath)) {
                    $message = "Failed to upload image. Please try again.";
                    $message_type = "error";
                    $imageFileName = null; // Ensure no invalid filename is saved
                }
            }
        }

        // If no errors so far, proceed to database insertion
        if (empty($message)) {
            try {
                $sql = "INSERT INTO items (title, description, image, status) VALUES (:title, :description, :image, :status)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':title' => $title,
                    ':description' => $description,
                    ':image' => $imageFileName,
                    ':status' => $status
                ]);

                // Redirect to homepage on successful submission
                header("Location: index.php?msg=Item added successfully!&type=success");
                exit();

            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
                $message_type = "error";
                 error_log("Report item DB error: " . $e->getMessage());
                 // Clean up uploaded image if DB insertion failed
                 if ($imageFileName && file_exists($uploadPath)) {
                     unlink($uploadPath);
                 }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Lost or Found Item</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="report-page"> <!-- Added body class for JS -->
    <div class="container">
        <header class="app-header-small">
            <h1 id="report-page-title"><i class="fas fa-file-invoice"></i> Report Item</h1>
            <a href="index.php" class="btn btn-secondary back-button"><i class="fas fa-arrow-left"></i> Back to Home</a>
        </header>

        <?php if (!empty($message)): ?>
            <div class="notification <?php echo $message_type; ?>" style="display: block; position: static; margin-bottom: 20px;">
                <i class="fas <?php echo ($message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle'); ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <form id="report-form" class="report-form-card" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Item Title</label>
                <input type="text" id="title" name="title" placeholder="e.g., Blue Water Bottle" required>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5" placeholder="Provide details like brand, color, location, etc." required></textarea>
            </div>

            <div class="form-group">
                <label for="category">Status</label> <!-- Changed label to Status for clarity -->
                <div class="custom-select-wrapper">
                    <select id="category" name="status" required> <!-- name='status' to match DB column -->
                        <option value="" disabled selected>Select Lost or Found</option>
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                    </select>
                    <span class="custom-select-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
            </div>

            <div class="form-group">
                <label for="image">Image (Optional)</label>
                <div class="image-upload-area">
                    <input type="file" id="image" name="image" accept="image/*">
                    <p><i class="fas fa-cloud-upload-alt"></i> Drag & Drop or Click to Upload Image</p>
                    <div id="image-preview" class="image-preview">
                        <img src="" alt="Image Preview" style="display: none;">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-submit"><i class="fas fa-paper-plane"></i> Submit Report</button>
        </form>
    </div>
    <!-- No notification-container here, as PHP handles the message and redirects -->
    <script src="script.js"></script>
</body>
</html>