<!-- Sidebar -->
<nav class="sidebar">
    <div class="sidebar-brand">
        <i class="bi bi-lightning-charge-fill me-2"></i>
        <h4>RoboMart</h4>
    </div>
    
    <div class="sidebar-nav">
        <div class="nav-section-title">Main Menu</div>
        
        <a href="<?php echo SITE_URL; ?>/backend/index.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' && !strpos($_SERVER['PHP_SELF'], 'users') ? 'active' : ''; ?>">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>
        
        <div class="nav-section-title">Management</div>
        
        <a href="<?php echo SITE_URL; ?>/backend/users/index.php" 
           class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'users') !== false ? 'active' : ''; ?>">
            <i class="bi bi-people"></i>
            User Management
        </a>
        
        <div class="nav-section-title">Store</div>
        
        <a href="<?php echo SITE_URL; ?>/backend/products/index.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'products') !== false ? 'active' : ''; ?>">
            <i class="bi bi-box-seam"></i>
            Products
        </a>
        
        <a href="<?php echo SITE_URL; ?>/backend/categories/index.php" class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'categories') !== false ? 'active' : ''; ?>">
            <i class="bi bi-tags"></i>
            Categories
        </a>

        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/orders/') !== false ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/backend/orders/index.php">
            <i class="bi bi-cart"></i>
            Orders
        </a>
        
        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/coupons/') !== false ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/backend/coupons/index.php">
            <i class="bi bi-ticket-perforated"></i>
            Coupons
        </a>
        
        <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], '/blog/') !== false ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/backend/blog/index.php">
            <i class="bi bi-journal-text"></i>
            Blog Management
        </a>
        
        <div class="nav-section-title">Other</div>
        
        <a href="<?php echo SITE_URL; ?>/index.php" class="nav-link">
            <i class="bi bi-house"></i>
            View Store
        </a>
        
        <a href="<?php echo SITE_URL; ?>/logout.php" class="nav-link text-danger">
            <i class="bi bi-box-arrow-right"></i>
            Logout
        </a>
    </div>
</nav>

<!-- Main Content Wrapper -->
<div class="main-content">
    <!-- Top Header -->
    <header class="top-header">
        <div class="d-flex align-items-center">
            <button class="btn btn-link text-white d-lg-none me-3" id="sidebarToggle">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h5 class="mb-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h5>
        </div>
        
        <div class="d-flex align-items-center gap-3">
            <div class="dropdown">
                <button class="btn btn-link text-white dropdown-toggle d-flex align-items-center gap-2" 
                        type="button" data-bs-toggle="dropdown">
                    <div class="avatar">
                        <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                    </div>
                    <span class="d-none d-md-inline"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/account.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?php echo SITE_URL; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Content -->
    <div class="content-wrapper">
