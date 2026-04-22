<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
requireLogin(['admin']);

$bookings = getAdminBookingRequests();
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><bookings></bookings>');
foreach ($bookings as $booking) {
    $node = $xml->addChild('booking');
    foreach (['booking_id','title','client_name','client_email','park_name','field_name','start_datetime','end_datetime','guest_count','booking_status','reservation_fee','special_requests','created_at','decision_date'] as $field) {
        $node->addChild($field, htmlspecialchars((string) ($booking[$field] ?? '')));
    }
}
header('Content-Type: application/xml; charset=utf-8');
header('Content-Disposition: attachment; filename="bookings-export.xml"');
echo $xml->asXML();
