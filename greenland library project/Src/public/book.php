<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit;
}

// Handle form actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_book'])) {
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $isbn = trim($_POST['isbn']);
        $category = trim($_POST['category']);
        $copies = (int)$_POST['copies'];
        
        try {
            $stmt = $pdo->prepare("INSERT INTO books (title, author, isbn, category, total_copies, available_copies) 
                                 VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $author, $isbn, $category, $copies, $copies]);
            $message = 'Book added successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error adding book: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
    
    if (isset($_POST['delete_book'])) {
        $book_id = (int)$_POST['book_id'];
        try {
            $pdo->prepare("DELETE FROM books WHERE id = ?")->execute([$book_id]);
            $message = 'Book deleted successfully!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Error deleting book';
            $message_type = 'error';
        }
    }
}

// Get all books
$stmt = $pdo->query("SELECT * FROM books ORDER BY title");
$books = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Books - LibrarySys</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea; --success: #48bb78;
            --danger: #f56565; --warning: #ed8936;
            --dark: #2d3748; --gray-100: #f7fafc;
            --gray-200: #edf2f7; --gray-300: #e2e8f0;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--gray-100);
            color: var(--dark);
        }
        .navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand h1 { 
            background: linear-gradient(135deg, var(--primary), #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .message {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        .message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .form-section, .table-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .form-group { margin-bottom: 1.5rem; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--dark); }
        input, select { 
            width: 100%; padding: 12px 16px; 
            border: 2px solid var(--gray-200); 
            border-radius: 8px; 
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        input:focus, select:focus { outline: none; border-color: var(--primary); }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-right: 1rem;
            margin-bottom: 1rem;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
        .btn-danger { background: var(--danger); color: white; }
        .btn:hover { transform: translateY(-2px); }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--gray-200); }
        th { background: var(--gray-100); font-weight: 600; }
        .book-actions { white-space: nowrap; }
        .stock { font-weight: 600; color: var(--success); }
        .low-stock { color: var(--warning); }
        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .container { padding: 0 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h1><i class="fas fa-book"></i> Book Management</h1>
        </div>
        <div>
            <a href="dashboard.php" class="btn btn-primary">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <?php if ($message): ?>
        <div class="message <?php echo $message_type; ?>">
            <i class="fas <?php echo $message_type == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="form-section">
            <h2><i class="fas fa-plus-circle"></i> Add New Book</h2>
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Title *</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Author *</label>
                        <input type="text" name="author" required>
                    </div>
                    <div class="form-group">
                        <label>ISBN</label>
                        <input type="text" name="isbn">
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category">
                            <option value="">Select Category</option>
                            <option value="Fiction">Fiction</option>
                            <option value="Non-Fiction">Non-Fiction</option>
                            <option value="Science">Science</option>
                            <option value="Programming">Programming</option>
                            <option value="Mystery">Mystery</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Total Copies *</label>
                        <input type="number" name="copies" min="1" max="100" value="1" required>
                    </div>
                </div>
                <button type="submit" name="add_book" class="btn btn-success">
                    <i class="fas fa-save"></i> Add Book
                </button>
            </form>
        </div>

        <div class="table-section">
            <h2><i class="fas fa-list"></i> Book Inventory (<?php echo count($books); ?> books)</h2>
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>ISBN</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($book['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($book['author']); ?></td>
                            <td><?php echo htmlspecialchars($book['isbn'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($book['category'] ?? 'Uncategorized'); ?></td>
                            <td>
                                <span class="stock <?php echo $book['available_copies'] < 2 ? 'low-stock' : ''; ?>">
                                    <?php echo $book['available_copies']; ?> / <?php echo $book['total_copies']; ?>
                                </span>
                            </td>
                            <td class="book-actions">
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Delete this book?')">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="delete_book" class="btn btn-danger" 
                                            style="padding: 6px 12px; font-size: 0.8rem;">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($books)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--gray-600);">
                                <i class="fas fa-book" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.5;"></i>
                                <p>No books in inventory. Add your first book above!</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
