USE nys_parks;

INSERT INTO parks (name, region, park_type, address_line, city, zip_code, hours, amenities, description, image_url, latitude, longitude, is_featured) VALUES
('Jones Beach State Park', 'Long Island', 'Beach', '1 Ocean Pkwy', 'Wantagh', '11793', '6:00 AM - 10:00 PM', 'Boardwalk, concerts, beaches, parking', 'Oceanfront boardwalk, summer concerts, family activities, and wide sandy beaches.', 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=80', 40.5962, -73.5030, 1),
('Letchworth State Park', 'Western New York', 'Waterfalls', '1 Letchworth State Park', 'Castile', '14427', '6:00 AM - 11:00 PM', 'Trails, overlooks, picnic areas', 'Dramatic gorge views, trails, scenic overlooks, and some of the state''s most iconic waterfalls.', 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=1200&q=80', 42.6730, -78.0048, 1),
('Niagara Falls State Park', 'Western New York', 'Landmark', '332 Prospect St', 'Niagara Falls', '14303', 'Open daily', 'Observation decks, visitors center', 'World-famous waterfalls, observation decks, and unforgettable sightseeing experiences.', 'https://images.unsplash.com/photo-1528909514045-2fa4ac7a08ba?auto=format&fit=crop&w=1200&q=80', 43.0828, -79.0742, 1),
('Watkins Glen State Park', 'Finger Lakes', 'Hiking', '1009 N Franklin St', 'Watkins Glen', '14891', '8:00 AM - dusk', 'Gorge trails, waterfalls, scenic routes', 'Stone bridges, layered waterfalls, gorge trails, and one of New York''s signature hikes.', 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=1200&q=80', 42.3801, -76.8733, 1),
('Montauk Point State Park', 'Long Island', 'Coastal', '2000 Montauk Hwy', 'Montauk', '11954', 'Sunrise - sunset', 'Lighthouse, trails, fishing', 'Clifftop views, striped lighthouse scenery, fishing spots, and dramatic coastal landscapes.', 'https://images.unsplash.com/photo-1464822759023-fed622ff2c3b?auto=format&fit=crop&w=1200&q=80', 41.0722, -71.8603, 0),
('Minnewaska State Park Preserve', 'Hudson Valley', 'Trails', '5281 Route 44-55', 'Kerhonkson', '12446', '9:00 AM - 7:00 PM', 'Lakes, cliffs, carriage roads', 'Ridge-top views, lakes, cliffs, carriage roads, and peaceful hiking routes.', 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?auto=format&fit=crop&w=1200&q=80', 41.7392, -74.2349, 0);

INSERT INTO users (first_name, last_name, email, password_hash, role, phone, birthdate, organization, notes, park_id, account_status) VALUES
('Admin', 'User', 'admin@nysparks.local', '$2y$12$bpa9e9oDIxhNKWoCaAcj5.Phd3vPaSQyoAXmXqEIZjv.2PiJj.Pki', 'admin', '(555) 111-1000', '1990-01-10', 'NYS Parks', 'Seeded administrator account', 1, 'active'),
('Jordan', 'Rivera', 'employee@nysparks.local', '$2y$12$bpa9e9oDIxhNKWoCaAcj5.Phd3vPaSQyoAXmXqEIZjv.2PiJj.Pki', 'employee', '(555) 111-2000', '1995-07-18', NULL, 'Seeded employee account', 1, 'active'),
('Dev', 'User', 'client@nysparks.local', '$2y$12$bpa9e9oDIxhNKWoCaAcj5.Phd3vPaSQyoAXmXqEIZjv.2PiJj.Pki', 'client', '(555) 234-8811', '1992-04-12', 'Community Events Group', 'Primary contact for summer festival and youth recreation events.', NULL, 'active'),
('Casey', 'Morgan', 'casey.morgan@nysparks.local', '$2y$12$bpa9e9oDIxhNKWoCaAcj5.Phd3vPaSQyoAXmXqEIZjv.2PiJj.Pki', 'employee', '(555) 222-3333', '1993-09-22', NULL, 'Guest services', 2, 'active'),
('Taylor', 'Brooks', 'taylor.brooks@nysparks.local', '$2y$12$bpa9e9oDIxhNKWoCaAcj5.Phd3vPaSQyoAXmXqEIZjv.2PiJj.Pki', 'employee', '(555) 333-4444', '1991-03-17', NULL, 'Operations', 3, 'active');

INSERT INTO fields (park_id, name, field_type, capacity, availability_status, notes) VALUES
(1, 'South Shore Field', 'Open Field', 250, 'available', 'Large event staging area'),
(2, 'Falls Lawn', 'Open Field', 180, 'available', 'Good for food fairs and youth events'),
(3, 'River View Plaza', 'Plaza', 220, 'available', 'Best for medium events'),
(4, 'Gorge Terrace', 'Scenic Deck', 120, 'available', 'Limited amplified sound'),
(5, 'Lighthouse Lawn', 'Open Field', 150, 'available', 'Ideal for sunrise sessions');

INSERT INTO events (park_id, field_id, title, description, category, event_type, start_datetime, end_datetime, ticket_price, event_status, created_by) VALUES
(1, 1, 'Summer Concert Series: Rock the Beach', 'Live music and food vendors by the ocean.', 'Music', 'public', '2026-07-15 19:00:00', '2026-07-15 22:00:00', 45.00, 'published', 1),
(5, 5, 'Sunrise Yoga by the Lighthouse', 'Morning yoga and guided breathing session.', 'Wellness', 'public', '2026-06-20 06:00:00', '2026-06-20 07:30:00', 15.00, 'published', 1),
(1, 1, 'Food Truck Festival', 'Food trucks, music, and family activities.', 'Food', 'public', '2026-08-05 11:00:00', '2026-08-05 16:00:00', 5.00, 'published', 1),
(4, 4, 'Family Stargazing Night', 'Astronomy guides and telescopes for all ages.', 'Family', 'public', '2026-08-18 20:00:00', '2026-08-18 22:00:00', 12.00, 'published', 1);

INSERT INTO bookings (client_id, park_id, field_id, title, booking_type, attendee_email, start_datetime, end_datetime, guest_count, requested_setup, event_description, special_requests, reservation_fee, booking_status, reviewed_by, reviewed_at, admin_notes) VALUES
(3, 3, 3, 'Community Food Fair', 'Community', 'dev.user@example.com', '2026-07-02 10:00:00', '2026-07-02 15:00:00', 150, 'Tables + power', 'Local food vendors and family activities.', 'Need parking support and ADA access.', 620.00, 'pending', NULL, NULL, NULL),
(3, 1, 1, 'Sunset Wellness Series', 'Wellness', 'dev.user@example.com', '2026-06-18 18:00:00', '2026-06-18 20:00:00', 60, 'Small stage', 'Outdoor wellness class and speaker.', 'Power for microphones.', 180.00, 'approved', 1, '2026-05-10 09:00:00', 'Approved pending final setup confirmation'),
(3, 2, 2, 'Youth Sports Day', 'Sports', 'dev.user@example.com', '2026-08-14 09:00:00', '2026-08-14 14:00:00', 120, 'Cones + tents', 'Youth sports clinic and family picnic.', 'Need extra restroom signage.', 520.00, 'confirmed', 1, '2026-05-12 11:15:00', 'Confirmed'),
(3, 1, 1, 'Sunset Jazz Festival', 'Music', 'dev.user@example.com', '2026-08-11 18:00:00', '2026-08-11 22:00:00', 220, 'Stage + seating', 'Evening jazz event with food stalls.', 'Security requested.', 780.00, 'pending', NULL, NULL, NULL);

INSERT INTO employee_schedules (employee_id, park_id, shift_date, start_time, end_time, assignment, schedule_status, notes, created_by) VALUES
(2, 1, '2026-06-14', '08:00:00', '16:00:00', 'Events support', 'scheduled', NULL, 1),
(2, 1, '2026-06-15', '09:00:00', '17:00:00', 'Ranger duty', 'scheduled', NULL, 1),
(4, 2, '2026-06-14', '09:00:00', '17:00:00', 'Guest services', 'scheduled', NULL, 1),
(5, 3, '2026-06-15', '07:00:00', '15:00:00', 'Operations', 'scheduled', NULL, 1);

INSERT INTO pto_requests (employee_id, leave_type, start_date, end_date, reason, pto_status, reviewed_by, reviewed_at, admin_notes) VALUES
(2, 'Vacation', '2026-07-20', '2026-07-22', 'Family trip', 'pending', NULL, NULL, NULL),
(4, 'Personal', '2026-08-03', '2026-08-05', 'Personal days', 'approved', 1, '2026-06-01 10:30:00', 'Coverage available'),
(5, 'Sick Leave', '2026-09-12', '2026-09-12', 'Medical appointment', 'pending', NULL, NULL, NULL);

INSERT INTO payments (user_id, booking_id, payment_type, donor_name, donor_email, amount, card_last4, card_brand, exp_month, exp_year, payment_method, payment_status, transaction_ref) VALUES
(3, 2, 'reservation', 'Dev User', 'dev.user@example.com', 180.00, '4242', 'Visa', 12, 2027, 'card', 'completed', 'RES-2026-0001'),
(3, NULL, 'donation', 'Dev User', 'dev.user@example.com', 50.00, '1111', 'Visa', 11, 2027, 'card', 'completed', 'DON-2026-0001');

INSERT INTO attendance (event_id, user_id, attendee_email, guest_count, attendance_status) VALUES
(1, 3, 'dev.user@example.com', 2, 'attending'),
(2, 3, 'dev.user@example.com', 1, 'attending');
