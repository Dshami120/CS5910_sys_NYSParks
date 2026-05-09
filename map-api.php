<?php
// Configure Google Maps for the public map page.
define('GOOGLE_MAPS_API_KEY', getenv('GOOGLE_MAPS_API_KEY') ?: 'PASTE_YOUR_GOOGLE_MAPS_API_KEY_HERE'); //AIzaSyA-liQXGjeUwcrDMyq3m_vMmBtqA9nL21Y

// Check whether live Google Maps is available.
function has_google_maps_api_key(): bool
{
    return GOOGLE_MAPS_API_KEY !== '' && GOOGLE_MAPS_API_KEY !== 'PASTE_YOUR_GOOGLE_MAPS_API_KEY_HERE';
}
