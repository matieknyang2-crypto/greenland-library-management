<?php
session_start();
require_once 'config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login_simple.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Get dashboard stats
try {
    // Total books
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM books");
    $total_books = $stmt->fetch()['total'];

    // Available books
    $stmt = $pdo->query("SELECT SUM(available_copies) as available FROM books");
    $available_books = $stmt->fetch()['available'];

    // Active borrowings
    $stmt = $pdo->query("SELECT COUNT(*) as active FROM borrowings WHERE status = 'borrowed'");
    $active_borrowings = $stmt->fetch()['active'];

    // Overdue
    $stmt = $pdo->query("SELECT COUNT(*) as overdue FROM borrowings 
                        WHERE status = 'borrowed' AND due_date < CURDATE()");
    $overdue = $stmt->fetch()['overdue'];

    // Recent activity
    $stmt = $pdo->query("SELECT b.title, b.author, br.borrow_date, br.due_date, br.status, u.username
                        FROM borrowings br
                        JOIN books b ON br.book_id = b.id
                        JOIN users u ON br.user_id = u.id
                        ORDER BY br.borrow_date DESC LIMIT 5");
    $recent_activity = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = ['total_books' => 0, 'available_books' => 0, 'active_borrowings' => 0, 'overdue' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - LibrarySys</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Modern dashboard styles - using the same design system as ui-showcase */
        :root {
            --primary: #667eea; --primary-dark: #5a67d8;
            --success: #48bb78; --danger: #f56565;
            --warning: #ed8936; --dark: #2d3748;
            --light: #f7fafc; --gray-100: #f7fafc;
            --gray-200: #edf2f7; --gray-300: #e2e8f0;
            --gray-600: #4a5568; --gray-800: #2d3748;
            --shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0,0,0,0.1);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Inter', -apple-system, sans-serif;
            background: var(--gray-100);
            color: var(--gray-800);
        }
        .navbar {
            background: white;
            padding: 1rem 2rem;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .nav-brand h2 { 
            background: linear-gradient(135deg, var(--primary), #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .user-info { display: flex; align-items: center; gap: 1rem; }
        .user-info i { color: var(--primary); }
        .container { max-width: 1400px; margin: 2rem auto; padding: 0 2rem; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-4px); }
        .stat-number { 
            font-size: 3rem; 
            font-weight: 800; 
            margin-bottom: 0.5rem;
        }
        .stat-card.total .stat-number { color: var(--primary); }
        .stat-card.available .stat-number { color: var(--success); }
        .stat-card.active .stat-number { color: var(--warning); }
        .stat-card.overdue .stat-number { color: var(--danger); }
        .stat-label { color: var(--gray-600); font-weight: 500; font-size: 1.1rem; }
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
        }
        .card h3 { margin-bottom: 1rem; color: var(--gray-800); }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            margin: 0.5rem 0.25rem 0 0;
            transition: all 0.3s ease;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .table-container { background: white; border-radius: 16px; padding: 2rem; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid var(--gray-200); }
        th { background: var(--gray-100); font-weight: 600; }
        .status { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status.on-time { background: var(--success); color: white; }
        .status.overdue { background: var(--danger); color: white; }
        @media (max-width: 768px) {
            .container { padding: 0 1rem; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-brand">
            <h2><i class="fas fa-book"></i> LibrarySys Dashboard</h2>
        </div>
        <div class="user-info">
            <i class="fas fa-user"></i>
            <span>Hi, <?php echo htmlspecialchars($username); ?> (<?php echo ucfirst($role); ?>)</span>
            <a href="logout.php" class="btn btn-primary" style="padding: 8px 16px; font-size: 0.9rem;">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_books; ?></div>
                <div class="stat-label">Total Books</div>
            </div>
            <div class="stat-card available">
                <div class="stat-number"><?php echo $available_books; ?></div>
                <div class="stat-label">Available</div>
            </div>
            <div class="stat-card active">
                <div class="stat-number"><?php echo $active_borrowings; ?></div>
                <div class="stat-label">Active Loans</div>
            </div>
            <div class="stat-card overdue">
                <div class="stat-number"><?php echo $overdue; ?></div>
                <div class="stat-label">Overdue</div>
            </div>
        </div>

        <div class="action-grid">
            <div class="card">
                <h3><i class="fas fa-plus-circle"></i> Quick Actions</h3>
                <a href="books.php" class="btn btn-primary"><i class="fas fa-book"></i> Manage Books</a>
                <a href="#" class="btn btn-primary"><i class="fas fa-exchange-alt"></i> Circulation</a>
                <a href="#" class="btn btn-primary"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="ui-showcase.html" class="btn btn-primary" target="_blank"><i class="fas fa-eye"></i> UI Demo</a>
            </div>
            
            <div class="card">
                <h3><i class="fas fa-clock"></i> System Info</h3>
                <p><strong>Logged in:</strong> <?php echo date('M j, Y g:i A', $_SESSION['login_time']); ?></p>
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Database:</strong> Connected ✅</p>
                <p><strong>Role:</strong> <span style="color: var(--primary);"><?php echo ucfirst($role); ?></span></p>
            </div>
        </div>

        <div class="table-container">
            <h3><i class="fas fa-history"></i> Recent Activity</h3>
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Author</th>
                        <th>Borrower</th>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_activity as $activity): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($activity['title']); ?></strong></td>
                        <td><?php echo htmlspecialchars($activity['author']); ?></td>
                        <td><?php echo htmlspecialchars($activity['username']); ?></td>
                        <td><?php echo date('M j', strtotime($activity['borrow_date'])); ?></td>
                        <td>
                            <?php 
                            $due = new DateTime($activity['due_date']);
                            $today = new DateTime();
                            if ($due < $today && $activity['status'] == 'borrowed') {
                                echo '<span style="color: var(--danger); font-weight: 600;">' . date('M j', strtotime($activity['due_date'])) . '</span>';
                            } else {
                                echo date('M j', strtotime($activity['due_date']));
                            }
                            ?>
                        </td>
                        <td>
                            <span class="status <?php echo $activity['status'] == 'borrowed' && $due < $today ? 'overdue' : 'on-time'; ?>">
                                <?php echo ucfirst($activity['status']); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($recent_activity)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 3rem; color: var(--gray-600);">No recent activity</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
