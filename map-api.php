<?php
// Google Maps JavaScript API key for the public map page.
// Replace the placeholder with your actual key after creating one in Google Cloud Console.
define('GOOGLE_MAPS_API_KEY', 'PASTE_YOUR_GOOGLE_MAPS_API_KEY_HERE');//<!--AIzaSyA-liQXGjeUwcrDMyq3m_vMmBtqA9nL21Y-->

function has_google_maps_api_key(): bool
{
    return GOOGLE_MAPS_API_KEY !== '' && GOOGLE_MAPS_API_KEY !== 'PASTE_YOUR_GOOGLE_MAPS_API_KEY_HERE';//<!--AIzaSyA-liQXGjeUwcrDMyq3m_vMmBtqA9nL21Y-->
}
