<?php
require 'bootstrap.php';
require 'map-api.php';

// Load parks for the map and directory.
$parks = $db->query("SELECT * FROM parks ORDER BY is_featured DESC, name")->fetchAll();

// Build map-safe park data for JavaScript.
$mapParks = array_map(function ($park) {
    return [
        'id' => (int) $park['id'],
        'name' => $park['name'],
        'region' => $park['region'],
        'park_type' => $park['park_type'],
        'city' => $park['city'],
        'state' => $park['state'],
        'address_line' => $park['address_line'],
        'zip_code' => $park['zip_code'],
        'hours' => $park['hours'],
        'amenities' => $park['amenities'],
        'description' => $park['description'],
        'card_summary' => $park['card_summary'],
        'image_url' => $park['image_url'],
        'image_alt' => $park['image_alt'],
        'latitude' => $park['latitude'] !== null ? (float) $park['latitude'] : null,
        'longitude' => $park['longitude'] !== null ? (float) $park['longitude'] : null,
        'total_fields' => (int) $park['total_fields'],
        'max_capacity' => (int) $park['max_capacity'],
    ];
}, $parks);
?>

<?php
$pageTitle = 'NYS Parks - Map';
$bodyPage = 'map';
$extraHead = <<<'HTML'
  <style>
    .map-shell {
      min-height: 620px;
      border-radius: 1.35rem;
      overflow: hidden;
      box-shadow: var(--nys-shadow);
      border: 1px solid rgba(233, 238, 245, 0.9);
    }

    #parksGoogleMap {
      width: 100%;
      min-height: 620px;
      z-index: 1;
    }

    .map-sidebar {
      max-height: 620px;
      overflow-y: auto;
    }

    .park-map-row {
      border: 1px solid var(--nys-border);
      border-radius: 1rem;
      background: #ffffff;
      transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .park-map-row:hover,
    .park-map-row:focus {
      transform: translateY(-2px);
      box-shadow: 0 10px 24px rgba(15, 24, 52, 0.08);
      border-color: rgba(40, 127, 75, 0.35);
    }

    .park-map-row.active {
      border-color: rgba(40, 127, 75, 0.7);
      box-shadow: 0 12px 26px rgba(40, 127, 75, 0.13);
    }

    .map-detail-photo {
      width: 100%;
      height: 170px;
      object-fit: cover;
      border-radius: 1rem;
    }

    .map-api-notice {
      min-height: 620px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem;
      text-align: center;
      background: linear-gradient(135deg, rgba(248, 252, 249, 0.95), rgba(237, 247, 241, 0.95));
    }

    @media (max-width: 1199px) {
      #parksGoogleMap,
      .map-shell {
        min-height: 500px;
      }

      .map-sidebar {
        max-height: 420px;
      }
    }
  </style>
HTML;
?>

<?php include __DIR__ . '/includes/header.php'; ?>

    <main>
        <!-- blue title section -->
        <section class="subpage-hero subpage-hero-news text-white d-flex align-items-center">
            <div class="subpage-hero-overlay"></div>

            <div class="container position-relative py-5">
                <div class="row">
                    <div class="col-lg-8">
                        <span class="hero-kicker">Interactive discovery</span>
                        <h1 class="display-5 fw-bold mb-3">Statewide park map</h1>
                        <p class="lead text-white-50 mb-0">Explore parks from the database, open markers, and use the park tiles below to jump directly to each destination.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Map and park directory -->
        <section class="py-5">
            <section class="container">
                <section class="row g-4 align-items-stretch">

                    <!-- Park list sidebar -->
                    <aside class="col-xl-4">
                        <section class="soft-card p-4 h-100 map-sidebar">

                            <!-- Directory heading -->
                            <section class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <section>
                                    <p class="section-kicker mb-2">Park directory</p>
                                    <h2 class="h5 fw-bold mb-1">Select a park</h2>
                                    <p class="text-muted mb-0 small">Click a park to focus the map marker and view details.</p>
                                </section>

                                <span class="badge text-bg-success rounded-pill"><?= count($parks) ?> parks</span>
                            </section>

                            <!-- Park search input -->
                            <label class="form-label" for="mapParkSearch">Search parks</label>
                            <input type="search" id="mapParkSearch" class="form-control mb-3" placeholder="Search by name, region, or city" />

                            <!-- Park list buttons -->
                            <section class="d-grid gap-3" id="mapParkList">
                                <?php foreach ($parks as $park): ?>
                                    <button
                                            type="button"
                                            class="park-map-row text-start p-3"
                                            data-park-id="<?= (int)$park['id'] ?>"
                                            data-park-search="<?= e(strtolower($park['name'] . ' ' . $park['region'] . ' ' . $park['city'] . ' ' . $park['park_type'])) ?>"
                                    >
                                        <section class="d-flex justify-content-between align-items-start gap-3">
                                            <section>
                                                <strong><?= e($park['name']) ?></strong><br />
                                                <span class="text-muted small"><?= e($park['city']) ?>, <?= e($park['state']) ?> · <?= e($park['region']) ?></span>
                                            </section>

                                            <i class="bi bi-geo-alt-fill text-success"></i>
                                        </section>
                                    </button>
                                <?php endforeach; ?>
                            </section>
                        </section>
                    </aside>

                    <!-- Google map column -->
                    <article class="col-xl-8">
                        <section class="map-shell mb-4">
                            <section id="parksGoogleMap" role="application" aria-label="Interactive Google map of New York State parks"></section>
                        </section>

                        <!-- Selected park details -->
                        <section class="soft-card p-4" id="selectedParkDetails">
                            <p class="section-kicker mb-2">Selected park</p>
                            <h2 class="h5 fw-bold mb-2">Choose a park from the list or tiles</h2>
                            <p class="text-muted mb-0">The selected park details will appear here after you open a map marker.</p>
                        </section>
                    </article>
                </section>
            </section>
        </section>

        <!-- Map park tiles -->
        <section class="py-5 pt-2">
            <section class="container">

                <!-- Park tiles heading -->
                <header class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <section>
                        <h2 class="fw-bold mb-1">All parks on the map</h2>
                        <p class="text-muted mb-0">Use these park tiles to jump to each marker and details panel.</p>
                    </section>

                    <a href="parks.php" class="map-link">Open full parks page →</a>
                </header>

                <!-- Park tiles grid -->
                <section class="row g-4" id="mapParkTiles">
                    <?php foreach ($parks as $park): ?>
                        <article class="col-md-6 col-xl-4" data-park-search="<?= e(strtolower($park['name'] . ' ' . $park['region'] . ' ' . $park['city'] . ' ' . $park['park_type'])) ?>">
                            <figure class="image-card h-100 mb-0">
                                <img src="<?= e($park['image_url']) ?>" alt="<?= e($park['image_alt'] ?: $park['name']) ?>" class="image-cover-md" />

                                <figcaption class="p-4">
                                    <p class="section-kicker mb-2"><?= e($park['region']) ?> · <?= e($park['park_type']) ?></p>
                                    <h3 class="h5 fw-bold mb-2"><?= e($park['name']) ?></h3>
                                    <p class="text-muted mb-2"><?= e($park['card_summary'] ?: $park['description']) ?></p>
                                    <p class="small text-muted mb-2"><i class="bi bi-grid-3x3-gap me-1"></i><?= (int)$park['total_fields'] ?> fields · max <?= (int)$park['max_capacity'] ?> guests</p>
                                    <p class="small text-muted mb-3"><i class="bi bi-geo-alt me-1"></i><?= e($park['city']) ?>, <?= e($park['state']) ?></p>

                                    <button type="button" class="btn btn-success w-100 rounded-pill fw-semibold" data-park-id="<?= (int)$park['id'] ?>">
                                        Show on Map
                                    </button>
                                </figcaption>
                            </figure>
                        </article>
                    <?php endforeach; ?>

                    <?php if (!$parks): ?>
                        <p class="text-muted">No parks are available to map yet.</p>
                    <?php endif; ?>
                </section>
            </section>
        </section>
    </main>

    <!-- Bootstrap JavaScript bundle -->
    <script>
        // Store map park data from PHP.
        const mapParks = <?= json_encode($mapParks, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const hasGoogleMapsApiKey = <?= has_google_maps_api_key() ? 'true' : 'false' ?>;

        let parksGoogleMap = null;
        let parkInfoWindow = null;
        const markerByParkId = new Map();

        // Escape unsafe text before inserting HTML.
        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>'"]/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;' })[char];
            });
        }

        // Build formatted park address.
        function parkAddress(park) {
            return `${escapeHtml(park.address_line)}<br>${escapeHtml(park.city)}, ${escapeHtml(park.state)} ${escapeHtml(park.zip_code)}`;
        }

        // Render selected park detail card.
        function renderDetailCard(park) {
            const details = document.getElementById('selectedParkDetails');

            details.innerHTML = `
        <section class="row g-3 align-items-start">
          <article class="col-md-4">
            <img src="${escapeHtml(park.image_url)}" alt="${escapeHtml(park.image_alt || park.name)}" class="map-detail-photo" />
          </article>
          <article class="col-md-8">
            <p class="section-kicker mb-2">${escapeHtml(park.region)} · ${escapeHtml(park.park_type)}</p>
            <h2 class="h4 fw-bold mb-2">${escapeHtml(park.name)}</h2>
            <p class="text-muted mb-3">${escapeHtml(park.description)}</p>
            <section class="row g-3">
              <article class="col-md-6">
                <section class="simple-card p-3 h-100">
                  <h3 class="h6 fw-bold mb-2"><i class="bi bi-geo-alt me-1"></i>Location</h3>
                  <p class="text-muted mb-0">${parkAddress(park)}</p>
                </section>
              </article>
              <article class="col-md-6">
                <section class="simple-card p-3 h-100">
                  <h3 class="h6 fw-bold mb-2"><i class="bi bi-clock me-1"></i>Hours</h3>
                  <p class="text-muted mb-0">${escapeHtml(park.hours)}</p>
                </section>
              </article>
              <article class="col-md-6">
                <section class="simple-card p-3 h-100">
                  <h3 class="h6 fw-bold mb-2"><i class="bi bi-people me-1"></i>Capacity</h3>
                  <p class="text-muted mb-0">${park.total_fields} fields · max ${park.max_capacity} guests</p>
                </section>
              </article>
              <article class="col-md-6">
                <section class="simple-card p-3 h-100">
                  <h3 class="h6 fw-bold mb-2"><i class="bi bi-grid-3x3-gap me-1"></i>Amenities</h3>
                  <p class="text-muted mb-0">${escapeHtml(park.amenities || 'Amenities vary by season. Check official park guidance before visiting.')}</p>
                </section>
              </article>
            </section>
          </article>
        </section>
      `;
        }

        // Highlight selected park row and tile.
        function setActiveParkRow(parkId) {
            document.querySelectorAll('[data-park-id]').forEach((element) => {
                element.classList.toggle('active', Number(element.dataset.parkId) === Number(parkId));
            });
        }

        // Build marker popup HTML.
        function markerPopupHtml(park) {
            return `
        <section style="max-width: 250px;">
          <strong>${escapeHtml(park.name)}</strong><br>
          <span>${escapeHtml(park.city)}, ${escapeHtml(park.state)} · ${escapeHtml(park.region)}</span><br>
          <button type="button" class="btn btn-success btn-sm rounded-pill mt-2" onclick="openPark(${Number(park.id)})">View details</button>
        </section>
      `;
        }

        // Open a park on the map and details panel.
        function openPark(parkId) {
            const park = mapParks.find((item) => Number(item.id) === Number(parkId));

            if (!park) return;

            renderDetailCard(park);
            setActiveParkRow(park.id);

            const marker = markerByParkId.get(Number(park.id));

            if (parksGoogleMap && marker && parkInfoWindow) {
                parksGoogleMap.panTo(marker.getPosition());
                parksGoogleMap.setZoom(11);
                parkInfoWindow.setContent(markerPopupHtml(park));
                parkInfoWindow.open(parksGoogleMap, marker);
                document.getElementById('parksGoogleMap').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        // Show fallback when Google Maps API key is missing.
        function showMapApiNotice() {
            const mapContainer = document.getElementById('parksGoogleMap');

            mapContainer.innerHTML = `
        <section class="map-api-notice">
          <article>
            <i class="bi bi-map fs-1 text-success"></i>
            <h2 class="h4 fw-bold mt-3 mb-2">Google Maps API key needed</h2>
            <p class="text-muted mb-0">
              Add your key in <code>map-api.php</code> to load the Google map. The park list and tiles are still connected to the database.
            </p>
          </article>
        </section>
      `;
        }

        // Initialize Google map and markers.
        function initParksGoogleMap() {
            const parksWithCoordinates = mapParks.filter((park) => park.latitude !== null && park.longitude !== null);

            parksGoogleMap = new google.maps.Map(document.getElementById('parksGoogleMap'), {
                center: { lat: 42.9, lng: -75.5 },
                zoom: 6,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: true
            });

            parkInfoWindow = new google.maps.InfoWindow();
            const bounds = new google.maps.LatLngBounds();

            parksWithCoordinates.forEach((park) => {
                const position = { lat: Number(park.latitude), lng: Number(park.longitude) };
                const marker = new google.maps.Marker({
                    position,
                    map: parksGoogleMap,
                    title: park.name
                });

                marker.addListener('click', () => openPark(park.id));
                markerByParkId.set(Number(park.id), marker);
                bounds.extend(position);
            });

            if (parksWithCoordinates.length) {
                parksGoogleMap.fitBounds(bounds, 35);
            }
        }

        window.initParksGoogleMap = initParksGoogleMap;

        // Bind park click events.
        document.querySelectorAll('[data-park-id]').forEach((element) => {
            element.addEventListener('click', () => openPark(element.dataset.parkId));
        });

        // Filter park list and tiles by search.
        const searchInput = document.getElementById('mapParkSearch');

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();

            document.querySelectorAll('[data-park-search]').forEach((element) => {
                const matches = element.dataset.parkSearch.includes(term);
                element.classList.toggle('d-none', !matches);
            });
        });

        // Show API notice when no Google key exists.
        if (!hasGoogleMapsApiKey) {
            showMapApiNotice();
        }
    </script>

<?php if (has_google_maps_api_key()): ?>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=<?= urlencode(GOOGLE_MAPS_API_KEY) ?>&callback=initParksGoogleMap"></script>
<?php endif; ?>

    <!-- Shared site JavaScript -->
<?php include __DIR__ . '/includes/footer.php'; ?>