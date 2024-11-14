<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Manager</title>
    <!-- CSS style below -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="app-container">
        <!-- Header Section -->
        <header class="header">
            <h1>Company Manager</h1>
            <p class="subtitle">Manage and sync company information</p>
        </header>

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
                    >
                    <button id="addButton" class="primary-button">Add Company</button>
                </div>
                <!-- Error/Success Messages -->
                <div id="messageArea" class="message-area"></div>
            </div>
        </section>

        <!-- Companies List Section -->
        <section class="companies-section">
            <!-- Loading State -->
            <div id="loadingState" class="loading-state hidden">
                Loading companies...
            </div>

            <!-- Companies List -->
            <div id="companiesList" class="companies-grid">
                <!-- Companies will be loaded here -->
            </div>

            <!-- No Companies State -->
            <div id="emptyState" class="empty-state hidden">
                No companies found. Add one above!
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
                <button class="sync-button">Sync</button>
                <button class="delete-button">Delete</button>
            </div>
        </div>
    </template>
</body>
</html>