<?php
require_once '../../config.php';
require_admin();

$page_title = 'Add New Post';
include '../includes/header.php';
include '../includes/sidebar.php';

$title = '';
$content = '';
$image_url = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = trim($_POST['image_url']);
    
    if (empty($title) || empty($content)) {
        $error = "Title and Content are required.";
    } else {
        // Generate Slug
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        
        // Check slug uniqueness
        $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$slug]);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, image_url, author_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $image_url, $_SESSION['user_id']]);
            echo "<script>window.location.href='index.php';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h3 mb-0 text-gray-800">Add New Post</h2>
        <a href="index.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Post Title</label>
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($title); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($image_url); ?>" placeholder="https://example.com/image.jpg">
                            <div class="form-text">Paste a direct link to a featured image.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" class="form-control" rows="10" required><?php echo htmlspecialchars($content); ?></textarea>
                            <div class="form-text">You can use simple text. HTML is stored as plain text currently.</div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Publish Post
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small text-muted">
                        <li>Title should be catchy and descriptive.</li>
                        <li>Slug will be auto-generated from the title.</li>
                        <li>Use high-quality images for better engagement.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
