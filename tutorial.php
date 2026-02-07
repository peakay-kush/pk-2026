<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Get tutorial by slug
$slug = isset($_GET['slug']) ? $_GET['slug'] : '';

if (empty($slug)) {
    header('Location: ' . SITE_URL . '/tutorials');
    exit;
}

$stmt = $conn->prepare("SELECT * FROM tutorials WHERE slug = ?");
$stmt->execute([$slug]);

if ($stmt->rowCount() === 0) {
    header('Location: ' . SITE_URL . '/tutorials');
    exit;
}

$tutorial = $stmt->fetch();
$page_title = $tutorial['title'];
$page_description = isset($tutorial['excerpt']) ? $tutorial['excerpt'] : substr(strip_tags($tutorial['content']), 0, 160);
$page_image = $tutorial['image'] ?? 'assets/images/logo 2.png';
$og_type = 'article';

// Structured Data for Tutorial (Article)
$extra_head = '
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "' . htmlspecialchars($tutorial['title']) . '",
  "image": "' . SITE_URL . '/' . htmlspecialchars($tutorial['image'] ?? 'assets/images/logo 2.png') . '",
  "author": {
    "@type": "Organization",
    "name": "' . SITE_NAME . '"
  },  
  "publisher": {
    "@type": "Organization",
    "name": "' . SITE_NAME . '",
    "logo": {
      "@type": "ImageObject",
      "url": "' . SITE_URL . '/assets/images/logo 2.png"
    }
  },
  "datePublished": "' . date('c', strtotime($tutorial['created_at'])) . '",
  "description": "' . htmlspecialchars($page_description) . '"
}
</script>';

require_once 'includes/header.php';

// Fetch related tutorials
$related_stmt = $conn->prepare("SELECT * FROM tutorials WHERE category = ? AND slug != ? LIMIT 3");
$related_stmt->execute([$tutorial['category'], $slug]);
$related_tutorials = $related_stmt;
?>

<!-- Tutorial Header -->
<section class="pt-5 pb-4 bg-light">
    <!-- Prism.js for code highlighting -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet" />
    <div class="container">
        <div class="mb-4">
            <a href="<?php echo SITE_URL; ?>/tutorials" class="btn btn-outline-primary shadow-sm"
                style="border-radius: 20px;">
                <i class="fas fa-arrow-left me-2"></i> Back to Tutorials
            </a>
        </div>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <?php if ($tutorial['image']): ?>
                    <img src="<?php echo SITE_URL; ?>/<?php echo htmlspecialchars($tutorial['image']); ?>"
                        class="img-fluid rounded mb-4" alt="<?php echo htmlspecialchars($tutorial['title']); ?>"
                        style="max-height: 400px; width: 100%; object-fit: cover;">
                <?php endif; ?>
                <span class="badge mb-3"
                    style="background-color: var(--accent-color); color: white;"><?php echo htmlspecialchars($tutorial['category']); ?></span>
                <?php if (!empty($tutorial['difficulty'])): ?>
                    <span
                        class="badge mb-3 <?php echo $tutorial['difficulty'] === 'beginner' ? 'bg-success' : ($tutorial['difficulty'] === 'intermediate' ? 'bg-warning' : 'bg-danger'); ?>">
                        <?php echo ucfirst($tutorial['difficulty']); ?>
                    </span>
                <?php endif; ?>
                <h1 class="mb-3" style="color: #0B63CE;"><?php echo htmlspecialchars($tutorial['title']); ?></h1>
                <p class="text-muted">
                    <i class="fas fa-calendar"></i> Published on <?php echo formatDate($tutorial['created_at']); ?>
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Tutorial Content -->
<section class="pt-4 pb-5">
    <div class="container">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="tutorial-content">
                    <?php
                    // Replace literal \n with actual newlines (for sample data)
                    $content = str_replace('\n', "\n", $tutorial['content']);

                    // Fix relative image paths for proper display
                    $content = str_replace('src="../assets/', 'src="' . SITE_URL . '/assets/', $content);
                    $content = str_replace('src="assets/', 'src="' . SITE_URL . '/assets/', $content);

                    // Add copy button to code blocks
                    $content = preg_replace(
                        '/<pre(.*?)><code(.*?)>(.*?)<\/code><\/pre>/is',
                        '<div class="code-wrapper">
                            <button class="copy-btn" onclick="copyCode(this)"><i class="far fa-copy"></i> Copy</button>
                            <pre$1><code$2>$3</code></pre>
                        </div>',
                        $content
                    );

                    echo $content;
                    ?>
                </div>

                <!-- Tutorial Resources -->
                <?php if (!empty($tutorial['video_url']) || !empty($tutorial['pdf_file']) || !empty($tutorial['images'])): ?>
                    <div class="mt-5">
                        <h3 class="mb-4"><i class="fas fa-folder-open"></i> Tutorial Resources</h3>

                        <?php
                        // Parse links from images field (stored as JSON)
                        $links = [];
                        if (!empty($tutorial['images'])) {
                            $decoded = json_decode($tutorial['images'], true);
                            if (is_array($decoded)) {
                                $links = $decoded;
                            }
                        }
                        ?>

                        <?php if (!empty($tutorial['video_url'])): ?>
                            <?php
                            $videos = explode(',', $tutorial['video_url']);
                            foreach ($videos as $video):
                                $video = trim($video);
                                if (!empty($video)):
                                    ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-video text-danger"></i> Video Tutorial</h5>
                                            <?php if (strpos($video, 'youtube.com') !== false || strpos($video, 'youtu.be') !== false): ?>
                                                <?php
                                                // Extract YouTube video ID
                                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i', $video, $match);
                                                $videoId = $match[1] ?? '';
                                                if ($videoId):
                                                    ?>
                                                    <div class="ratio ratio-16x9">
                                                        <iframe src="https://www.youtube.com/embed/<?php echo htmlspecialchars($videoId); ?>"
                                                            allowfullscreen></iframe>
                                                    </div>
                                                <?php else: ?>
                                                    <a href="<?php echo htmlspecialchars($video); ?>" target="_blank" class="btn btn-primary">
                                                        <i class="fas fa-external-link-alt"></i> Watch Video
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="<?php echo htmlspecialchars($video); ?>" target="_blank" class="btn btn-primary">
                                                    <i class="fas fa-external-link-alt"></i> Watch Video
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        <?php endif; ?>

                        <?php if (!empty($tutorial['pdf_file'])): ?>
                            <?php
                            $pdfs = explode(',', $tutorial['pdf_file']);
                            foreach ($pdfs as $pdf):
                                $pdf = trim($pdf);
                                if (!empty($pdf)):
                                    $pdfName = basename($pdf);
                                    ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <h5 class="card-title"><i class="fas fa-file-pdf text-danger"></i> PDF Document</h5>
                                            <p class="mb-2"><?php echo htmlspecialchars($pdfName); ?></p>
                                            <div class="d-flex flex-column flex-sm-row gap-2">
                                                <a href="<?php echo htmlspecialchars($pdf); ?>" class="btn btn-primary" target="_blank"
                                                    download>
                                                    <i class="fas fa-download"></i> Download PDF
                                                </a>
                                                <a href="<?php echo htmlspecialchars($pdf); ?>" class="btn btn-outline-primary"
                                                    target="_blank">
                                                    <i class="fas fa-eye"></i> View PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endif;
                            endforeach;
                            ?>
                        <?php endif; ?>

                        <?php if (!empty($links) && count($links) > 0): ?>
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-link text-primary"></i> Resource Links</h5>
                                    <div class="list-group list-group-flush">
                                        <?php foreach ($links as $link): ?>
                                            <?php if (!empty($link['title']) && !empty($link['url'])): ?>
                                                <a href="<?php echo htmlspecialchars($link['url']); ?>"
                                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                    target="_blank">
                                                    <span><i
                                                            class="fas fa-external-link-alt text-muted me-2"></i><?php echo htmlspecialchars($link['title']); ?></span>
                                                    <i class="fas fa-arrow-right text-muted"></i>
                                                </a>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Share Section -->
                <div class="mt-5 pt-5 border-top">
                    <h5>Found this tutorial helpful?</h5>
                    <p>Share it with your classmates and friends!</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/tutorial/' . $tutorial['slug']); ?>"
                            class="btn btn-primary" target="_blank"><i class="fab fa-facebook"></i> Share</a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(SITE_URL . '/tutorial/' . $tutorial['slug']); ?>&text=<?php echo urlencode($tutorial['title']); ?>"
                            class="btn btn-info text-white" target="_blank"><i class="fab fa-twitter"></i> Tweet</a>
                        <a href="https://wa.me/?text=<?php echo urlencode($tutorial['title'] . ' - ' . SITE_URL . '/tutorial/' . $tutorial['slug']); ?>"
                            class="btn btn-success" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Tutorials -->
<?php if ($related_tutorials->rowCount() > 0): ?>
    <section class="section-padding bg-light">
        <div class="container">
            <h3 class="mb-4">Related Tutorials</h3>
            <div class="row g-4">
                <?php while ($related = $related_tutorials->fetch()): ?>
                    <div class="col-md-4">
                        <div class="card tutorial-card h-100">
                            <div class="card-body">
                                <span class="badge mb-2"
                                    style="background-color: var(--accent-color); color: white;"><?php echo htmlspecialchars($related['category']); ?></span>
                                <h5 class="card-title" style="color: #0B63CE;">
                                    <?php echo htmlspecialchars($related['title']); ?>
                                </h5>
                                <p class="card-text"><?php echo htmlspecialchars($related['excerpt']); ?></p>
                                <a href="<?php echo SITE_URL; ?>/tutorial/<?php echo urlencode($related['slug']); ?>"
                                    class="btn btn-accent">
                                    Read Tutorial
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container text-center">
        <h2>Need Components for This Project?</h2>
        <p class="lead mb-4">Browse our shop for all the parts you need</p>
        <a href="<?php echo SITE_URL; ?>/shop" class="btn btn-lg"><i class="fas fa-shopping-bag"></i> Visit Shop</a>
    </div>
</section>

<!-- Prism.js script -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-clike.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-c.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-cpp.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-arduino.min.js"></script>

<script>
    function copyCode(button) {
        const pre = button.nextElementSibling;
        const code = pre.querySelector('code');
        const text = code.innerText;

        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = button.innerHTML;
            button.innerHTML = '<i class="fas fa-check"></i> Copied!';
            button.classList.add('copied');

            setTimeout(() => {
                button.innerHTML = originalHtml;
                button.classList.remove('copied');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy: ', err);
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>