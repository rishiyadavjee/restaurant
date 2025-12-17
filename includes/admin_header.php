<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 1rem;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background: #495057;
        }
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar p-3" style="width: 250px;">
            <a href="index.php" class="d-flex align-items-center mb-3 text-white text-decoration-none">
                <i class="fas fa-utensils me-2 fs-4"></i>
                <span class="fs-4 fw-bold">Admin Panel</span>
            </a>
            <hr class="text-white">
            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li>
                    <a href="manage_menu.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_menu.php' ? 'active' : ''; ?>">
                        <i class="fas fa-utensils me-2"></i>Menu Items
                    </a>
                </li>
                <li>
                    <a href="manage_categories.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : ''; ?>">
                        <i class="fas fa-list me-2"></i>Categories
                    </a>
                </li>
                <li>
                    <a href="manage_bookings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_bookings.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar me-2"></i>Bookings
                    </a>
                </li>
                <li>
                    <a href="manage_slider.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_slider.php' ? 'active' : ''; ?>">
                        <i class="fas fa-images me-2"></i>Slider
                    </a>
                </li>
                <li>
                    <a href="manage_contacts.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_contacts.php' ? 'active' : ''; ?>">
                        <i class="fas fa-envelope me-2"></i>Messages
                    </a>
                </li>
                <li>
                    <a href="../index.php" class="nav-link">
                        <i class="fas fa-home me-2"></i>View Website
                    </a>
                </li>
                <li>
                    <a href="../logout.php" class="nav-link">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1 p-4" style="background: #f8f9fc;">
            <nav class="navbar navbar-light bg-white shadow-sm mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand mb-0 h1">
                        Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </span>
                </div>
            </nav>