<?php
include 'db.php';

// Example query to fetch all companies
$sql = "SELECT * FROM companies";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/app.css">
    <title>Senbee Codetest 3 - Company Manager</title>
</head>
<body>

    <!-- Display List of Companies -->
    <h2>Company List</h2>
    <?php if ($result->num_rows > 0): ?>
        <ul>
            <?php while ($row = $result->fetch_assoc()): ?>
                <li>CVR: <?= $row['cvr_number'] ?> - Name: <?= $row['name'] ?></li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No records found</p>
    <?php endif; ?>

    <!-- Form to Add a New Company -->
    <form method="POST" action="index.php">
        <label for="cvr_number">CVR Number:</label>
        <input type="text" id="cvr_number" name="cvr_number" required>
        
        <label for="name">Name:</label>
        <input type="text" id="name" name="name">
        
        <label for="phone">Phone:</label>
        <input type="text" id="phone" name="phone">
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email">
        
        <label for="address">Address:</label>
        <input type="text" id="address" name="address">
        
        <button type="submit" name="add_company">Add Company</button>
    </form>

    <script src="/js/app.js"></script>
    
</body>
</html>

<?php
// Insert new company if form is submitted
if (isset($_POST['add_company'])) {
    $cvr_number = $_POST['cvr_number'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    $sql = "INSERT INTO companies (cvr_number, name, phone, email, address) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $cvr_number, $name, $phone, $email, $address);
    $stmt->execute();
    $stmt->close();

    // Redirect to the same page to refresh the company list
    header("Location: index.php");
    exit();
}
?>
