<?php
require_once 'db.php'; // Include your database connection

$items = [];
try {
    $stmt = $pdo->query("SELECT id, title, description, image, status, created_at FROM items ORDER BY created_at DESC");
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Index page DB error: " . $e->getMessage());
    $items = []; // Ensure $items is an array even on error
}

// Handle messages from redirection (e.g., after item submission)
$message = $_GET['msg'] ?? '';
$message_type = $_GET['type'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>College Lost & Found</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="index-page"> <!-- Added body class for JS -->
    <div class="container">
        <header class="app-header">
            <h1><i class="fas fa-search-dollar"></i> College Lost & Found</h1>
            <a href="report.php" class="btn btn-primary report-button">Report Item <i class="fas fa-plus-circle"></i></a>
        </header>

        <?php if (!empty($message)): ?>
            <div class="notification <?php echo $message_type; ?>" style="display: block; position: static; margin-bottom: 20px;">
                <i class="fas <?php echo ($message_type === 'success' ? 'fa-check-circle' : 'fa-times-circle'); ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <section class="filter-section">
            <div class="search-bar">
                <input type="text" id="search-input" placeholder="Search by title or description...">
                <i class="fas fa-search"></i>
            </div>
            <!-- The category filter is not handled by JS in this minimalist setup,
                 it would require a full page refresh or AJAX to filter server-side.
                 Keeping it for UI consistency, but its JS logic is now removed. -->
            <div class="filter-options">
                <div class="custom-select-wrapper">
                    <select id="category-filter">
                        <option value="">All Categories</option>
                        <option value="lost">Lost</option>
                        <option value="found">Found</option>
                    </select>
                    <span class="custom-select-arrow"><i class="fas fa-chevron-down"></i></span>
                </div>
            </div>
        </section>

        <section class="recently-reported">
            <h2>Recently Reported Items</h2>
            <div id="items-container" class="item-grid">
                <?php if (empty($items)): ?>
                    <p id="no-items-message" class="no-items-message" style="display: block;">No items reported yet. Be the first!</p>
                <?php else: ?>
                    <p id="no-items-message" class="no-items-message" style="display: none;">No items found matching your criteria.</p>
                    <?php foreach ($items as $item): ?>
                        <?php
                         $imageUrl = !empty($item['image']) && file_exists('uploads/' . $item['image'])
    ? 'uploads/' . htmlspecialchars($item['image'])
    : 'assets/no-image.png';
                            $statusClass = htmlspecialchars($item['status']);
                        ?>
                        <div class="item-card">
                            <a href="item_detail.php?id=<?php echo htmlspecialchars($item['id']); ?>" style="text-decoration: none; color: inherit;">
                                <img src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                <div class="item-card-content">
                                    <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                                    <p><?php echo htmlspecialchars(mb_strimwidth($item['description'], 0, 100, "...")); ?></p>
                                    <span class="category <?php echo $statusClass; ?>"><?php echo ucfirst($statusClass); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <!-- No notification-container, as messages are embedded in PHP now -->
    <script src="script.js"></script>
</body>
</html>