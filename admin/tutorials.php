<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $id = (int) $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tutorials WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['flash_message'] = 'Tutorial deleted successfully!';
    $_SESSION['flash_type'] = 'success';
    header('Location: tutorials');
    exit;
}

// Handle PDF delete
if (isset($_GET['delete_pdf'])) {
    if (!verifyCSRFToken($_GET['csrf_token'] ?? '')) {
        die('Invalid security token');
    }
    $id = (int) $_GET['delete_pdf'];

    // Get PDF file path
    $stmt = $conn->prepare("SELECT pdf_file FROM tutorials WHERE id = ?");
    $stmt->execute([$id]);
    $tutorial = $stmt->fetch();

    if ($tutorial && $tutorial['pdf_file']) {
        // Delete physical file
        $pdf_path = __DIR__ . '/../' . $tutorial['pdf_file'];
        if (file_exists($pdf_path)) {
            unlink($pdf_path);
        }

        // Update database to remove PDF reference
        $stmt = $conn->prepare("UPDATE tutorials SET pdf_file = NULL WHERE id = ?");
        $stmt->execute([$id]);

        $_SESSION['flash_message'] = 'PDF deleted successfully!';
        $_SESSION['flash_type'] = 'success';
    }

    header('Location: tutorials');
    exit;
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $video_url = trim($_POST['video_url']);
    $category = trim($_POST['category']);
    $difficulty = trim($_POST['difficulty']);
    $content = $_POST['content'] ?? '';

    // Generate slug from title if not provided
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

    // Handle links (JSON array)
    $links = [];
    if (isset($_POST['link_title']) && is_array($_POST['link_title'])) {
        foreach ($_POST['link_title'] as $index => $link_title) {
            if (!empty($link_title) && !empty($_POST['link_url'][$index])) {
                $links[] = [
                    'title' => trim($link_title),
                    'url' => trim($_POST['link_url'][$index])
                ];
            }
        }
    }
    $links_json = json_encode($links);

    // Get current tutorial data for image handling
    $current_image = null;
    $current_pdf = null;
    if ($id) {
        $stmt = $conn->prepare("SELECT image, pdf_file, difficulty FROM tutorials WHERE id = ?");
        $stmt->execute([$id]);
        $current = $stmt->fetch();
        $current_image = $current['image'] ?? null;
        $current_pdf = $current['pdf_file'] ?? null;
    }

    // Handle image upload
    $image_path = $current_image;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/images/tutorials/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['image']['type'];

        if (in_array($file_type, $allowed_types)) {
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = 'tutorial_' . time() . '.' . $extension;
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                // Delete old image if exists
                if ($current_image && file_exists($upload_dir . basename($current_image))) {
                    unlink($upload_dir . basename($current_image));
                }
                $image_path = 'assets/images/tutorials/' . $filename;
            }
        }
    }

    // Handle PDF upload
    $pdf_path = $current_pdf;
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../assets/pdfs/tutorials/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        if ($_FILES['pdf_file']['type'] === 'application/pdf') {
            $filename = 'tutorial_' . time() . '_' . basename($_FILES['pdf_file']['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['pdf_file']['tmp_name'], $target_path)) {
                // Delete old PDF if exists
                if ($current_pdf && file_exists($upload_dir . basename($current_pdf))) {
                    unlink($upload_dir . basename($current_pdf));
                }
                $pdf_path = 'assets/pdfs/tutorials/' . $filename;
            }
        }
    }

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE tutorials SET title = ?, slug = ?, excerpt = ?, content = ?, video_url = ?, category = ?, difficulty = ?, image = ?, pdf_file = ?, images = ? WHERE id = ?");
        $stmt->execute([$title, $slug, $description, $content, $video_url, $category, $difficulty, $image_path, $pdf_path, $links_json, $id]);
        $_SESSION['flash_message'] = 'Tutorial updated successfully!';
        $_SESSION['flash_type'] = 'success';
    } else {
        // Insert
        $stmt = $conn->prepare("INSERT INTO tutorials (title, slug, excerpt, content, video_url, category, difficulty, image, pdf_file, images) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $slug, $description, $content, $video_url, $category, $difficulty, $image_path, $pdf_path, $links_json]);
        $_SESSION['flash_message'] = 'Tutorial added successfully!';
        $_SESSION['flash_type'] = 'success';
    }

    header('Location: tutorials');
    exit;
}

$page_title = 'Tutorials Management';
require_once 'header.php';

// Get tutorial for editing
$edit_tutorial = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM tutorials WHERE id = ?");
    $stmt->execute([$id]);
    $edit_tutorial = $stmt->fetch();
}

// Get all tutorials
$tutorials = $conn->query("SELECT * FROM tutorials ORDER BY created_at DESC")->fetchAll();
?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="page-title mb-0"><i class="fas fa-graduation-cap"></i> Tutorials Management</h2>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tutorialModal">
                <i class="fas fa-plus"></i> Add New Tutorial
            </button>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-list"></i> All Tutorials
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>Category</th>
                                <th>Difficulty</th>
                                <th>Resources</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tutorials as $tutorial): ?>
                                <tr>
                                    <td><strong>#<?php echo $tutorial['id']; ?></strong></td>
                                    <td>
                                        <?php if ($tutorial['image']): ?>
                                            <img src="../<?php echo htmlspecialchars($tutorial['image']); ?>" alt="Tutorial"
                                                style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                        <?php else: ?>
                                            <div
                                                style="width: 60px; height: 60px; background: #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($tutorial['title']); ?></strong></td>
                                    <td>
                                        <?php
                                        $category_class = 'bg-secondary';
                                        switch (strtolower($tutorial['category'])) {
                                            case 'arduino':
                                                $category_class = 'bg-primary';
                                                break;
                                            case 'iot':
                                                $category_class = 'bg-info';
                                                break;
                                            case 'raspberry pi':
                                                $category_class = 'bg-danger';
                                                break;
                                            case 'robotics':
                                                $category_class = 'bg-warning';
                                                break;
                                            case 'electronics':
                                                $category_class = 'bg-success';
                                                break;
                                            case 'sensors':
                                                $category_class = 'bg-info';
                                                break;
                                            case 'power':
                                                $category_class = 'bg-warning';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $category_class; ?>">
                                            <?php echo htmlspecialchars($tutorial['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $difficulty_val = $tutorial['difficulty'] ?? 'beginner';
                                        $badge_class = 'bg-secondary';
                                        $icon = 'fa-circle';
                                        switch ($difficulty_val) {
                                            case 'beginner':
                                                $badge_class = 'bg-success';
                                                $icon = 'fa-star';
                                                break;
                                            case 'intermediate':
                                                $badge_class = 'bg-warning';
                                                $icon = 'fa-star-half-alt';
                                                break;
                                            case 'advanced':
                                                $badge_class = 'bg-danger';
                                                $icon = 'fa-fire';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <i class="fas <?php echo $icon; ?>"></i> <?php echo ucfirst($difficulty_val); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($tutorial['video_url']): ?>
                                            <a href="<?php echo htmlspecialchars($tutorial['video_url']); ?>" target="_blank"
                                                class="btn btn-sm btn-outline-primary mb-1">
                                                <i class="fas fa-play-circle"></i> Video
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($tutorial['pdf_file']): ?>
                                            <a href="../<?php echo htmlspecialchars($tutorial['pdf_file']); ?>" target="_blank"
                                                class="btn btn-sm btn-outline-danger mb-1">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                            <a href="?delete_pdf=<?php echo $tutorial['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                                class="btn btn-sm btn-outline-secondary mb-1"
                                                onclick="return confirm('Are you sure you want to delete this PDF file?');"
                                                title="Delete PDF">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php
                                        $links = json_decode($tutorial['images'] ?? '[]', true);
                                        if ($links && count($links) > 0):
                                            ?>
                                            <span class="badge bg-info">
                                                <i class="fas fa-link"></i> <?php echo count($links); ?> Links
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><small
                                            class="text-muted"><?php echo date('M d, Y', strtotime($tutorial['created_at'])); ?></small>
                                    </td>
                                    <td class="text-nowrap">
                                        <button class="btn btn-sm btn-primary me-2"
                                            onclick="editTutorial(<?php echo $tutorial['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?delete=<?php echo $tutorial['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this tutorial?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tutorial Modal -->
<div class="modal fade" id="tutorialModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add New Tutorial</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="tutorialForm" enctype="multipart/form-data">
                <?php echo csrfField(); ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="tutorialId">

                    <div class="mb-3">
                        <label class="form-label">Tutorial Title</label>
                        <input type="text" class="form-control" name="title" id="tutorialTitle" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Excerpt (Brief Summary)</label>
                        <textarea class="form-control" name="description" id="tutorialDescription" rows="2"
                            required></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Full Tutorial Content (Topics, Subtopics, Code, etc.)</label>
                        <textarea class="form-control" name="content" id="tutorialContent" rows="10"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Tutorial Image (Face of Tutorial)</label>
                        <div class="drop-zone">
                            <i class="fas fa-cloud-upload-alt icon"></i>
                            <div class="text">Drag & drop or <b>browse</b></div>
                            <input type="file" name="image" id="tutorialImage" accept="image/*">
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">Upload an image to represent this tutorial</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <input type="text" class="form-control" name="category" id="tutorialCategory"
                                placeholder="e.g., Electronics, Arduino, IoT" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Difficulty Level</label>
                            <select class="form-select" name="difficulty" id="tutorialDifficulty" required>
                                <option value="">Select Level</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Video URL (YouTube, Vimeo, etc.)</label>
                        <input type="url" class="form-control" name="video_url" id="tutorialVideoUrl"
                            placeholder="https://www.youtube.com/watch?v=...">
                        <div id="videoPreview" class="mt-2"></div>
                    </div>

                    <!-- Links Section -->
                    <div class="mb-3">
                        <label class="form-label">Resource Links</label>
                        <div id="linksContainer">
                            <div class="link-item mb-2">
                                <div class="row">
                                    <div class="col-md-5">
                                        <input type="text" class="form-control" name="link_title[]"
                                            placeholder="Link Title">
                                    </div>
                                    <div class="col-md-6">
                                        <input type="url" class="form-control" name="link_url[]" placeholder="https://">
                                    </div>
                                    <div class="col-md-1">
                                        <button type="button" class="btn btn-danger btn-sm w-100"
                                            onclick="removeLink(this)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="addLink()">
                            <i class="fas fa-plus"></i> Add Another Link
                        </button>
                    </div>

                    <!-- PDF Section -->
                    <div class="mb-3">
                        <label class="form-label">PDF Resource</label>
                        <div class="drop-zone">
                            <i class="fas fa-file-pdf icon" style="color: #ef4444;"></i>
                            <div class="text">Drag & drop PDF or <b>browse</b></div>
                            <input type="file" name="pdf_file" id="tutorialPdf" accept=".pdf">
                        </div>
                        <div class="preview-container"></div>
                        <small class="text-muted">Upload a PDF guide, schematic, or documentation</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Tutorial</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/qtfzaflz9y6nb7hy0dvj1uqw5uwlzk7bp4zm93whxc9nydld/tinymce/8/tinymce.min.js"
    referrerpolicy="origin" crossorigin="anonymous"></script>

<script>
    // Global tutorial data
    var allTutorialsData = <?php echo json_encode($tutorials); ?>;

    document.addEventListener('DOMContentLoaded', function () {
        // Initialize TinyMCE
        if (typeof tinymce !== 'undefined') {
            tinymce.init({
                selector: '#tutorialContent',
                plugins: [
                    // Core editing features
                    'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
                    // Premium features
                    'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown', 'importword', 'exportword', 'exportpdf'
                ],
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat | codesample',
                tinycomments_mode: 'embedded',
                tinycomments_author: 'Author name',
                mergetags_list: [
                    { value: 'First.Name', title: 'First Name' },
                    { value: 'Email', title: 'Email' },
                ],
                ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
                uploadcare_public_key: 'cf36b40869233f98bb88',
                codesample_languages: [
                    { text: 'HTML/XML', value: 'markup' },
                    { text: 'JavaScript', value: 'javascript' },
                    { text: 'CSS', value: 'css' },
                    { text: 'PHP', value: 'php' },
                    { text: 'C++', value: 'cpp' },
                    { text: 'C#', value: 'csharp' },
                    { text: 'Python', value: 'python' }
                ],
                setup: function (editor) {
                    editor.on('change', function () {
                        editor.save();
                    });
                }
            });

            // Fix for TinyMCE dialogs (like Code Sample) not allowing focus inside Bootstrap Modals
            document.addEventListener('focusin', (e) => {
                if (e.target.closest(".tox-tinymce-aux, .moxman-window, .tam-assetmanager-root") !== null) {
                    e.stopImmediatePropagation();
                }
            });
        }

        // YouTube Video Preview Logic
        const videoInput = document.getElementById('tutorialVideoUrl');
        if (videoInput) {
            videoInput.addEventListener('input', function (e) {
                updateVideoPreviewForInput(e.target.value);
            });
        }
    });

    function getYoutubeId(url) {
        const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
        const match = url.match(regExp);
        return (match && match[2].length === 11) ? match[2] : null;
    }

    function updateVideoPreviewForInput(url) {
        const preview = document.getElementById('videoPreview');
        if (!preview) return;

        const youtubeId = getYoutubeId(url);

        if (youtubeId) {
            preview.innerHTML = `
                <div class="card bg-dark text-white p-2">
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://img.youtube.com/vi/${youtubeId}/mqdefault.jpg" class="img-thumbnail" style="width: 120px;">
                        <div>
                            <p class="mb-1 small text-info"><i class="fab fa-youtube"></i> YouTube Video Detected</p>
                            <a href="${url}" target="_blank" class="btn btn-sm btn-outline-light"><i class="fas fa-external-link-alt"></i> Test Link</a>
                        </div>
                    </div>
                </div>
            `;
        } else if (url) {
            preview.innerHTML = `<div class="alert alert-secondary py-1 px-2 small"><i class="fas fa-link"></i> External Video Link</div>`;
        } else {
            preview.innerHTML = '';
        }
    }

    function editTutorial(id) {
        const tutorial = allTutorialsData.find(t => t.id == id);
        if (!tutorial) return;

        document.getElementById('modalTitle').textContent = 'Edit Tutorial';
        document.getElementById('tutorialId').value = tutorial.id;
        document.getElementById('tutorialTitle').value = tutorial.title;
        document.getElementById('tutorialDescription').value = tutorial.excerpt || tutorial.description || '';
        document.getElementById('tutorialCategory').value = tutorial.category;
        document.getElementById('tutorialDifficulty').value = tutorial.difficulty || 'beginner';
        document.getElementById('tutorialVideoUrl').value = tutorial.video_url || '';

        if (tutorial.video_url) {
            updateVideoPreviewForInput(tutorial.video_url);
        } else {
            document.getElementById('videoPreview').innerHTML = '';
        }

        // Set TinyMCE content
        if (typeof tinymce !== 'undefined' && tinymce.get('tutorialContent')) {
            tinymce.get('tutorialContent').setContent(tutorial.content || '');
        } else {
            document.getElementById('tutorialContent').value = tutorial.content || '';
        }

        // Handle Image and PDF Previews in the new Drop-Zone containers
        const containers = document.querySelectorAll('.preview-container');
        // Clear all previews first
        containers.forEach(c => c.innerHTML = '');

        // Image Preview (1st container)
        if (tutorial.image) {
            const imgContainer = document.querySelector('input#tutorialImage').closest('.mb-3').querySelector('.preview-container');
            if (imgContainer) {
                imgContainer.innerHTML = `<div class="preview-item"><img src="../${tutorial.image}" alt="Preview"></div>`;
            }
        }

        // PDF Preview (2nd container)
        if (tutorial.pdf_file) {
            const pdfContainer = document.querySelector('input#tutorialPdf').closest('.mb-3').querySelector('.preview-container');
            if (pdfContainer) {
                const pdfName = tutorial.pdf_file.split('/').pop();
                pdfContainer.innerHTML = `<div class="alert alert-info py-2 m-0"><i class="fas fa-file-pdf"></i> Current: ${pdfName}</div>`;
            }
        }

        const linksContainer = document.getElementById('linksContainer');
        linksContainer.innerHTML = '';

        let links = [];
        try {
            links = JSON.parse(tutorial.images || '[]');
        } catch (e) {
            links = [];
        }

        if (links.length > 0) {
            links.forEach(link => {
                const linkItem = document.createElement('div');
                linkItem.className = 'link-item mb-2';
                linkItem.innerHTML = `
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="link_title[]" placeholder="Link Title" value="${link.title || ''}">
                        </div>
                        <div class="col-md-6">
                            <input type="url" class="form-control" name="link_url[]" placeholder="https://" value="${link.url || ''}">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeLink(this)">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                `;
                linksContainer.appendChild(linkItem);
            });
        } else {
            addLink();
        }

        // Show Modal
        const el = document.getElementById('tutorialModal');
        const modal = bootstrap.Modal.getInstance(el) || new bootstrap.Modal(el);
        modal.show();
    }

    // Reset form when modal is closed
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('tutorialModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('modalTitle').textContent = 'Add New Tutorial';
            document.getElementById('tutorialForm').reset();
            document.querySelectorAll('.preview-container').forEach(c => c.innerHTML = '');
            document.getElementById('videoPreview').innerHTML = '';
            if (typeof tinymce !== 'undefined' && tinymce.get('tutorialContent')) {
                tinymce.get('tutorialContent').setContent('');
            }
            const linksContainer = document.getElementById('linksContainer');
            linksContainer.innerHTML = `
                <div class="link-item mb-2">
                    <div class="row">
                        <div class="col-md-5">
                            <input type="text" class="form-control" name="link_title[]" placeholder="Link Title">
                        </div>
                        <div class="col-md-6">
                            <input type="url" class="form-control" name="link_url[]" placeholder="https://">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeLink(this)">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
    });

    function addLink() {
        const container = document.getElementById('linksContainer');
        const linkItem = document.createElement('div');
        linkItem.className = 'link-item mb-2';
        linkItem.innerHTML = `
            <div class="row">
                <div class="col-md-5">
                    <input type="text" class="form-control" name="link_title[]" placeholder="Link Title">
                </div>
                <div class="col-md-6">
                    <input type="url" class="form-control" name="link_url[]" placeholder="https://">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-danger btn-sm w-100" onclick="removeLink(this)">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
        `;
        if (container) container.appendChild(linkItem);
    }

    function removeLink(button) {
        button.closest('.link-item').remove();
    }
</script>

<?php require_once 'footer.php'; ?>