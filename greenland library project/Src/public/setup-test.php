<?php
// Database setup and system test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🧪 LibrarySys Setup & Test</h1>";
echo "<pre>";

// 1. Create config.php if not exists
if (!file_exists('config.php')) {
    $config = '<?php
$pdo = new PDO("mysql:host=localhost;dbname=library_sys", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec("SET NAMES utf8mb4");
';
    file_put_contents('config.php', $config);
    echo "✅ Created config.php\n";
} else {
    echo "✅ config.php exists\n";
    require_once 'config.php';
}

// 2. Create database and tables
try {
    $pdo = new PDO("mysql:host=localhost;dbname=library_sys", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Database doesn't exist, create it
    $pdo = new PDO("mysql:host=localhost", "root", "");
    $pdo->exec("CREATE DATABASE IF NOT EXISTS library_sys CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo = new PDO("mysql:host=localhost;dbname=library_sys", "root", "");
    echo "✅ Created database library_sys\n";
}

$tables = [
    'users' => "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'librarian') DEFAULT 'librarian',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    'books' => "CREATE TABLE IF NOT EXISTS books (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        isbn VARCHAR(20) UNIQUE,
        category VARCHAR(100),
        total_copies INT DEFAULT 1,
        available_copies INT DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'borrowings' => "CREATE TABLE IF NOT EXISTS borrowings (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        book_id INT,
        borrow_date DATE NOT NULL,
        due_date DATE NOT NULL,
        return_date DATE NULL,
        status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (book_id) REFERENCES books(id),
        INDEX idx_book (book_id),
        INDEX idx_user (user_id)
    )"
];

foreach ($tables as $table => $sql) {
    $pdo->exec($sql);
    echo "✅ Created table: $table\n";
}

// 3. Insert test data
$adminHash = password_hash('admin123', PASSWORD_DEFAULT);
$pdo->exec("INSERT IGNORE INTO users (username, password, role) VALUES 
    ('admin', '$adminHash', 'admin'),
    ('librarian', '" . password_hash('lib123', PASSWORD_DEFAULT) . "', 'librarian')");
echo "✅ Inserted test users\n";

$books = [
    ["The Great Gatsby", "F. Scott Fitzgerald", "978-0743273565", "Fiction", 5, 3],
    ["To Kill a Mockingbird", "Harper Lee", "978-0061120084", "Fiction", 3, 2],
    ["1984", "George Orwell", "978-0451524935", "Dystopian", 4, 1],
    ["Clean Code", "Robert C. Martin", "978-0132350884", "Programming", 2, 2]
];

foreach ($books as $book) {
    $stmt = $pdo->prepare("INSERT IGNORE INTO books (title, author, isbn, category, total_copies, available_copies) 
                          VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute($book);
}
echo "✅ Inserted test books\n";

echo "</pre><h2>🎉 Setup Complete!</h2>";
echo "<p><strong>Credentials:</strong><br>";
echo "Admin: <code>admin</code> / <code>admin123</code><br>";
echo "Librarian: <code>librarian</code> / <code>lib123</code></p>";
echo '<a href="login_simple.php" style="display: inline-block; padding: 12px 24px; background: #28a745; color: white; text-decoration: none; border-radius: 6px;">→ Go to Login</a>';
?>
