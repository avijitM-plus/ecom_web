<?php
require_once '../../config.php';
require_admin();

$page_title = 'Add New Post';
include '../includes/header.php';
include '../includes/sidebar.php';

$title = '';
$slug = '';
$content = '';
$image_url = '';
$status = 'published';
$category = '';
$excerpt = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $image_url = trim($_POST['image_url']);
    $status = trim($_POST['status']);
    $category = trim($_POST['category']);
    $excerpt = trim($_POST['excerpt']);
    
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
            $stmt = $pdo->prepare("INSERT INTO blog_posts (title, slug, content, image_url, status, category, excerpt, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$title, $slug, $content, $image_url, $status, $category, $excerpt, $_SESSION['user_id']]);
            echo "<script>window.location.href='index.php';</script>";
            exit;
        } catch (PDOException $e) {
            $error = "Database Error: " . $e->getMessage();
        }
    }
}
?>

<!-- TinyMCE -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#content',
        height: 500,
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount checklist mediaembed casechange export formatpainter pageembed linkchecker a11ychecker tinymcespellchecker permanentpen powerpaste advtable advcode editimage tinycomments tableofcontents footnotes mergetags autocorrect typography inlinecss',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
        tinycomments_mode: 'embedded',
        tinycomments_author: 'Author name',
        mergetags_list: [
            { value: 'First.Name', title: 'First Name' },
            { value: 'Email', title: 'Email' },
        ],
    });
</script>

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
                            <label class="form-label">Excerpt (Summary)</label>
                            <textarea name="excerpt" class="form-control" rows="3"><?php echo htmlspecialchars($excerpt); ?></textarea>
                            <div class="form-text">A short summary displayed on the blog listing page.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" id="content" class="form-control" rows="10"><?php echo htmlspecialchars($content); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($image_url); ?>" placeholder="https://example.com/image.jpg">
                            <div class="form-text">Paste a direct link to a featured image.</div>
                        </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Publish Options</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="published" <?php echo $status === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo $status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($category); ?>" placeholder="e.g. Technology, AI">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg"></i> Publish Post
                    </button>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tips</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small text-muted">
                        <li>Title should be catchy and descriptive.</li>
                        <li>Use high-quality images.</li>
                        <li>Categories help organize your content.</li>
                        <li>Excerpts improve SEO and user experience.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
