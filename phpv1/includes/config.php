<?php
/*
 |--------------------------------------------------------------------------
 | Global site settings
 |--------------------------------------------------------------------------
 | Keep all simple project-wide settings in one place.
 | This makes it easier to update names, URLs, and database credentials later.
 */

// Basic site information used in the page title, navbar brand, and footer.
define('SITE_NAME', 'NYS Parks & Recreation');
define('SITE_TAGLINE', 'Explore parks, events, and outdoor experiences across New York State.');
define('BASE_URL', '/nys-parks-homepage');

// Database settings for XAMPP.
// Update only these values if your local MySQL setup is different.
define('DB_HOST', 'localhost');
define('DB_NAME', 'nys_parks');
define('DB_USER', 'root');
define('DB_PASS', '');

// Friendly default timezone for formatting dates on the site.
date_default_timezone_set('America/New_York');
