<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireLogin(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectTo('../admin-xml.php');
}

if (!extension_loaded('simplexml')) {
    setFlashMessage('danger', 'SimpleXML is not enabled in this PHP environment.');
    redirectTo('../admin-xml.php');
}

if (empty($_FILES['parks_xml']['tmp_name']) || !is_uploaded_file($_FILES['parks_xml']['tmp_name'])) {
    setFlashMessage('danger', 'Please choose an XML file to upload.');
    redirectTo('../admin-xml.php');
}

$db = getDbConnection();
if (!$db || !tableExists($db, 'Parks')) {
    setFlashMessage('danger', 'The Parks table is not ready yet. Import the SQL files first.');
    redirectTo('../admin-xml.php');
}

$xml = @simplexml_load_file($_FILES['parks_xml']['tmp_name']);
if ($xml === false || !isset($xml->park)) {
    setFlashMessage('danger', 'The XML file could not be read or does not use the expected <parks><park> format.');
    redirectTo('../admin-xml.php');
}

$sql = "INSERT INTO Parks (park_name, region, park_type, address, city, state, zip_code, hours, amenities, summary, image_url, latitude, longitude, total_fields, max_capacity)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 100)
        ON DUPLICATE KEY UPDATE
            region = VALUES(region),
            park_type = VALUES(park_type),
            address = VALUES(address),
            city = VALUES(city),
            state = VALUES(state),
            zip_code = VALUES(zip_code),
            hours = VALUES(hours),
            amenities = VALUES(amenities),
            summary = VALUES(summary),
            image_url = VALUES(image_url),
            latitude = VALUES(latitude),
            longitude = VALUES(longitude)";
$stmt = $db->prepare($sql);
if (!$stmt) {
    setFlashMessage('danger', 'Could not prepare the park import query.');
    redirectTo('../admin-xml.php');
}

$count = 0;
foreach ($xml->park as $park) {
    $parkName = trim((string) ($park->park_name ?? ''));
    $region = trim((string) ($park->region ?? ''));
    $parkType = trim((string) ($park->park_type ?? 'Nature'));
    $address = trim((string) ($park->address ?? ''));
    $city = trim((string) ($park->city ?? ''));
    $state = strtoupper(trim((string) ($park->state ?? 'NY')));
    $zip = trim((string) ($park->zip_code ?? '00000'));
    $hours = trim((string) ($park->hours ?? 'Open Daily'));
    $amenities = trim((string) ($park->amenities ?? ''));
    $summary = trim((string) ($park->summary ?? ''));
    $imageUrl = trim((string) ($park->image_url ?? ''));
    $latitude = strlen(trim((string) ($park->latitude ?? ''))) ? (float) $park->latitude : null;
    $longitude = strlen(trim((string) ($park->longitude ?? ''))) ? (float) $park->longitude : null;

    if ($parkName === '' || $region === '' || $address === '' || $city === '') {
        continue;
    }

    $stmt->bind_param('sssssssssssdd', $parkName, $region, $parkType, $address, $city, $state, $zip, $hours, $amenities, $summary, $imageUrl, $latitude, $longitude);
    if ($stmt->execute()) {
        $count++;
    }
}
$stmt->close();
setFlashMessage('success', "Imported or updated {$count} park records from XML.");
redirectTo('../admin-xml.php');
