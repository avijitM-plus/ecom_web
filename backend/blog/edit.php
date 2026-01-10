<?php
require_once '../../config.php';
require_admin();

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$id]);
$post = $stmt->fetch();

if (!$post) {
    echo "Post not found.";
    exit;
}

$page_title = 'Edit Post';
include '../includes/header.php';
include '../includes/sidebar.php';

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
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET title = ?, content = ?, image_url = ?, status = ?, category = ?, excerpt = ? WHERE id = ?");
            $stmt->execute([$title, $content, $image_url, $status, $category, $excerpt, $id]);
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
        <h2 class="h3 mb-0 text-gray-800">Edit Post</h2>
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
                            <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                        </div>

                         <div class="mb-3">
                            <label class="form-label">Slug (Read Only)</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($post['slug']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Excerpt (Summary)</label>
                            <textarea name="excerpt" class="form-control" rows="3"><?php echo htmlspecialchars($post['excerpt'] ?? ''); ?></textarea>
                            <div class="form-text">A short summary displayed on the blog listing page.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Content</label>
                            <textarea name="content" id="content" class="form-control" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image URL</label>
                            <input type="url" name="image_url" class="form-control" value="<?php echo htmlspecialchars($post['image_url']); ?>">
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
                            <option value="published" <?php echo ($post['status'] ?? 'published') === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo ($post['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($post['category'] ?? ''); ?>" placeholder="e.g. Technology, AI">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Update Post
                    </button>
                    
                     <hr class="my-4">
                    
                    <?php if ($post['image_url']): ?>
                        <div class="text-center">
                            <label class="form-label d-block text-start mb-2">Featured Image Preview</label>
                            <img src="<?php echo htmlspecialchars($post['image_url']); ?>" class="img-fluid rounded mb-3" alt="Preview">
                        </div>
                    <?php endif; ?>
                    
                    <a href="../../blog-details.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-sm btn-outline-info w-100">
                        View Live <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
