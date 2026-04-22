<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin(['admin']);

$events = getAllPublicEvents();
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><events></events>');
foreach ($events as $event) {
    $eventNode = $xml->addChild('event');
    foreach (['title','park_name','category','event_type','start_datetime','end_datetime','event_status','description','image_url'] as $field) {
        $eventNode->addChild($field, htmlspecialchars((string) ($event[$field] ?? '')));
    }
}
header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="events-export.xml"');
echo $xml->asXML();
