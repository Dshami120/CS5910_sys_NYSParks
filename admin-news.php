<?php
// Load setup and require admin access.
require 'bootstrap.php';
$user = require_role($db, 'admin');

// Load news lookup values.
$newsTopics = NEWS_TOPICS;
$newsStatuses = NEWS_STATUSES;

// Handle news form actions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = post('action');

    // Process supported news actions.
    if (in_array($action, ['publish_news','update_news','delete_news'], true)) {
        $newsId = (int) post('news_id');

        // Delete selected news item.
        if ($action === 'delete_news') {
            if ($newsId <= 0) {
                flash_set('error', 'Select a news item to delete.');
                redirect('admin-news.php');
            }

            $exists = $db->prepare("SELECT COUNT(*) FROM news WHERE id=?");
            $exists->execute([$newsId]);

            if ((int)$exists->fetchColumn() === 0) {
                flash_set('error', 'News item not found.');
                redirect('admin-news.php');
            }

            $db->prepare("DELETE FROM news WHERE id=?")->execute([$newsId]);

            flash_set('success', 'News item deleted.');
            redirect('admin-news.php');
        }

        // Normalize topic and status.
        $topic = post('topic') ?: 'community';
        $status = post('news_status') ?: 'published';

        if (!in_array($topic, $newsTopics, true)) {
            $topic = 'community';
        }

        if (!in_array($status, $newsStatuses, true)) {
            $status = 'draft';
        }

        // Validate required article fields.
        if (post('title') === '' || post('summary') === '' || post('content') === '' || post('region') === '') {
            flash_set('error', 'News title, summary, content, and region are required.');
            redirect($newsId > 0 ? 'admin-news.php?news_id=' . $newsId : 'admin-news.php');
        }

        // Prepare shared article values.
        $publishedDate = post('published_date') ?: date('Y-m-d');
        $isFeatured = post('is_featured') === '1' ? 1 : 0;

        // Publish new article.
        if ($action === 'publish_news') {
            if ($newsId > 0) {
                flash_set('error', 'Use Create New before publishing a new article.');
                redirect('admin-news.php?news_id=' . $newsId);
            }

            $db->prepare("INSERT INTO news (title, topic, published_date, region, summary, content, image_url, image_alt, card_summary, tag, is_featured, news_status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([post('title'), $topic, $publishedDate, post('region'), post('summary'), post('content'), post('image_url') ?: null, post('image_alt') ?: null, post('card_summary') ?: null, post('tag') ?: ucwords(str_replace('_',' ', $topic)), $isFeatured, $status]);

            $newId = (int) $db->lastInsertId();

            flash_set('success', 'News item published.');
            redirect('admin-news.php?news_id=' . $newId);
        }

        // Update selected article.
        if ($action === 'update_news') {
            if ($newsId <= 0) {
                flash_set('error', 'Select a news item to update.');
                redirect('admin-news.php');
            }

            $exists = $db->prepare("SELECT COUNT(*) FROM news WHERE id=?");
            $exists->execute([$newsId]);

            if ((int)$exists->fetchColumn() === 0) {
                flash_set('error', 'News item not found.');
                redirect('admin-news.php');
            }

            $db->prepare("UPDATE news SET title=?, topic=?, published_date=?, region=?, summary=?, content=?, image_url=?, image_alt=?, card_summary=?, tag=?, is_featured=?, news_status=? WHERE id=?")
                ->execute([post('title'), $topic, $publishedDate, post('region'), post('summary'), post('content'), post('image_url') ?: null, post('image_alt') ?: null, post('card_summary') ?: null, post('tag') ?: ucwords(str_replace('_',' ', $topic)), $isFeatured, $status, $newsId]);

            flash_set('success', 'News item updated.');
            redirect('admin-news.php?news_id=' . $newsId);
        }
    }
}

// Load news search value.
$newsSearch = trim(get('news_q', ''));

// Load all news items.
$newsItems = $db->query("SELECT * FROM news ORDER BY published_date DESC, updated_at DESC, id DESC")->fetchAll();
$filteredNewsItems = $newsItems;

// Apply news search.
if ($newsSearch !== '') {
    $like = '%' . $newsSearch . '%';
    $newsSearchStmt = $db->prepare("SELECT * FROM news WHERE title LIKE ? OR summary LIKE ? OR region LIKE ? OR topic LIKE ? OR news_status LIKE ? ORDER BY published_date DESC, updated_at DESC, id DESC");
    $newsSearchStmt->execute([$like, $like, $like, $like, $like]);
    $filteredNewsItems = $newsSearchStmt->fetchAll();
}

// Load selected news item.
$selectedNewsId = (int) get('news_id');
$selectedNews = null;

if ($selectedNewsId) {
    $stmt = $db->prepare("SELECT * FROM news WHERE id=?");
    $stmt->execute([$selectedNewsId]);
    $selectedNews = $stmt->fetch();
}
?>

<?php
// Set page metadata.
$pageTitle = 'NYS Parks - Admin News';
$bodyPage = 'admin-news';
$extraHead = '';
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container py-5">

        <!-- Flash message -->
        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type']==='error'?'danger':'success' ?> mb-4">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <!-- Page heading and admin navigation -->
        <section class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
            <div>
                <p class="eyebrow mb-2">Admin Workspace</p>
                <h1 class="display-6 fw-bold mb-2">News Manager</h1>
                <p class="text-muted mb-0">Create, update, publish, archive, or delete public news articles.</p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <a class="btn btn-outline-dark" href="admin-dashboard.php">
                    <i class="bi bi-speedometer2"></i> Admin Dash
                </a>
                <a class="btn btn-outline-dark" href="admin-employee-schedule.php">
                    <i class="bi bi-calendar3"></i> Employee Schedules
                </a>
                <a class="btn btn-outline-dark" href="admin-pto.php">
                    <i class="bi bi-briefcase"></i> PTO Requests
                </a>
                <a class="btn btn-outline-dark" href="admin-bookings.php">
                    <i class="bi bi-journal-check"></i> Client Bookings
                </a>
                <a class="btn btn-success" href="admin-news.php">
                    <i class="bi bi-newspaper"></i> News Manager
                </a>
                <a class="btn btn-outline-dark" href="admin-employee-accounts.php">
                    <i class="bi bi-people"></i> Employee Accounts
                </a>
                <a class="btn btn-outline-dark" href="admin-csv.php">
                    <i class="bi bi-filetype-csv"></i> CSV
                </a>
            </div>
        </section>

        <!-- News manager -->
        <section class="card shadow-sm border-0 rounded-4 mb-4" id="news-manager">
            <div class="card-body p-4 p-lg-5">

                <!-- News manager heading -->
                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h3 fw-bold mb-1"><?= $selectedNews ? 'Update Selected News' : 'Create News Article' ?></h2>
                        <p class="text-muted mb-0">Use search or the dropdown to edit an existing article, or choose Create New before publishing a new one.</p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 align-items-start">
                        <a class="btn btn-outline-dark" href="admin-news.php">
                            <i class="bi bi-plus-circle"></i> Create New
                        </a>

                        <?php if ($selectedNews && ($selectedNews['news_status'] ?? '') === 'published'): ?>
                            <a class="btn btn-success" href="news.php#news-<?= (int)$selectedNews['id'] ?>">
                                <i class="bi bi-box-arrow-up-right"></i> View on News Page
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- News manager layout -->
                <div class="row g-4">

                    <!-- Existing news column -->
                    <div class="col-xl-5">
                        <div class="account-panel h-100">
                            <h3 class="h5 fw-bold mb-3">Existing News</h3>

                            <!-- Search existing news -->
                            <form method="get" class="row g-2 align-items-end mb-3">
                                <div class="col-12">
                                    <label class="form-label">Search Existing News</label>
                                    <input type="text" name="news_q" class="form-control" value="<?= e($newsSearch) ?>" placeholder="Search by title, topic, region, or status" />
                                </div>

                                <div class="col-12 d-flex gap-2">
                                    <button type="submit" class="btn btn-outline-dark btn-sm">Search</button>
                                    <a href="admin-news.php" class="btn btn-outline-secondary btn-sm">Reset</a>
                                </div>
                            </form>

                            <!-- News dropdown -->
                            <form method="get" class="mb-3">
                                <label class="form-label">News Article Dropdown</label>

                                <?php if ($newsSearch !== ''): ?>
                                    <input type="hidden" name="news_q" value="<?= e($newsSearch) ?>" />
                                <?php endif; ?>

                                <select class="form-select form-select-lg" name="news_id" onchange="this.form.submit()">
                                    <option value="">Select news article</option>

                                    <?php foreach ($newsItems as $item): ?>
                                        <option value="<?= (int)$item['id'] ?>" <?= $selectedNews && (int)$selectedNews['id']===(int)$item['id'] ? 'selected' : '' ?>>
                                            <?= e($item['title']) ?> — <?= e(ucfirst($item['news_status'])) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>

                            <!-- Search result links -->
                            <?php if ($newsSearch !== ''): ?>
                                <div class="vstack gap-2 mb-3">
                                    <p class="small text-muted mb-0">Search results: <?= count($filteredNewsItems) ?></p>

                                    <?php foreach ($filteredNewsItems as $item): ?>
                                        <a class="text-decoration-none text-dark border rounded-3 p-2 <?= $selectedNews && (int)$selectedNews['id'] === (int)$item['id'] ? 'bg-light' : '' ?>" href="admin-news.php?news_id=<?= (int)$item['id'] ?>&news_q=<?= urlencode($newsSearch) ?>">
                                            <strong class="d-block small"><?= e($item['title']) ?></strong>
                                            <span class="small text-muted"><?= e(ucfirst($item['news_status'])) ?> · <?= e($item['region']) ?></span>
                                        </a>
                                    <?php endforeach; ?>

                                    <?php if (!$filteredNewsItems): ?>
                                        <p class="small text-muted mb-0">No news articles matched your search.</p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Selected article preview -->
                            <div class="mini-panel">
                                <strong class="d-block mb-2">Selected Article Preview</strong>

                                <?php if ($selectedNews): ?>
                                    <p class="small text-muted mb-1">Title: <?= e($selectedNews['title']) ?></p>
                                    <p class="small text-muted mb-1">Status: <?= e(ucfirst($selectedNews['news_status'])) ?></p>
                                    <p class="small text-muted mb-1">Region: <?= e($selectedNews['region']) ?></p>
                                    <p class="small text-muted mb-0">Published: <?= e(format_date($selectedNews['published_date'])) ?></p>
                                <?php else: ?>
                                    <p class="small text-muted mb-0">No article selected. The form is ready for a new news article.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- News form column -->
                    <div class="col-xl-7">
                        <div class="account-panel h-100">
                            <h3 class="h5 fw-bold mb-3">News Form</h3>

                            <!-- News article form -->
                            <form class="row g-3" method="post">
                                <input type="hidden" name="news_id" value="<?= (int)($selectedNews['id'] ?? 0) ?>" />

                                <!-- Title field -->
                                <div class="col-md-8">
                                    <label class="form-label">Title</label>
                                    <input type="text" name="title" class="form-control form-control-lg" value="<?= e($selectedNews['title'] ?? '') ?>" required />
                                </div>

                                <!-- Published date field -->
                                <div class="col-md-4">
                                    <label class="form-label">Published Date</label>
                                    <input type="date" name="published_date" class="form-control form-control-lg" value="<?= e((string)($selectedNews['published_date'] ?? date('Y-m-d'))) ?>" />
                                </div>

                                <!-- Topic field -->
                                <div class="col-md-4">
                                    <label class="form-label">Topic</label>
                                    <select name="topic" class="form-select form-select-lg">
                                        <?php foreach ($newsTopics as $topic): ?>
                                            <option value="<?= e($topic) ?>" <?= ($selectedNews['topic'] ?? 'community')===$topic ? 'selected' : '' ?>>
                                                <?= e(ucwords(str_replace('_',' ', $topic))) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Region field -->
                                <div class="col-md-4">
                                    <label class="form-label">Region</label>
                                    <input type="text" name="region" class="form-control form-control-lg" value="<?= e($selectedNews['region'] ?? '') ?>" required />
                                </div>

                                <!-- Status field -->
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="news_status" class="form-select form-select-lg">
                                        <?php foreach ($newsStatuses as $status): ?>
                                            <option value="<?= e($status) ?>" <?= ($selectedNews['news_status'] ?? 'published')===$status ? 'selected' : '' ?>>
                                                <?= e(ucfirst($status)) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Tag field -->
                                <div class="col-md-6">
                                    <label class="form-label">Tag</label>
                                    <input type="text" name="tag" class="form-control" value="<?= e($selectedNews['tag'] ?? '') ?>" />
                                </div>

                                <!-- Featured checkbox -->
                                <div class="col-md-6 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="newsFeatured" <?= !empty($selectedNews['is_featured']) ? 'checked' : '' ?> />
                                        <label class="form-check-label" for="newsFeatured">Featured news</label>
                                    </div>
                                </div>

                                <!-- Summary field -->
                                <div class="col-12">
                                    <label class="form-label">Summary</label>
                                    <textarea name="summary" class="form-control" rows="2" required><?= e($selectedNews['summary'] ?? '') ?></textarea>
                                </div>

                                <!-- Content field -->
                                <div class="col-12">
                                    <label class="form-label">Article Content</label>
                                    <textarea name="content" class="form-control" rows="5" required><?= e($selectedNews['content'] ?? '') ?></textarea>
                                </div>

                                <!-- Image URL field -->
                                <div class="col-md-6">
                                    <label class="form-label">Image URL</label>
                                    <input type="text" name="image_url" class="form-control" value="<?= e($selectedNews['image_url'] ?? '') ?>" />
                                </div>

                                <!-- Image alt field -->
                                <div class="col-md-6">
                                    <label class="form-label">Image Alt Text</label>
                                    <input type="text" name="image_alt" class="form-control" value="<?= e($selectedNews['image_alt'] ?? '') ?>" />
                                </div>

                                <!-- Card summary field -->
                                <div class="col-12">
                                    <label class="form-label">Card Summary</label>
                                    <input type="text" name="card_summary" class="form-control" value="<?= e($selectedNews['card_summary'] ?? '') ?>" />
                                </div>

                                <!-- Form action buttons -->
                                <div class="col-12 d-flex flex-wrap gap-3">
                                    <button type="submit" name="action" value="publish_news" class="btn btn-success btn-lg" <?= $selectedNews ? 'disabled' : '' ?>>Publish News</button>
                                    <button type="submit" name="action" value="update_news" class="btn btn-outline-dark btn-lg" <?= $selectedNews ? '' : 'disabled' ?>>Update Selected News</button>
                                    <button type="submit" name="action" value="delete_news" class="btn btn-outline-danger btn-lg" data-confirm="Delete this news item?" <?= $selectedNews ? '' : 'disabled' ?>>Delete Selected News</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>