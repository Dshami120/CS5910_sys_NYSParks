<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin(['admin']);

$parks = getAllParks();
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><parks></parks>');
foreach ($parks as $park) {
    $parkNode = $xml->addChild('park');
    foreach (['park_name','region','park_type','address','city','state','zip_code','hours','amenities','summary','image_url','latitude','longitude'] as $field) {
        $parkNode->addChild($field, htmlspecialchars((string) ($park[$field] ?? '')));
    }
}
header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="parks-export.xml"');
echo $xml->asXML();
