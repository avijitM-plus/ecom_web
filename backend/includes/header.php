<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>RoboMart Admin</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --header-height: 60px;
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
        }
        
        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            border-right: 1px solid rgba(255,255,255,0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }
        
        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            background: var(--primary-gradient);
        }
        
        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
            color: #fff;
        }
        
        .sidebar-nav {
            padding: 1rem 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: #94a3b8;
            border-radius: 0;
            transition: all 0.2s ease;
        }
        
        .nav-link:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }
        
        .nav-link.active {
            background: var(--primary-gradient);
            color: #fff;
        }
        
        .nav-link i {
            width: 24px;
            margin-right: 12px;
            font-size: 1.1rem;
        }
        
        .nav-section-title {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }
        
        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        /* Header */
        .top-header {
            height: var(--header-height);
            background: #1e293b;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        /* Cards */
        .stat-card {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.3);
        }
        
        .stat-card.gradient-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .stat-card.gradient-blue {
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        }
        
        .stat-card.gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .stat-card.gradient-orange {
            background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .stat-label {
            color: rgba(255,255,255,0.8);
            font-size: 0.875rem;
        }
        
        /* Tables */
        .table-container {
            background: #1e293b;
            border-radius: 16px;
            border: 1px solid rgba(255,255,255,0.1);
            overflow: hidden;
        }
        
        .table {
            margin: 0;
            color: #e2e8f0;
        }
        
        .table thead th {
            background: #334155;
            border-bottom: none;
            padding: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 1rem;
            border-color: rgba(255,255,255,0.05);
            vertical-align: middle;
        }
        
        .table tbody tr:hover {
            background: rgba(255,255,255,0.02);
        }
        
        /* Badges */
        .badge-admin {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .badge-user {
            background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
        }
        
        .badge-active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .badge-inactive {
            background: #475569;
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
        }
        
        /* Forms */
        .form-control, .form-select {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.1);
            color: #e2e8f0;
        }
        
        .form-control:focus, .form-select:focus {
            background: #1e293b;
            border-color: #667eea;
            color: #e2e8f0;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        /* Content wrapper */
        .content-wrapper {
            padding: 1.5rem;
        }
        
        /* Page header */
        .page-header {
            margin-bottom: 1.5rem;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
        }
        
        /* Avatar */
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--primary-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        /* Dropdown */
        .dropdown-menu {
            background: #1e293b;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        .dropdown-item {
            color: #e2e8f0;
        }
        
        .dropdown-item:hover {
            background: rgba(255,255,255,0.05);
            color: #fff;
        }
        
        /* Alert overrides */
        .alert {
            border: none;
            border-radius: 12px;
        }
    </style>
</head>
<body>
