<?php
require_once __DIR__ . '/db.php';

/*
 |--------------------------------------------------------------------------
 | Homepage fallback data
 |--------------------------------------------------------------------------
 | This data is used when:
 | 1. The database is not connected yet
 | 2. The Parks / Events tables are empty
 | 3. You want the page to look good immediately in class demos
 */
function getFallbackParks(): array
{
    return [
        [
            'park_name' => 'Letchworth State Park',
            'region' => 'Finger Lakes',
            'city' => 'Mount Morris',
            'state' => 'NY',
            'amenities' => 'Waterfalls, Hiking, Scenic Views',
            'image_url' => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
            'badge' => 'Featured',
        ],
        [
            'park_name' => 'Jones Beach State Park',
            'region' => 'Long Island',
            'city' => 'Wantagh',
            'state' => 'NY',
            'amenities' => 'Beach, Boardwalk, Concerts',
            'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
            'badge' => 'Popular',
        ],
        [
            'park_name' => 'Watkins Glen State Park',
            'region' => 'Southern Tier',
            'city' => 'Watkins Glen',
            'state' => 'NY',
            'amenities' => 'Gorge Trails, Waterfalls, Photography',
            'image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80',
            'badge' => 'Top Trails',
        ],
    ];
}

function getFallbackEvents(): array
{
    return [
        [
            'title' => 'Sunrise Yoga at the Park',
            'park_name' => 'Jones Beach State Park',
            'start_datetime' => date('Y-m-d 08:00:00', strtotime('+7 days')),
            'description' => 'Join us for a peaceful guided yoga session near the water.',
            'event_type' => 'public',
        ],
        [
            'title' => 'Nature Photography Walk',
            'park_name' => 'Watkins Glen State Park',
            'start_datetime' => date('Y-m-d 10:00:00', strtotime('+12 days')),
            'description' => 'Explore scenic trails and learn tips for photographing waterfalls and landscapes.',
            'event_type' => 'public',
        ],
        [
            'title' => 'Family Picnic & Ranger Talk',
            'park_name' => 'Letchworth State Park',
            'start_datetime' => date('Y-m-d 13:00:00', strtotime('+18 days')),
            'description' => 'Bring the family for an afternoon picnic followed by a short ranger-led talk.',
            'event_type' => 'public',
        ],
    ];
}

/*
 |--------------------------------------------------------------------------
 | Featured parks for the homepage
 |--------------------------------------------------------------------------
 | Pull data from the real Parks table if available.
 | Otherwise use the fallback demo data above.
 */
function getFeaturedParks(int $limit = 3): array
{
    $db = getDbConnection();

    if ($db && tableExists($db, 'Parks')) {
        $sql = "SELECT park_name, region, city, state, amenities
                FROM Parks
                ORDER BY park_name ASC
                LIMIT ?";

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $parks = [];

            $defaultImages = [
                'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80',
            ];

            $defaultBadges = ['Featured', 'Popular', 'Top Pick'];
            $i = 0;

            while ($row = $result->fetch_assoc()) {
                $row['image_url'] = $defaultImages[$i % count($defaultImages)];
                $row['badge'] = $defaultBadges[$i % count($defaultBadges)];
                $parks[] = $row;
                $i++;
            }

            $stmt->close();

            if (!empty($parks)) {
                return $parks;
            }
        }
    }

    return array_slice(getFallbackParks(), 0, $limit);
}

/*
 |--------------------------------------------------------------------------
 | Upcoming events for the homepage
 |--------------------------------------------------------------------------
 | The provided Events SQL includes title, description, event_type,
 | start_datetime, end_datetime, and park_id. We join Parks so the homepage
 | can show the event location in a simple card.
 */
function getUpcomingEvents(int $limit = 3): array
{
    $db = getDbConnection();

    if ($db && tableExists($db, 'Events') && tableExists($db, 'Parks')) {
        $sql = "SELECT e.title,
                       e.description,
                       e.event_type,
                       e.start_datetime,
                       p.park_name
                FROM Events e
                INNER JOIN Parks p ON e.park_id = p.park_id
                WHERE e.event_type = 'public'
                  AND e.event_status IN ('published', 'draft', 'closed', 'completed')
                ORDER BY e.start_datetime ASC
                LIMIT ?";

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('i', $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            $events = [];

            while ($row = $result->fetch_assoc()) {
                $events[] = $row;
            }

            $stmt->close();

            if (!empty($events)) {
                return $events;
            }
        }
    }

    return array_slice(getFallbackEvents(), 0, $limit);
}

/*
 |--------------------------------------------------------------------------
 | Tiny formatting helper for cleaner output in the view.
 |--------------------------------------------------------------------------
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}


/*
 |--------------------------------------------------------------------------
 | Full parks directory fallback data
 |--------------------------------------------------------------------------
 | Used on parks.php when the database is not ready yet.
 | Each item mirrors the kind of data we expect from the Parks table.
 */
function getFallbackParkDirectory(): array
{
    return [
        [
            'park_name' => 'Letchworth State Park',
            'region' => 'Finger Lakes',
            'address' => '1 Letchworth State Park',
            'city' => 'Mount Morris',
            'state' => 'NY',
            'zip_code' => '14510',
            'hours' => '6:00 AM - 11:00 PM',
            'amenities' => 'Waterfalls, Trails, Camping, Scenic Overlooks',
            'latitude' => '42.6461',
            'longitude' => '-77.9817',
            'park_type' => 'Nature',
            'image_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
            'summary' => 'Known as the Grand Canyon of the East, famous for waterfalls and gorge views.',
        ],
        [
            'park_name' => 'Jones Beach State Park',
            'region' => 'Long Island',
            'address' => '1 Ocean Parkway',
            'city' => 'Wantagh',
            'state' => 'NY',
            'zip_code' => '11793',
            'hours' => '6:00 AM - Sunset',
            'amenities' => 'Beach, Boardwalk, Concerts, Fishing',
            'latitude' => '40.5968',
            'longitude' => '-73.5085',
            'park_type' => 'Beach',
            'image_url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
            'summary' => 'A classic oceanfront destination with beaches, events, and family attractions.',
        ],
        [
            'park_name' => 'Watkins Glen State Park',
            'region' => 'Southern Tier',
            'address' => '1009 N Franklin St',
            'city' => 'Watkins Glen',
            'state' => 'NY',
            'zip_code' => '14891',
            'hours' => '8:00 AM - Dusk',
            'amenities' => 'Gorge Trail, Waterfalls, Photography, Picnics',
            'latitude' => '42.3809',
            'longitude' => '-76.8730',
            'park_type' => 'Nature',
            'image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80',
            'summary' => 'A dramatic gorge park with stone bridges, trails, and famous waterfalls.',
        ],
        [
            'park_name' => 'Bear Mountain State Park',
            'region' => 'Hudson Valley',
            'address' => '3006 Seven Lakes Dr',
            'city' => 'Bear Mountain',
            'state' => 'NY',
            'zip_code' => '10911',
            'hours' => '6:00 AM - 9:00 PM',
            'amenities' => 'Hiking, Zoo, Picnic Areas, Scenic Drives',
            'latitude' => '41.3129',
            'longitude' => '-73.9882',
            'park_type' => 'Mountain',
            'image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
            'summary' => 'A Hudson Valley favorite with trails, river views, and year-round recreation.',
        ],
        [
            'park_name' => 'Saratoga Spa State Park',
            'region' => 'Capital Region',
            'address' => '19 Roosevelt Dr',
            'city' => 'Saratoga Springs',
            'state' => 'NY',
            'zip_code' => '12866',
            'hours' => '6:00 AM - 10:00 PM',
            'amenities' => 'Pools, Golf, Performing Arts, Trails',
            'latitude' => '43.0327',
            'longitude' => '-73.7679',
            'park_type' => 'Family',
            'image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
            'summary' => 'A cultural and recreation destination with trails, mineral springs, and pools.',
        ],
        [
            'park_name' => 'Niagara Falls State Park',
            'region' => 'Western New York',
            'address' => '332 Prospect St',
            'city' => 'Niagara Falls',
            'state' => 'NY',
            'zip_code' => '14303',
            'hours' => 'Open Daily',
            'amenities' => 'Waterfalls, Observation Areas, Tours, Family Attractions',
            'latitude' => '43.0962',
            'longitude' => '-79.0377',
            'park_type' => 'Landmark',
            'image_url' => 'https://images.unsplash.com/photo-1482192596544-9eb780fc7f66?auto=format&fit=crop&w=1200&q=80',
            'summary' => 'A world-famous state park anchored by iconic waterfalls and visitor experiences.',
        ],
    ];
}

/*
 |--------------------------------------------------------------------------
 | Parks directory page data
 |--------------------------------------------------------------------------
 | Returns all parks for the public parks listing page.
 | We intentionally keep the SQL simple for a capstone project.
 */
function getAllParks(): array
{
    $db = getDbConnection();

    if ($db && tableExists($db, 'Parks')) {
        $sql = "SELECT park_name, region, address, city, state, zip_code, hours, amenities, latitude, longitude
                FROM Parks
                ORDER BY park_name ASC";

        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            $parks = [];
            $defaultImages = [
                'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1482192596544-9eb780fc7f66?auto=format&fit=crop&w=1200&q=80',
            ];
            $defaultTypes = ['Nature', 'Beach', 'Mountain', 'Family', 'Landmark', 'Waterfront'];
            $i = 0;

            while ($row = $result->fetch_assoc()) {
                $row['image_url'] = $defaultImages[$i % count($defaultImages)];
                $row['park_type'] = $defaultTypes[$i % count($defaultTypes)];
                $row['summary'] = !empty($row['amenities'])
                    ? 'Amenities include ' . $row['amenities'] . '.'
                    : 'Explore trails, views, and outdoor recreation across this NYS park.';
                $parks[] = $row;
                $i++;
            }

            if (!empty($parks)) {
                return $parks;
            }
        }
    }

    return getFallbackParkDirectory();
}

/*
 |--------------------------------------------------------------------------
 | Distinct regions helper
 |--------------------------------------------------------------------------
 | Useful for a simple filter dropdown on the parks page.
 */
function getParkRegions(array $parks): array
{
    $regions = [];

    foreach ($parks as $park) {
        if (!empty($park['region'])) {
            $regions[] = $park['region'];
        }
    }

    $regions = array_values(array_unique($regions));
    sort($regions);

    return $regions;
}


/*
 |--------------------------------------------------------------------------
 | Full public events directory fallback data
 |--------------------------------------------------------------------------
 | Used on events.php when the database is not ready yet.
 | We keep these records simple and close to the Events table structure.
 */
function getFallbackEventDirectory(): array
{
    return [
        [
            'event_id' => 1,
            'title' => 'Sunrise Yoga on the Shore',
            'description' => 'Start the morning with a guided yoga session near the water. Great for beginners, families, and weekend visitors.',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_datetime' => date('Y-m-d 08:00:00', strtotime('+7 days')),
            'end_datetime' => date('Y-m-d 09:30:00', strtotime('+7 days')),
            'park_name' => 'Jones Beach State Park',
            'region' => 'Long Island',
            'city' => 'Wantagh',
            'category' => 'Wellness',
            'image_url' => 'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80',
        ],
        [
            'event_id' => 2,
            'title' => 'Nature Photography Walk',
            'description' => 'Join a ranger-led walk focused on waterfalls, scenic overlooks, and beginner photography tips.',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_datetime' => date('Y-m-d 10:00:00', strtotime('+12 days')),
            'end_datetime' => date('Y-m-d 12:00:00', strtotime('+12 days')),
            'park_name' => 'Watkins Glen State Park',
            'region' => 'Southern Tier',
            'city' => 'Watkins Glen',
            'category' => 'Nature',
            'image_url' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80',
        ],
        [
            'event_id' => 3,
            'title' => 'Family Picnic and Ranger Talk',
            'description' => 'Pack a lunch and enjoy a family-friendly afternoon with games, picnic space, and a short educational ranger talk.',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_datetime' => date('Y-m-d 13:00:00', strtotime('+18 days')),
            'end_datetime' => date('Y-m-d 15:00:00', strtotime('+18 days')),
            'park_name' => 'Letchworth State Park',
            'region' => 'Finger Lakes',
            'city' => 'Mount Morris',
            'category' => 'Family',
            'image_url' => 'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
        ],
        [
            'event_id' => 4,
            'title' => 'Hudson Valley Trail Challenge',
            'description' => 'A guided hiking challenge for visitors looking for a more active outdoor experience with scenic views.',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_datetime' => date('Y-m-d 09:00:00', strtotime('+26 days')),
            'end_datetime' => date('Y-m-d 12:30:00', strtotime('+26 days')),
            'park_name' => 'Bear Mountain State Park',
            'region' => 'Hudson Valley',
            'city' => 'Bear Mountain',
            'category' => 'Hiking',
            'image_url' => 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
        ],
        [
            'event_id' => 5,
            'title' => 'Evening Concert on the Lawn',
            'description' => 'Live music, food vendors, and a relaxing summer atmosphere in one of New York’s most iconic parks.',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_datetime' => date('Y-m-d 18:30:00', strtotime('+34 days')),
            'end_datetime' => date('Y-m-d 20:30:00', strtotime('+34 days')),
            'park_name' => 'Saratoga Spa State Park',
            'region' => 'Capital Region',
            'city' => 'Saratoga Springs',
            'category' => 'Music',
            'image_url' => 'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
        ],
        [
            'event_id' => 6,
            'title' => 'Niagara Falls History Tour',
            'description' => 'Discover the stories, landmarks, and history connected to one of the most famous parks in the state.',
            'event_type' => 'public',
            'event_status' => 'published',
            'start_datetime' => date('Y-m-d 11:00:00', strtotime('+41 days')),
            'end_datetime' => date('Y-m-d 12:30:00', strtotime('+41 days')),
            'park_name' => 'Niagara Falls State Park',
            'region' => 'Western New York',
            'city' => 'Niagara Falls',
            'category' => 'History',
            'image_url' => 'https://images.unsplash.com/photo-1482192596544-9eb780fc7f66?auto=format&fit=crop&w=1200&q=80',
        ],
    ];
}

/*
 |--------------------------------------------------------------------------
 | Tiny helper to infer a public-facing category
 |--------------------------------------------------------------------------
 | The Events table in the class schema does not include a category column,
 | so we derive one from the title when needed.
 */
function deriveEventCategory(string $title): string
{
    $title = strtolower($title);

    if (str_contains($title, 'yoga') || str_contains($title, 'wellness')) {
        return 'Wellness';
    }
    if (str_contains($title, 'music') || str_contains($title, 'concert')) {
        return 'Music';
    }
    if (str_contains($title, 'hike') || str_contains($title, 'trail')) {
        return 'Hiking';
    }
    if (str_contains($title, 'photo')) {
        return 'Nature';
    }
    if (str_contains($title, 'history') || str_contains($title, 'tour')) {
        return 'History';
    }
    if (str_contains($title, 'family') || str_contains($title, 'picnic')) {
        return 'Family';
    }

    return 'Outdoor';
}

/*
 |--------------------------------------------------------------------------
 | Public events directory page data
 |--------------------------------------------------------------------------
 | Reads public events from Events and joins Parks for display details.
 | Falls back to demo events when MySQL is not ready.
 */
function getAllPublicEvents(): array
{
    $db = getDbConnection();

    if ($db && tableExists($db, 'Events') && tableExists($db, 'Parks')) {
        $sql = "SELECT e.event_id,
                       e.title,
                       e.description,
                       e.event_type,
                       e.event_status,
                       e.start_datetime,
                       e.end_datetime,
                       p.park_name,
                       p.region,
                       p.city
                FROM Events e
                INNER JOIN Parks p ON e.park_id = p.park_id
                WHERE e.event_type = 'public'
                ORDER BY e.start_datetime ASC, e.title ASC";

        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            $events = [];
            $defaultImages = [
                'https://images.unsplash.com/photo-1506126613408-eca07ce68773?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1501785888041-af3ef285b470?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1506157786151-b8491531f063?auto=format&fit=crop&w=1200&q=80',
                'https://images.unsplash.com/photo-1482192596544-9eb780fc7f66?auto=format&fit=crop&w=1200&q=80',
            ];
            $i = 0;

            while ($row = $result->fetch_assoc()) {
                $row['category'] = deriveEventCategory($row['title'] ?? '');
                $row['image_url'] = $defaultImages[$i % count($defaultImages)];
                $events[] = $row;
                $i++;
            }

            if (!empty($events)) {
                return $events;
            }
        }
    }

    return getFallbackEventDirectory();
}

/*
 |--------------------------------------------------------------------------
 | Distinct park names for the events filter dropdown
 |--------------------------------------------------------------------------
 */
function getEventParkNames(array $events): array
{
    $parks = [];

    foreach ($events as $event) {
        if (!empty($event['park_name'])) {
            $parks[] = $event['park_name'];
        }
    }

    $parks = array_values(array_unique($parks));
    sort($parks);

    return $parks;
}

/*
 |--------------------------------------------------------------------------
 | Distinct event categories for the events filter dropdown
 |--------------------------------------------------------------------------
 */
function getEventCategories(array $events): array
{
    $categories = [];

    foreach ($events as $event) {
        if (!empty($event['category'])) {
            $categories[] = $event['category'];
        }
    }

    $categories = array_values(array_unique($categories));
    sort($categories);

    return $categories;
}

/*
 |--------------------------------------------------------------------------
 | Distinct event months for the events filter dropdown
 |--------------------------------------------------------------------------
 | Returns values in Y-m format so filtering is easy in JavaScript.
 */
function getEventMonths(array $events): array
{
    $months = [];

    foreach ($events as $event) {
        if (!empty($event['start_datetime'])) {
            $value = date('Y-m', strtotime($event['start_datetime']));
            $label = date('F Y', strtotime($event['start_datetime']));
            $months[$value] = $label;
        }
    }

    ksort($months);

    return $months;
}


/*
 |--------------------------------------------------------------------------
 | Client portal fallback data
 |--------------------------------------------------------------------------
 | These records make the client portal presentable even before the live
 | Bookings / Fields workflow is fully wired into MySQL.
 */
function getFallbackClientBookings(array $user): array
{
    $userName = trim(($user['first_name'] ?? 'Client') . ' ' . ($user['last_name'] ?? '')); 

    return [
        [
            'booking_id' => 5001,
            'title' => 'Birthday Picnic Reservation',
            'park_name' => 'Letchworth State Park',
            'field_name' => 'North Picnic Field',
            'start_datetime' => date('Y-m-d 11:00:00', strtotime('+10 days')),
            'end_datetime' => date('Y-m-d 15:00:00', strtotime('+10 days')),
            'guest_count' => 30,
            'booking_status' => 'approved',
            'reservation_fee' => '125.00',
            'special_requests' => 'Need two nearby picnic tables and accessible parking information.',
            'attendee_email' => $user['email'] ?? 'client@example.com',
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
        ],
        [
            'booking_id' => 5002,
            'title' => 'Community Wellness Meetup',
            'park_name' => 'Jones Beach State Park',
            'field_name' => 'Ocean Lawn',
            'start_datetime' => date('Y-m-d 09:00:00', strtotime('+21 days')),
            'end_datetime' => date('Y-m-d 12:00:00', strtotime('+21 days')),
            'guest_count' => 45,
            'booking_status' => 'pending',
            'reservation_fee' => '0.00',
            'special_requests' => 'Client contact: ' . $userName . '. Please review outdoor sound policy.',
            'attendee_email' => $user['email'] ?? 'client@example.com',
            'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [
            'booking_id' => 5003,
            'title' => 'Family Reunion Field Day',
            'park_name' => 'Bear Mountain State Park',
            'field_name' => 'Lakeside Activity Field',
            'start_datetime' => date('Y-m-d 10:00:00', strtotime('-18 days')),
            'end_datetime' => date('Y-m-d 14:00:00', strtotime('-18 days')),
            'guest_count' => 60,
            'booking_status' => 'confirmed',
            'reservation_fee' => '175.00',
            'special_requests' => 'Completed family reunion event used for dashboard history preview.',
            'attendee_email' => $user['email'] ?? 'client@example.com',
            'created_at' => date('Y-m-d H:i:s', strtotime('-35 days')),
        ],
    ];
}

function getFallbackFieldDirectory(): array
{
    return [
        ['field_id' => 101, 'park_name' => 'Letchworth State Park', 'park_id' => 1, 'field_name' => 'North Picnic Field', 'field_type' => 'Picnic', 'capacity' => 80],
        ['field_id' => 102, 'park_name' => 'Letchworth State Park', 'park_id' => 1, 'field_name' => 'Falls Overlook Lawn', 'field_type' => 'Event Lawn', 'capacity' => 120],
        ['field_id' => 201, 'park_name' => 'Jones Beach State Park', 'park_id' => 2, 'field_name' => 'Ocean Lawn', 'field_type' => 'Open Lawn', 'capacity' => 150],
        ['field_id' => 202, 'park_name' => 'Jones Beach State Park', 'park_id' => 2, 'field_name' => 'Boardwalk Pavilion Area', 'field_type' => 'Pavilion', 'capacity' => 100],
        ['field_id' => 301, 'park_name' => 'Bear Mountain State Park', 'park_id' => 4, 'field_name' => 'Lakeside Activity Field', 'field_type' => 'Activity Field', 'capacity' => 90],
        ['field_id' => 302, 'park_name' => 'Bear Mountain State Park', 'park_id' => 4, 'field_name' => 'Scenic Grove Picnic Area', 'field_type' => 'Picnic', 'capacity' => 70],
        ['field_id' => 401, 'park_name' => 'Watkins Glen State Park', 'park_id' => 3, 'field_name' => 'Gorge Meadow', 'field_type' => 'Meadow', 'capacity' => 60],
        ['field_id' => 501, 'park_name' => 'Saratoga Spa State Park', 'park_id' => 5, 'field_name' => 'Performing Arts Lawn', 'field_type' => 'Event Lawn', 'capacity' => 140],
        ['field_id' => 601, 'park_name' => 'Niagara Falls State Park', 'park_id' => 6, 'field_name' => 'Observation Picnic Terrace', 'field_type' => 'Terrace', 'capacity' => 50],
    ];
}

function getBookableParks(): array
{
    $parks = getAllParks();
    $result = [];
    $i = 1;

    foreach ($parks as $park) {
        $result[] = [
            'park_id' => $park['park_id'] ?? $i,
            'park_name' => $park['park_name'],
            'region' => $park['region'] ?? '',
            'city' => $park['city'] ?? '',
        ];
        $i++;
    }

    return $result;
}

function getBookableFields(): array
{
    $db = getDbConnection();

    if ($db && tableExists($db, 'Fields')) {
        $hasFieldType = tableHasColumn($db, 'Fields', 'field_type');
        $sql = "SELECT f.field_id, f.park_id, f.field_name, " . ($hasFieldType ? "f.field_type" : "'' AS field_type") . ", f.capacity, p.park_name
                FROM Fields f
                INNER JOIN Parks p ON f.park_id = p.park_id
                ORDER BY p.park_name ASC, f.field_name ASC";

        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            $fields = [];
            while ($row = $result->fetch_assoc()) {
                $fields[] = $row;
            }
            return $fields;
        }
    }

    return getFallbackFieldDirectory();
}

function getClientDashboardBookings(array $user): array
{
    $db = getDbConnection();
    $bookings = [];

    if ($db && tableExists($db, 'Bookings')) {
        $hasParkId = tableHasColumn($db, 'Bookings', 'park_id');
        $hasCreatedAt = tableHasColumn($db, 'Bookings', 'created_at');
        $hasReservationFee = tableHasColumn($db, 'Bookings', 'reservation_fee');
        $hasSpecialRequests = tableHasColumn($db, 'Bookings', 'special_requests');

        $sql = "SELECT b.booking_id,
                       b.user_id,
                       b.event_id,
                       b.field_id,
                       b.attendee_email,
                       b.start_datetime,
                       b.end_datetime,
                       b.guest_count,
                       b.booking_status,
                       " . ($hasReservationFee ? "b.reservation_fee" : "0.00 AS reservation_fee") . ",
                       " . ($hasSpecialRequests ? "b.special_requests" : "'' AS special_requests") . ",
                       " . ($hasCreatedAt ? "b.created_at" : "b.start_datetime AS created_at") . ",
                       COALESCE(e.title, 'Reservation Request') AS title,
                       COALESCE(p.park_name, p2.park_name, 'NYS Park') AS park_name,
                       COALESCE(f.field_name, 'Reserved Area') AS field_name
                FROM Bookings b
                LEFT JOIN Events e ON b.event_id = e.event_id
                LEFT JOIN Fields f ON b.field_id = f.field_id
                LEFT JOIN Parks p ON f.park_id = p.park_id
                " . ($hasParkId ? "LEFT JOIN Parks p2 ON b.park_id = p2.park_id" : "LEFT JOIN Parks p2 ON 1 = 0") . "
                WHERE b.user_id = ?
                ORDER BY b.start_datetime DESC";

        $stmt = $db->prepare($sql);
        if ($stmt) {
            $userId = (int) ($user['user_id'] ?? 0);
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($result && ($row = $result->fetch_assoc())) {
                $bookings[] = $row;
            }
            $stmt->close();
        }
    }

    $sessionBookings = $_SESSION['demo_client_requests'][$user['email'] ?? ''] ?? [];

    if (empty($bookings)) {
        $bookings = getFallbackClientBookings($user);
    }

    if (!empty($sessionBookings)) {
        $bookings = array_merge($sessionBookings, $bookings);
    }

    usort($bookings, function ($a, $b) {
        return strtotime($b['start_datetime']) <=> strtotime($a['start_datetime']);
    });

    return $bookings;
}

function getClientBookingStats(array $bookings): array
{
    $stats = [
        'total' => count($bookings),
        'pending' => 0,
        'approved' => 0,
        'confirmed' => 0,
        'next_up' => null,
    ];

    foreach ($bookings as $booking) {
        $status = strtolower($booking['booking_status'] ?? 'pending');
        if ($status === 'pending') {
            $stats['pending']++;
        }
        if ($status === 'approved') {
            $stats['approved']++;
        }
        if ($status === 'confirmed') {
            $stats['confirmed']++;
        }

        $start = strtotime($booking['start_datetime'] ?? '');
        if ($start && $start >= time()) {
            if ($stats['next_up'] === null || $start < strtotime($stats['next_up']['start_datetime'])) {
                $stats['next_up'] = $booking;
            }
        }
    }

    return $stats;
}

function getBookingStatusBadgeClass(string $status): string
{
    return match (strtolower($status)) {
        'approved' => 'success',
        'confirmed' => 'primary',
        'denied' => 'danger',
        'cancelled' => 'secondary',
        'expired' => 'dark',
        default => 'warning',
    };
}

function createClientEventRequest(array $user, array $formData): array
{
    $title = trim($formData['title'] ?? '');
    $description = trim($formData['description'] ?? '');
    $parkId = (int) ($formData['park_id'] ?? 0);
    $fieldId = (int) ($formData['field_id'] ?? 0);
    $eventDate = trim($formData['event_date'] ?? '');
    $startTime = trim($formData['start_time'] ?? '');
    $endTime = trim($formData['end_time'] ?? '');
    $guestCount = (int) ($formData['guest_count'] ?? 0);
    $attendeeEmail = strtolower(trim($formData['attendee_email'] ?? ''));
    $specialRequests = trim($formData['special_requests'] ?? '');

    if ($title === '' || $description === '' || $parkId <= 0 || $fieldId <= 0 || $eventDate === '' || $startTime === '' || $endTime === '' || $guestCount < 1 || !filter_var($attendeeEmail, FILTER_VALIDATE_EMAIL)) {
        return [
            'success' => false,
            'message' => 'Please complete all required event request fields with valid information.',
        ];
    }

    $startDateTime = date('Y-m-d H:i:s', strtotime($eventDate . ' ' . $startTime));
    $endDateTime = date('Y-m-d H:i:s', strtotime($eventDate . ' ' . $endTime));

    if (strtotime($startDateTime) >= strtotime($endDateTime)) {
        return [
            'success' => false,
            'message' => 'The start time must be earlier than the end time.',
        ];
    }

    $parks = getBookableParks();
    $fields = getBookableFields();
    $parkLookup = [];
    $fieldLookup = [];

    foreach ($parks as $park) {
        $parkLookup[(int) $park['park_id']] = $park;
    }
    foreach ($fields as $field) {
        $fieldLookup[(int) $field['field_id']] = $field;
    }

    if (!isset($parkLookup[$parkId]) || !isset($fieldLookup[$fieldId])) {
        return [
            'success' => false,
            'message' => 'Please choose a valid park and field combination.',
        ];
    }

    $selectedField = $fieldLookup[$fieldId];
    if ((int) ($selectedField['park_id'] ?? 0) !== $parkId) {
        return [
            'success' => false,
            'message' => 'The selected field does not belong to the selected park.',
        ];
    }

    if ($guestCount > (int) ($selectedField['capacity'] ?? 0) && (int) ($selectedField['capacity'] ?? 0) > 0) {
        return [
            'success' => false,
            'message' => 'Guest count cannot exceed the selected field capacity.',
        ];
    }

    $db = getDbConnection();
    $usedDemoMode = true;

    if ($db && tableExists($db, 'Events') && tableExists($db, 'Bookings')) {
        try {
            $db->begin_transaction();

            $eventSql = "INSERT INTO Events (park_id, field_id, booking_id, title, description, event_type, start_datetime, end_datetime, event_status, created_by)
                         VALUES (?, ?, NULL, ?, ?, 'private', ?, ?, 'draft', ?)";
            $eventStmt = $db->prepare($eventSql);

            if ($eventStmt) {
                $createdBy = (int) ($user['user_id'] ?? 0);
                $eventStmt->bind_param('iissssi', $parkId, $fieldId, $title, $description, $startDateTime, $endDateTime, $createdBy);
                $eventInsertSuccess = $eventStmt->execute();
                $eventId = (int) $db->insert_id;
                $eventStmt->close();

                if ($eventInsertSuccess && $eventId > 0) {
                    $bookingColumns = ['user_id', 'event_id', 'field_id', 'attendee_email', 'start_datetime', 'end_datetime', 'guest_count', 'booking_status'];
                    $bookingValues = [(int) ($user['user_id'] ?? 0), $eventId, $fieldId, $attendeeEmail, $startDateTime, $endDateTime, $guestCount, 'pending'];
                    $types = 'iiisssis';

                    if (tableHasColumn($db, 'Bookings', 'special_requests')) {
                        $bookingColumns[] = 'special_requests';
                        $bookingValues[] = $specialRequests;
                        $types .= 's';
                    }

                    if (tableHasColumn($db, 'Bookings', 'reservation_fee')) {
                        $bookingColumns[] = 'reservation_fee';
                        $bookingValues[] = '0.00';
                        $types .= 's';
                    }

                    if (tableHasColumn($db, 'Bookings', 'park_id')) {
                        $bookingColumns[] = 'park_id';
                        $bookingValues[] = $parkId;
                        $types .= 'i';
                    }

                    $placeholders = implode(', ', array_fill(0, count($bookingColumns), '?'));
                    $bookingSql = "INSERT INTO Bookings (" . implode(', ', $bookingColumns) . ") VALUES (" . $placeholders . ")";
                    $bookingStmt = $db->prepare($bookingSql);

                    if ($bookingStmt) {
                        $bookingStmt->bind_param($types, ...$bookingValues);
                        $bookingInsertSuccess = $bookingStmt->execute();
                        $bookingId = (int) $db->insert_id;
                        $bookingStmt->close();

                        if ($bookingInsertSuccess && $bookingId > 0) {
                            if (tableHasColumn($db, 'Events', 'booking_id')) {
                                $updateStmt = $db->prepare("UPDATE Events SET booking_id = ? WHERE event_id = ?");
                                if ($updateStmt) {
                                    $updateStmt->bind_param('ii', $bookingId, $eventId);
                                    $updateStmt->execute();
                                    $updateStmt->close();
                                }
                            }

                            $db->commit();
                            $usedDemoMode = false;
                        } else {
                            $db->rollback();
                        }
                    } else {
                        $db->rollback();
                    }
                } else {
                    $db->rollback();
                }
            }
        } catch (Throwable $exception) {
            if ($db->errno) {
                $db->rollback();
            }
        }
    }

    $parkName = $parkLookup[$parkId]['park_name'] ?? 'NYS Park';
    $fieldName = $selectedField['field_name'] ?? 'Requested Area';

    $demoBooking = [
        'booking_id' => rand(7000, 9999),
        'title' => $title,
        'park_name' => $parkName,
        'field_name' => $fieldName,
        'start_datetime' => $startDateTime,
        'end_datetime' => $endDateTime,
        'guest_count' => $guestCount,
        'booking_status' => 'pending',
        'reservation_fee' => '0.00',
        'special_requests' => $specialRequests,
        'attendee_email' => $attendeeEmail,
        'created_at' => date('Y-m-d H:i:s'),
    ];

    if ($usedDemoMode) {
        $_SESSION['demo_client_requests'][$user['email'] ?? ''][] = $demoBooking;
    }

    return [
        'success' => true,
        'message' => $usedDemoMode
            ? 'Your event request was saved in demo mode and added to your client dashboard.'
            : 'Your event request was submitted successfully and added to your client dashboard.',
    ];
}

/*
 |--------------------------------------------------------------------------
 | Admin portal fallback data and helpers
 |--------------------------------------------------------------------------
 | The admin portal needs to look complete even before every SQL table is
 | imported into MySQL. These helpers provide:
 | 1. Demo analytics for charts
 | 2. Demo booking request queues
 | 3. Demo PTO requests
 | 4. Demo employee schedules
 | 5. Simple create / update helpers that use the database when available
 |    and session-backed demo mode when the database is not ready yet.
 */
function getFallbackAdminBookings(): array
{
    return [
        [
            'booking_id' => 8101,
            'title' => 'Community Summer Picnic',
            'client_name' => 'Casey Client',
            'client_email' => 'client.demo@nysparks.local',
            'park_name' => 'Letchworth State Park',
            'field_name' => 'North Picnic Field',
            'start_datetime' => date('Y-m-d 12:00:00', strtotime('+5 days')),
            'end_datetime' => date('Y-m-d 16:00:00', strtotime('+5 days')),
            'guest_count' => 35,
            'booking_status' => 'approved',
            'reservation_fee' => '125.00',
            'special_requests' => 'Needs two accessible parking spots and picnic tables.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            'decision_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [
            'booking_id' => 8102,
            'title' => 'Youth Soccer Clinic',
            'client_name' => 'Jordan Fields',
            'client_email' => 'jordan.fields@example.com',
            'park_name' => 'Bear Mountain State Park',
            'field_name' => 'Lakeside Activity Field',
            'start_datetime' => date('Y-m-d 09:00:00', strtotime('+12 days')),
            'end_datetime' => date('Y-m-d 13:00:00', strtotime('+12 days')),
            'guest_count' => 50,
            'booking_status' => 'pending',
            'reservation_fee' => '0.00',
            'special_requests' => 'Please confirm restroom access near the field.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'decision_date' => null,
        ],
        [
            'booking_id' => 8103,
            'title' => 'Beach Wedding Reception',
            'client_name' => 'Morgan Lee',
            'client_email' => 'morgan.lee@example.com',
            'park_name' => 'Jones Beach State Park',
            'field_name' => 'Ocean Lawn',
            'start_datetime' => date('Y-m-d 17:00:00', strtotime('+21 days')),
            'end_datetime' => date('Y-m-d 21:00:00', strtotime('+21 days')),
            'guest_count' => 90,
            'booking_status' => 'confirmed',
            'reservation_fee' => '250.00',
            'special_requests' => 'Approved with evening access extension.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-10 days')),
            'decision_date' => date('Y-m-d H:i:s', strtotime('-7 days')),
        ],
        [
            'booking_id' => 8104,
            'title' => 'Corporate Retreat Field Day',
            'client_name' => 'Avery Stone',
            'client_email' => 'avery.stone@example.com',
            'park_name' => 'Saratoga Spa State Park',
            'field_name' => 'Performing Arts Lawn',
            'start_datetime' => date('Y-m-d 10:00:00', strtotime('+28 days')),
            'end_datetime' => date('Y-m-d 15:00:00', strtotime('+28 days')),
            'guest_count' => 140,
            'booking_status' => 'denied',
            'reservation_fee' => '0.00',
            'special_requests' => 'Denied because requested group size exceeded approved capacity.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            'decision_date' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ],
        [
            'booking_id' => 8105,
            'title' => 'Volunteer Cleanup Meetup',
            'client_name' => 'Taylor Nguyen',
            'client_email' => 'taylor.nguyen@example.com',
            'park_name' => 'Watkins Glen State Park',
            'field_name' => 'Gorge Meadow',
            'start_datetime' => date('Y-m-d 08:00:00', strtotime('+8 days')),
            'end_datetime' => date('Y-m-d 11:00:00', strtotime('+8 days')),
            'guest_count' => 24,
            'booking_status' => 'pending',
            'reservation_fee' => '0.00',
            'special_requests' => 'Volunteer-led event needing ranger approval.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-8 hours')),
            'decision_date' => null,
        ],
        [
            'booking_id' => 8106,
            'title' => 'Family Reunion Picnic',
            'client_name' => 'Robin Perez',
            'client_email' => 'robin.perez@example.com',
            'park_name' => 'Niagara Falls State Park',
            'field_name' => 'Observation Picnic Terrace',
            'start_datetime' => date('Y-m-d 11:00:00', strtotime('+15 days')),
            'end_datetime' => date('Y-m-d 15:00:00', strtotime('+15 days')),
            'guest_count' => 42,
            'booking_status' => 'approved',
            'reservation_fee' => '95.00',
            'special_requests' => 'Waiting on final payment from client.',
            'created_at' => date('Y-m-d H:i:s', strtotime('-5 days')),
            'decision_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
    ];
}

function getFallbackAdminEmployees(): array
{
    return [
        ['user_id' => 1002, 'first_name' => 'Ellis', 'last_name' => 'Employee', 'email' => 'employee.demo@nysparks.local', 'park_name' => 'Letchworth State Park'],
        ['user_id' => 2201, 'first_name' => 'Mia', 'last_name' => 'Torres', 'email' => 'mia.torres@nysparks.local', 'park_name' => 'Jones Beach State Park'],
        ['user_id' => 2202, 'first_name' => 'Noah', 'last_name' => 'Bennett', 'email' => 'noah.bennett@nysparks.local', 'park_name' => 'Bear Mountain State Park'],
        ['user_id' => 2203, 'first_name' => 'Sofia', 'last_name' => 'Clark', 'email' => 'sofia.clark@nysparks.local', 'park_name' => 'Saratoga Spa State Park'],
    ];
}

function getFallbackAdminSchedules(): array
{
    return [
        [
            'schedule_id' => 9101,
            'employee_id' => 1002,
            'employee_name' => 'Ellis Employee',
            'park_name' => 'Letchworth State Park',
            'shift_date' => date('Y-m-d', strtotime('+1 day')),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'schedule_status' => 'scheduled',
            'notes' => 'Visitor support at lower falls trail entrance.',
        ],
        [
            'schedule_id' => 9102,
            'employee_id' => 2201,
            'employee_name' => 'Mia Torres',
            'park_name' => 'Jones Beach State Park',
            'shift_date' => date('Y-m-d', strtotime('+2 days')),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'schedule_status' => 'scheduled',
            'notes' => 'Concert prep support and guest wayfinding.',
        ],
        [
            'schedule_id' => 9103,
            'employee_id' => 2202,
            'employee_name' => 'Noah Bennett',
            'park_name' => 'Bear Mountain State Park',
            'shift_date' => date('Y-m-d', strtotime('+3 days')),
            'start_time' => '07:30:00',
            'end_time' => '15:30:00',
            'schedule_status' => 'scheduled',
            'notes' => 'Field inspection and picnic area support.',
        ],
    ];
}

function getFallbackAdminPtoRequests(): array
{
    return [
        [
            'pto_id' => 7101,
            'employee_id' => 1002,
            'employee_name' => 'Ellis Employee',
            'start_date' => date('Y-m-d', strtotime('+14 days')),
            'end_date' => date('Y-m-d', strtotime('+16 days')),
            'reason' => 'Family travel request.',
            'pto_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'decision_date' => null,
        ],
        [
            'pto_id' => 7102,
            'employee_id' => 2201,
            'employee_name' => 'Mia Torres',
            'start_date' => date('Y-m-d', strtotime('+20 days')),
            'end_date' => date('Y-m-d', strtotime('+21 days')),
            'reason' => 'Medical appointment and recovery day.',
            'pto_status' => 'approved',
            'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
            'decision_date' => date('Y-m-d H:i:s', strtotime('-2 days')),
        ],
        [
            'pto_id' => 7103,
            'employee_id' => 2203,
            'employee_name' => 'Sofia Clark',
            'start_date' => date('Y-m-d', strtotime('+10 days')),
            'end_date' => date('Y-m-d', strtotime('+12 days')),
            'reason' => 'Requested during peak staffing weekend.',
            'pto_status' => 'denied',
            'created_at' => date('Y-m-d H:i:s', strtotime('-6 days')),
            'decision_date' => date('Y-m-d H:i:s', strtotime('-3 days')),
        ],
    ];
}


function getAdminAnalytics(): array
{
    $fallback = getFallbackAdminAnalytics();
    $db = getDbConnection();

    if (!$db || !tableExists($db, 'Analytics_Daily')) {
        return $fallback;
    }

    $ranges = ['7' => 7, '30' => 30, '90' => 90];
    $output = [];

    foreach ($ranges as $key => $daysBack) {
        $siteData = [];
        $parkData = [];

        if ($daysBack === 7) {
            $siteSql = "SELECT metric_date AS label_date,
                               DATE_FORMAT(metric_date, '%a') AS label_text,
                               SUM(site_visits) AS site_visits,
                               SUM(bookings_created) AS bookings_created
                        FROM Analytics_Daily
                        WHERE park_id IS NULL
                          AND metric_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                        GROUP BY metric_date
                        ORDER BY metric_date ASC";
        } elseif ($daysBack === 30) {
            $siteSql = "SELECT MIN(metric_date) AS label_date,
                               CONCAT('Week of ', DATE_FORMAT(MIN(metric_date), '%b %e')) AS label_text,
                               SUM(site_visits) AS site_visits,
                               SUM(bookings_created) AS bookings_created
                        FROM Analytics_Daily
                        WHERE park_id IS NULL
                          AND metric_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                        GROUP BY YEARWEEK(metric_date, 1)
                        ORDER BY MIN(metric_date) ASC";
        } else {
            $siteSql = "SELECT MIN(metric_date) AS label_date,
                               DATE_FORMAT(MIN(metric_date), '%b %Y') AS label_text,
                               SUM(site_visits) AS site_visits,
                               SUM(bookings_created) AS bookings_created
                        FROM Analytics_Daily
                        WHERE park_id IS NULL
                          AND metric_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                        GROUP BY YEAR(metric_date), MONTH(metric_date)
                        ORDER BY MIN(metric_date) ASC";
        }

        $siteStmt = $db->prepare($siteSql);
        if ($siteStmt) {
            $siteStmt->bind_param('i', $daysBack);
            $siteStmt->execute();
            $siteResult = $siteStmt->get_result();
            while ($siteResult && $row = $siteResult->fetch_assoc()) {
                $siteData[] = $row;
            }
            $siteStmt->close();
        }

        $parkSql = "SELECT COALESCE(p.park_name, CONCAT('Park #', a.park_id)) AS park,
                           SUM(a.park_visits) AS visit_count
                    FROM Analytics_Daily a
                    LEFT JOIN Parks p ON a.park_id = p.park_id
                    WHERE a.park_id IS NOT NULL
                      AND a.metric_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                    GROUP BY a.park_id, park
                    ORDER BY visit_count DESC
                    LIMIT 6";
        $parkStmt = $db->prepare($parkSql);
        if ($parkStmt) {
            $parkStmt->bind_param('i', $daysBack);
            $parkStmt->execute();
            $parkResult = $parkStmt->get_result();
            while ($parkResult && $row = $parkResult->fetch_assoc()) {
                $parkData[] = [
                    'park' => $row['park'],
                    'count' => (int) $row['visit_count'],
                ];
            }
            $parkStmt->close();
        }

        if (!empty($siteData)) {
            $output[$key] = [
                'labels' => array_map(fn($row) => $row['label_text'], $siteData),
                'siteTraffic' => array_map(fn($row) => (int) $row['site_visits'], $siteData),
                'bookings' => array_map(fn($row) => (int) $row['bookings_created'], $siteData),
                'parkTraffic' => !empty($parkData) ? $parkData : $fallback[$key]['parkTraffic'],
            ];
        } else {
            $output[$key] = $fallback[$key];
        }
    }

    return $output;
}

function getFallbackAdminAnalytics(): array
{
    return [
        '7' => [
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'siteTraffic' => [520, 610, 590, 640, 720, 910, 860],
            'bookings' => [4, 7, 5, 6, 9, 12, 11],
            'parkTraffic' => [
                ['park' => 'Letchworth', 'count' => 420],
                ['park' => 'Jones Beach', 'count' => 560],
                ['park' => 'Bear Mountain', 'count' => 380],
                ['park' => 'Watkins Glen', 'count' => 290],
            ],
        ],
        '30' => [
            'labels' => ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            'siteTraffic' => [4200, 4600, 5100, 5700],
            'bookings' => [31, 36, 43, 49],
            'parkTraffic' => [
                ['park' => 'Letchworth', 'count' => 2300],
                ['park' => 'Jones Beach', 'count' => 3200],
                ['park' => 'Bear Mountain', 'count' => 1900],
                ['park' => 'Watkins Glen', 'count' => 1700],
                ['park' => 'Saratoga Spa', 'count' => 1400],
            ],
        ],
        '90' => [
            'labels' => ['Month 1', 'Month 2', 'Month 3'],
            'siteTraffic' => [12800, 15100, 16750],
            'bookings' => [102, 118, 134],
            'parkTraffic' => [
                ['park' => 'Letchworth', 'count' => 6700],
                ['park' => 'Jones Beach', 'count' => 9100],
                ['park' => 'Bear Mountain', 'count' => 5400],
                ['park' => 'Watkins Glen', 'count' => 4900],
                ['park' => 'Saratoga Spa', 'count' => 4300],
                ['park' => 'Niagara Falls', 'count' => 7800],
            ],
        ],
    ];
}

function getAdminEmployeeDirectory(): array
{
    $db = getDbConnection();
    if ($db && tableExists($db, 'Users')) {
        $hasParkId = tableHasColumn($db, 'Users', 'park_id');
        $sql = "SELECT u.user_id, u.first_name, u.last_name, u.email, " . ($hasParkId ? "COALESCE(p.park_name, '') AS park_name" : "'' AS park_name") . "
                FROM Users u
                " . ($hasParkId ? "LEFT JOIN Parks p ON u.park_id = p.park_id" : "") . "
                WHERE u.role = 'employee'
                ORDER BY u.last_name ASC, u.first_name ASC";
        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            $employees = [];
            while ($row = $result->fetch_assoc()) {
                $employees[] = $row;
            }
            return $employees;
        }
    }

    return getFallbackAdminEmployees();
}

function getAdminBookingRequests(): array
{
    $db = getDbConnection();
    $bookings = [];

    if ($db && tableExists($db, 'Bookings')) {
        $hasParkId = tableHasColumn($db, 'Bookings', 'park_id');
        $hasReservationFee = tableHasColumn($db, 'Bookings', 'reservation_fee');
        $hasSpecialRequests = tableHasColumn($db, 'Bookings', 'special_requests');
        $hasDecisionDate = tableHasColumn($db, 'Bookings', 'decision_date');

        $sql = "SELECT b.booking_id,
                       b.user_id,
                       b.event_id,
                       b.field_id,
                       b.attendee_email AS client_email,
                       b.start_datetime,
                       b.end_datetime,
                       b.guest_count,
                       b.booking_status,
                       " . ($hasReservationFee ? "b.reservation_fee" : "0.00 AS reservation_fee") . ",
                       " . ($hasSpecialRequests ? "b.special_requests" : "'' AS special_requests") . ",
                       b.created_at,
                       " . ($hasDecisionDate ? "b.decision_date" : "NULL AS decision_date") . ",
                       COALESCE(e.title, 'Reservation Request') AS title,
                       COALESCE(CONCAT(u.first_name, ' ', u.last_name), 'Client User') AS client_name,
                       COALESCE(f.field_name, 'Reserved Area') AS field_name,
                       COALESCE(p.park_name, p2.park_name, 'NYS Park') AS park_name
                FROM Bookings b
                LEFT JOIN Users u ON b.user_id = u.user_id
                LEFT JOIN Events e ON b.event_id = e.event_id
                LEFT JOIN Fields f ON b.field_id = f.field_id
                LEFT JOIN Parks p ON f.park_id = p.park_id
                " . ($hasParkId ? "LEFT JOIN Parks p2 ON b.park_id = p2.park_id" : "LEFT JOIN Parks p2 ON 1 = 0") . "
                ORDER BY b.start_datetime ASC";

        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $bookings[] = $row;
            }
        }
    }

    if (empty($bookings)) {
        $bookings = getFallbackAdminBookings();
    }

    if (!empty($_SESSION['demo_client_requests']) && is_array($_SESSION['demo_client_requests'])) {
        foreach ($_SESSION['demo_client_requests'] as $email => $requests) {
            foreach ($requests as $request) {
                $request['client_name'] = $request['client_name'] ?? 'Demo Client';
                $request['client_email'] = $request['attendee_email'] ?? $email;
                $bookings[] = $request;
            }
        }
    }

    if (!empty($_SESSION['demo_admin_bookings']) && is_array($_SESSION['demo_admin_bookings'])) {
        $overrides = [];
        foreach ($_SESSION['demo_admin_bookings'] as $booking) {
            $overrides[$booking['booking_id']] = $booking;
        }
        foreach ($bookings as &$booking) {
            if (isset($overrides[$booking['booking_id']])) {
                $booking = array_merge($booking, $overrides[$booking['booking_id']]);
            }
        }
        unset($booking);
    }

    usort($bookings, function ($a, $b) {
        return strtotime($a['start_datetime']) <=> strtotime($b['start_datetime']);
    });

    return $bookings;
}

function getAdminSchedules(): array
{
    $db = getDbConnection();
    $schedules = [];

    if ($db && tableExists($db, 'Employee_Schedules') && tableExists($db, 'Users') && tableExists($db, 'Parks')) {
        $sql = "SELECT s.schedule_id,
                       s.employee_id,
                       CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
                       p.park_name,
                       s.shift_date,
                       s.start_time,
                       s.end_time,
                       s.schedule_status,
                       s.notes
                FROM Employee_Schedules s
                INNER JOIN Users u ON s.employee_id = u.user_id
                INNER JOIN Parks p ON s.park_id = p.park_id
                ORDER BY s.shift_date ASC, s.start_time ASC";

        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $schedules[] = $row;
            }
        }
    }

    if (empty($schedules)) {
        $schedules = getFallbackAdminSchedules();
    }

    if (!empty($_SESSION['demo_admin_schedules']) && is_array($_SESSION['demo_admin_schedules'])) {
        $schedules = array_merge($_SESSION['demo_admin_schedules'], $schedules);
    }

    usort($schedules, function ($a, $b) {
        return strtotime(($a['shift_date'] ?? '') . ' ' . ($a['start_time'] ?? '00:00:00')) <=> strtotime(($b['shift_date'] ?? '') . ' ' . ($b['start_time'] ?? '00:00:00'));
    });

    return $schedules;
}

function getAdminPtoRequests(): array
{
    $db = getDbConnection();
    $requests = [];

    if ($db && tableExists($db, 'PTO_Requests') && tableExists($db, 'Users')) {
        $hasDecisionDate = tableHasColumn($db, 'PTO_Requests', 'decision_date');
        $sql = "SELECT pto.pto_id,
                       pto.employee_id,
                       CONCAT(u.first_name, ' ', u.last_name) AS employee_name,
                       pto.start_date,
                       pto.end_date,
                       pto.reason,
                       pto.pto_status,
                       pto.created_at,
                       " . ($hasDecisionDate ? "pto.decision_date" : "NULL AS decision_date") . "
                FROM PTO_Requests pto
                INNER JOIN Users u ON pto.employee_id = u.user_id
                ORDER BY pto.created_at DESC";

        $result = $db->query($sql);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $requests[] = $row;
            }
        }
    }

    if (empty($requests)) {
        $requests = getFallbackAdminPtoRequests();
    }

    if (!empty($_SESSION['demo_admin_pto']) && is_array($_SESSION['demo_admin_pto'])) {
        $overrides = [];
        foreach ($_SESSION['demo_admin_pto'] as $request) {
            $overrides[$request['pto_id']] = $request;
        }
        foreach ($requests as &$request) {
            if (isset($overrides[$request['pto_id']])) {
                $request = array_merge($request, $overrides[$request['pto_id']]);
            }
        }
        unset($request);
    }

    usort($requests, function ($a, $b) {
        return strtotime($b['created_at'] ?? '') <=> strtotime($a['created_at'] ?? '');
    });

    return $requests;
}

function getAdminDashboardMetrics(array $bookings, array $ptoRequests): array
{
    $metrics = [
        'events_booked' => 0,
        'pending_bookings' => 0,
        'denied_bookings' => 0,
        'upcoming_booked' => 0,
        'pending_pto' => 0,
    ];

    foreach ($bookings as $booking) {
        $status = strtolower($booking['booking_status'] ?? 'pending');
        if (in_array($status, ['approved', 'confirmed'], true)) {
            $metrics['events_booked']++;
        }
        if ($status === 'pending') {
            $metrics['pending_bookings']++;
        }
        if ($status === 'denied') {
            $metrics['denied_bookings']++;
        }
        if (in_array($status, ['approved', 'confirmed'], true) && strtotime($booking['start_datetime'] ?? '') >= strtotime('today')) {
            $metrics['upcoming_booked']++;
        }
    }

    foreach ($ptoRequests as $request) {
        if (strtolower($request['pto_status'] ?? 'pending') === 'pending') {
            $metrics['pending_pto']++;
        }
    }

    return $metrics;
}

function getAdminNotifications(array $bookings, array $ptoRequests): array
{
    $notifications = [];

    foreach ($bookings as $booking) {
        $status = strtolower($booking['booking_status'] ?? '');
        if ($status === 'pending') {
            $notifications[] = [
                'type' => 'booking',
                'icon' => 'bi-calendar-event',
                'title' => 'New booking request',
                'message' => ($booking['title'] ?? 'Reservation Request') . ' at ' . ($booking['park_name'] ?? 'NYS Park') . ' is waiting for review.',
                'time' => $booking['created_at'] ?? date('Y-m-d H:i:s'),
                'link' => 'admin-bookings.php',
            ];
        }
    }

    foreach ($ptoRequests as $request) {
        if (strtolower($request['pto_status'] ?? '') === 'pending') {
            $notifications[] = [
                'type' => 'pto',
                'icon' => 'bi-person-workspace',
                'title' => 'New PTO request',
                'message' => ($request['employee_name'] ?? 'Employee') . ' submitted PTO for ' . date('M d', strtotime($request['start_date'])) . '.',
                'time' => $request['created_at'] ?? date('Y-m-d H:i:s'),
                'link' => 'admin-pto.php',
            ];
        }
    }

    usort($notifications, function ($a, $b) {
        return strtotime($b['time']) <=> strtotime($a['time']);
    });

    return array_slice($notifications, 0, 8);
}

function getAdminBookingBuckets(array $bookings): array
{
    $buckets = [
        'upcoming' => [],
        'pending' => [],
        'denied' => [],
    ];

    foreach ($bookings as $booking) {
        $status = strtolower($booking['booking_status'] ?? 'pending');
        if (in_array($status, ['approved', 'confirmed'], true) && strtotime($booking['start_datetime'] ?? '') >= strtotime('today')) {
            $buckets['upcoming'][] = $booking;
        }
        if ($status === 'pending') {
            $buckets['pending'][] = $booking;
        }
        if ($status === 'denied') {
            $buckets['denied'][] = $booking;
        }
    }

    return $buckets;
}

function getAdminScheduleStatusBadgeClass(string $status): string
{
    return match (strtolower($status)) {
        'completed' => 'success',
        'cancelled' => 'secondary',
        default => 'primary',
    };
}

function getPtoStatusBadgeClass(string $status): string
{
    return match (strtolower($status)) {
        'approved' => 'success',
        'denied' => 'danger',
        default => 'warning',
    };
}

function createAdminScheduleEntry(array $adminUser, array $formData): array
{
    $employeeId = (int) ($formData['employee_id'] ?? 0);
    $parkId = (int) ($formData['park_id'] ?? 0);
    $shiftDate = trim($formData['shift_date'] ?? '');
    $startTime = trim($formData['start_time'] ?? '');
    $endTime = trim($formData['end_time'] ?? '');
    $notes = trim($formData['notes'] ?? '');

    if ($employeeId <= 0 || $parkId <= 0 || $shiftDate === '' || $startTime === '' || $endTime === '') {
        return ['success' => false, 'message' => 'Please complete all required schedule fields.'];
    }

    if (strtotime($shiftDate . ' ' . $startTime) >= strtotime($shiftDate . ' ' . $endTime)) {
        return ['success' => false, 'message' => 'Shift start time must be earlier than the end time.'];
    }

    $employeeLookup = [];
    foreach (getAdminEmployeeDirectory() as $employee) {
        $employeeLookup[(int) $employee['user_id']] = $employee;
    }
    $parkLookup = [];
    foreach (getBookableParks() as $park) {
        $parkLookup[(int) $park['park_id']] = $park;
    }

    if (!isset($employeeLookup[$employeeId]) || !isset($parkLookup[$parkId])) {
        return ['success' => false, 'message' => 'Please choose a valid employee and park.'];
    }

    $db = getDbConnection();
    $usedDemoMode = true;

    if ($db && tableExists($db, 'Employee_Schedules')) {
        $sql = "INSERT INTO Employee_Schedules (employee_id, park_id, shift_date, start_time, end_time, schedule_status, notes)
                VALUES (?, ?, ?, ?, ?, 'scheduled', ?)";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('iissss', $employeeId, $parkId, $shiftDate, $startTime, $endTime, $notes);
            if ($stmt->execute()) {
                $usedDemoMode = false;
            }
            $stmt->close();
        }
    }

    if ($usedDemoMode) {
        $_SESSION['demo_admin_schedules'][] = [
            'schedule_id' => rand(9900, 9999),
            'employee_id' => $employeeId,
            'employee_name' => trim(($employeeLookup[$employeeId]['first_name'] ?? '') . ' ' . ($employeeLookup[$employeeId]['last_name'] ?? '')),
            'park_name' => $parkLookup[$parkId]['park_name'] ?? 'NYS Park',
            'shift_date' => $shiftDate,
            'start_time' => $startTime . ':00',
            'end_time' => $endTime . ':00',
            'schedule_status' => 'scheduled',
            'notes' => $notes,
        ];
    }

    return [
        'success' => true,
        'message' => $usedDemoMode ? 'Schedule saved in demo mode.' : 'Employee schedule saved successfully.',
    ];
}

function updateAdminPtoStatus(int $ptoId, string $status, int $adminUserId): array
{
    $status = strtolower($status);
    if (!in_array($status, ['approved', 'denied'], true)) {
        return ['success' => false, 'message' => 'Invalid PTO status update.'];
    }

    $db = getDbConnection();
    $usedDemoMode = true;

    if ($db && tableExists($db, 'PTO_Requests')) {
        $hasReviewedBy = tableHasColumn($db, 'PTO_Requests', 'reviewed_by');
        $sql = "UPDATE PTO_Requests SET pto_status = ?, decision_date = NOW()" . ($hasReviewedBy ? ", reviewed_by = ?" : "") . " WHERE pto_id = ?";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            if ($hasReviewedBy) {
                $stmt->bind_param('sii', $status, $adminUserId, $ptoId);
            } else {
                $stmt->bind_param('si', $status, $ptoId);
            }
            if ($stmt->execute()) {
                $usedDemoMode = false;
            }
            $stmt->close();
        }
    }

    if ($usedDemoMode) {
        if (empty($_SESSION['demo_admin_pto'])) {
            $_SESSION['demo_admin_pto'] = [];
        }
        $_SESSION['demo_admin_pto'][$ptoId] = [
            'pto_id' => $ptoId,
            'pto_status' => $status,
            'decision_date' => date('Y-m-d H:i:s'),
        ];
    }

    return [
        'success' => true,
        'message' => $usedDemoMode ? 'PTO request updated in demo mode.' : 'PTO request updated successfully.',
    ];
}

function updateAdminBookingStatus(int $bookingId, string $status, int $adminUserId): array
{
    $status = strtolower($status);
    if (!in_array($status, ['approved', 'denied', 'confirmed', 'cancelled'], true)) {
        return ['success' => false, 'message' => 'Invalid booking status update.'];
    }

    $db = getDbConnection();
    $usedDemoMode = true;

    if ($db && tableExists($db, 'Bookings')) {
        $hasReviewedBy = tableHasColumn($db, 'Bookings', 'reviewed_by');
        $sql = "UPDATE Bookings SET booking_status = ?, decision_date = NOW()" . ($hasReviewedBy ? ", reviewed_by = ?" : "") . " WHERE booking_id = ?";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            if ($hasReviewedBy) {
                $stmt->bind_param('sii', $status, $adminUserId, $bookingId);
            } else {
                $stmt->bind_param('si', $status, $bookingId);
            }

            if ($stmt->execute()) {
                $usedDemoMode = false;
            }
            $stmt->close();
        }

        if (!$usedDemoMode && tableExists($db, 'Events')) {
            $lookup = $db->prepare("SELECT event_id FROM Bookings WHERE booking_id = ? LIMIT 1");
            if ($lookup) {
                $lookup->bind_param('i', $bookingId);
                $lookup->execute();
                $eventId = $lookup->get_result()?->fetch_assoc()['event_id'] ?? null;
                $lookup->close();
                if ($eventId) {
                    $eventStatus = match ($status) {
                        'approved', 'confirmed' => 'published',
                        'denied', 'cancelled' => 'cancelled',
                        default => 'draft',
                    };
                    $eventStmt = $db->prepare("UPDATE Events SET event_status = ? WHERE event_id = ?");
                    if ($eventStmt) {
                        $eventStmt->bind_param('si', $eventStatus, $eventId);
                        $eventStmt->execute();
                        $eventStmt->close();
                    }
                }
            }
        }
    }

    if ($usedDemoMode) {
        if (empty($_SESSION['demo_admin_bookings'])) {
            $_SESSION['demo_admin_bookings'] = [];
        }
        $_SESSION['demo_admin_bookings'][$bookingId] = [
            'booking_id' => $bookingId,
            'booking_status' => $status,
            'decision_date' => date('Y-m-d H:i:s'),
        ];
    }

    return [
        'success' => true,
        'message' => $usedDemoMode ? 'Booking request updated in demo mode.' : 'Booking request updated successfully.',
    ];
}


/*
 |--------------------------------------------------------------------------
 | Employee portal helpers
 |--------------------------------------------------------------------------
 | These functions power the employee portal pages.
 | The employee side is intentionally simple for the capstone:
 | 1. View assigned schedule
 | 2. Submit PTO request
 | 3. See past PTO decisions
 */
function getEmployeeSchedules(array $employeeUser): array
{
    $employeeId = (int) ($employeeUser['user_id'] ?? 0);
    $allSchedules = getAdminSchedules();

    $employeeSchedules = array_values(array_filter($allSchedules, function ($schedule) use ($employeeId) {
        return (int) ($schedule['employee_id'] ?? 0) === $employeeId;
    }));

    usort($employeeSchedules, function ($a, $b) {
        $left = strtotime(($a['shift_date'] ?? 'today') . ' ' . ($a['start_time'] ?? '00:00:00'));
        $right = strtotime(($b['shift_date'] ?? 'today') . ' ' . ($b['start_time'] ?? '00:00:00'));
        return $left <=> $right;
    });

    return $employeeSchedules;
}

function getEmployeePtoRequests(array $employeeUser): array
{
    $employeeId = (int) ($employeeUser['user_id'] ?? 0);
    $allRequests = getAdminPtoRequests();

    $employeeRequests = array_values(array_filter($allRequests, function ($request) use ($employeeId) {
        return (int) ($request['employee_id'] ?? 0) === $employeeId;
    }));

    usort($employeeRequests, function ($a, $b) {
        return strtotime($b['created_at'] ?? 'now') <=> strtotime($a['created_at'] ?? 'now');
    });

    return $employeeRequests;
}

function createEmployeePtoRequest(array $employeeUser, array $formData): array
{
    $employeeId = (int) ($employeeUser['user_id'] ?? 0);
    $startDate = trim((string) ($formData['start_date'] ?? ''));
    $endDate = trim((string) ($formData['end_date'] ?? ''));
    $reason = trim((string) ($formData['reason'] ?? ''));

    if ($employeeId <= 0 || $startDate === '' || $endDate === '') {
        return ['success' => false, 'message' => 'Please complete the required PTO fields.'];
    }

    if (strtotime($startDate) === false || strtotime($endDate) === false) {
        return ['success' => false, 'message' => 'Please provide valid PTO dates.'];
    }

    if (strtotime($startDate) > strtotime($endDate)) {
        return ['success' => false, 'message' => 'The PTO start date must be earlier than or equal to the end date.'];
    }

    if (strtotime($startDate) < strtotime('today')) {
        return ['success' => false, 'message' => 'Please choose a PTO start date that is today or later.'];
    }

    $db = getDbConnection();
    $usedDemoMode = true;

    if ($db && tableExists($db, 'PTO_Requests')) {
        $sql = "INSERT INTO PTO_Requests (employee_id, start_date, end_date, reason, pto_status, created_at)
                VALUES (?, ?, ?, ?, 'pending', NOW())";
        $stmt = $db->prepare($sql);
        if ($stmt) {
            $stmt->bind_param('isss', $employeeId, $startDate, $endDate, $reason);
            if ($stmt->execute()) {
                $usedDemoMode = false;
            }
            $stmt->close();
        }
    }

    if ($usedDemoMode) {
        if (empty($_SESSION['demo_admin_pto']) || !is_array($_SESSION['demo_admin_pto'])) {
            $_SESSION['demo_admin_pto'] = [];
        }

        $employeeName = trim((string) (($employeeUser['first_name'] ?? '') . ' ' . ($employeeUser['last_name'] ?? '')));
        if ($employeeName === '') {
            $employeeName = 'Employee';
        }

        $_SESSION['demo_admin_pto'][] = [
            'pto_id' => rand(8000, 9999),
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => $reason,
            'pto_status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'decision_date' => null,
        ];
    }

    return [
        'success' => true,
        'message' => $usedDemoMode ? 'PTO request submitted in demo mode.' : 'PTO request submitted successfully.',
    ];
}
