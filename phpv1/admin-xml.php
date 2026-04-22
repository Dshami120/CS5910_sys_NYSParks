<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Admin XML Tools | ' . SITE_NAME;
$activePage = '';
$adminSection = 'xml';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
requireLogin(['admin']);

$flash = getFlashMessage();
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>
<main class="admin-portal-page py-5">
    <div class="container">
        <?php if ($flash): ?>
            <div class="alert alert-<?php echo e($flash['type']); ?> mb-4"><?php echo e($flash['message']); ?></div>
        <?php endif; ?>

        <?php require __DIR__ . '/includes/admin-nav.php'; ?>

        <section class="admin-dashboard-hero rounded-4 p-4 p-lg-5 mb-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-8">
                    <span class="badge rounded-pill bg-light text-success px-3 py-2 mb-3">XML Tools</span>
                    <h1 class="display-6 fw-bold mb-3">Import and export project XML</h1>
                    <p class="lead text-white-75 mb-0">Use this screen to export parks, events, or bookings into XML files for submission/demo needs, or import a parks XML file back into MySQL.</p>
                </div>
                <div class="col-lg-4">
                    <div class="admin-hero-panel p-4 h-100">
                        <div class="small text-uppercase fw-bold text-white-50 mb-1">Tips</div>
                        <ul class="mb-0 text-white-75 small ps-3">
                            <li>Exports use the current database when available.</li>
                            <li>If MySQL is offline, exports fall back to demo data.</li>
                            <li>The park import expects the sample structure in <code>database/xml/sample-parks.xml</code>.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <span class="section-kicker">Export</span>
                        <h2 class="h4 fw-bold mb-3">Download XML files</h2>
                        <p class="text-muted">These exports are ready for demos, testing, or project submission attachments.</p>
                        <div class="d-grid gap-3">
                            <a href="api/export-parks-xml.php" class="btn btn-success btn-lg"><i class="bi bi-download me-2"></i>Export Parks XML</a>
                            <a href="api/export-events-xml.php" class="btn btn-outline-success btn-lg"><i class="bi bi-download me-2"></i>Export Events XML</a>
                            <a href="api/export-bookings-xml.php" class="btn btn-outline-success btn-lg"><i class="bi bi-download me-2"></i>Export Bookings XML</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <span class="section-kicker">Import</span>
                        <h2 class="h4 fw-bold mb-3">Import Parks XML</h2>
                        <p class="text-muted">Upload a parks XML file and the system will insert or update matching park records by <strong>park_name</strong>.</p>
                        <form action="api/import-parks-xml.php" method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="parksXmlFile" class="form-label fw-semibold">Choose XML file</label>
                                <input class="form-control" type="file" id="parksXmlFile" name="parks_xml" accept=".xml" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-upload me-2"></i>Import Parks XML</button>
                        </form>
                        <hr>
                        <p class="small text-muted mb-0">Sample files are included in <code>database/xml/</code> inside the project zip.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
