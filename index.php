<?php
/**
 * Main entry point for the Company Manager application
 * 
 * This file serves as the main interface for the application,
 * combining the backend functionality with the frontend presentation.
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define root path
define('ROOT_PATH', __DIR__);

// Include required files
require_once ROOT_PATH . '/app/config.php';

// Initialize page variables
$pageTitle = "Company Manager";
$dbError = null;

// Initialize database connection
try {
    $db = Database::getInstance()->getConnection();
    if ($db) {
        $dbConnected = true;
    }
} catch (Exception $e) {
    $dbError = "Database connection failed: " . $e->getMessage();
    $dbConnected = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- CSS inclusion with cache-busting -->
    <?php
    $cssFile = 'assets/css/styles.css';
    if (file_exists($cssFile)) {
        printf(
            '<link rel="stylesheet" href="%s?v=%s">',
            $cssFile,
            filemtime($cssFile)
        );
    }
    ?>
</head>
<body>
    <div class="app-container">
        <!-- Header Section -->
        <header class="header">
            <h1><?php echo htmlspecialchars($pageTitle); ?></h1>
            <p class="subtitle">Manage and sync company information</p>
            <?php if (defined('DEVELOPMENT') && DEVELOPMENT): ?>
                <small class="development-badge">Development Mode</small>
            <?php endif; ?>
        </header>

        <!-- Database Error Notice -->
        <?php if ($dbError): ?>
            <div class="error-message" role="alert">
                <?php echo htmlspecialchars($dbError); ?>
            </div>
        <?php endif; ?>

        <!-- Input Section -->
        <section class="input-section">
            <div class="card">
                <h2>Add New Company</h2>
                <div class="input-group">
                    <input 
                        type="text" 
                        id="cvrInput" 
                        placeholder="Enter 8-digit CVR Number"
                        maxlength="8"
                        pattern="\d{8}"
                        aria-label="CVR Number Input"
                        <?php if (!$dbConnected) echo 'disabled'; ?>
                    >
                    <button 
                        id="addButton" 
                        class="primary-button"
                        aria-label="Add Company"
                        <?php if (!$dbConnected) echo 'disabled'; ?>
                    >
                        Add Company
                    </button>
                </div>
                <div id="messageArea" class="message-area" role="alert" aria-live="polite"></div>
            </div>
        </section>

        <!-- Companies List Section -->
        <section class="companies-section">
            <!-- Loading State -->
            <div id="loadingState" class="loading-state hidden" role="status">
                Loading companies...
            </div>

            <!-- Companies List -->
            <div id="companiesList" class="companies-grid">
                <?php
                if ($dbConnected) {
                    try {
                        $stmt = $db->query('SELECT * FROM companies ORDER BY created_at DESC');
                        $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (empty($companies)) {
                            echo '<div id="emptyState" class="empty-state">No companies found. Add one above!</div>';
                        }
                    } catch (Exception $e) {
                        echo '<div class="error-message" role="alert">Error loading companies: ' . 
                             htmlspecialchars($e->getMessage()) . '</div>';
                    }
                }
                ?>
            </div>
        </section>
    </div>

    <!-- Company Card Template -->
    <template id="companyCardTemplate">
        <div class="company-card">
            <div class="company-header">
                <h3 class="company-name"></h3>
                <span class="cvr-number"></span>
            </div>
            <div class="company-details">
                <p class="company-address"></p>
                <p class="company-contact">
                    <span class="company-phone"></span>
                    <span class="company-email"></span>
                </p>
            </div>
            <div class="company-actions">
                <button class="sync-button" aria-label="Sync Company">Sync</button>
                <button class="delete-button" aria-label="Delete Company">Delete</button>
            </div>
        </div>
    </template>
    <!-- JavaScript inclusion with cache-busting -->
    <script src="assets/js/main.js"></script>
</body>
</html>