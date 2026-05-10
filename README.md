# NYS Parks & Recreation Portal — Flat PHP/MySQL Build

This is my NYS Parks & Recreation capstone project. It is a public-facing parks website with role-based account areas for clients, employees, and admins. The project is intentionally built as a flat PHP/MySQL application so it is easy to run in XAMPP, easy to review, and easy to submit without needing a framework install or complicated server routing.

The site combines public park discovery pages, event listings, news updates, a searchable FAQ, a mock donation flow, a map page, an AI guide page, and private dashboards for different user roles. Most pages keep the original static front-end structure, but the important workflows are connected to MySQL through `bootstrap.php` and prepared PDO queries.

This final package uses the polished p7r18 page structure and comments as the base, with the stable p7r19 `register.php` flow kept for client account registration.

---

## Project goals

The main goal of this build is to show a complete, understandable, database-backed parks portal. I focused on making the project practical for a class/capstone review instead of overcomplicating it with extra folders or framework files.

The project demonstrates:

- a professional public NYS Parks style website
- public pages that visitors can use without logging in
- private pages protected by role checks
- client reservation/event-request workflows
- employee schedule and PTO workflows
- admin approval, employee management, news management, and CSV export workflows
- reusable shared navigation, footer, Bootstrap styling, and JavaScript helpers
- a MySQL schema with foreign keys, indexes, enum fields, and check constraints
- seeded test data so the project can be demoed immediately after import

---

## Tech stack

- **PHP** for server-side pages and form handling
- **MySQL / MariaDB** for the relational database
- **PDO** for database access
- **Bootstrap 5.3.3** for layout, cards, forms, buttons, grid, and responsive design
- **Bootstrap Icons** for navigation and UI icons
- **Vanilla JavaScript** for filtering, FAQ search, news expansion, donation form toggles, AI chat UI, and chart setup
- **Chart.js** for dashboard charts where available
- **Google Maps JavaScript API** for the live public map feature when a key is configured
- **OpenAI / ChatGPT API** for the AI Guide chat functionality through `ai-api.php`
- **External CDN/API resources** including Bootstrap, Bootstrap Icons, Chart.js, Google Maps, Unsplash images, and OpenAI API access
- **XAMPP** as the intended local runtime
- **HTML/CSS** with one shared stylesheet in `css/styles.css`

There are no Laravel, Symfony, React, Node, Composer, or npm requirements.

---

## Current folder structure

The project is flat on purpose. All main pages live in the root folder.

```text
p7r18_base_p7r19_register_final/
├── about.php
├── account.php
├── admin-bookings.php
├── admin-dashboard.php
├── admin-employee-accounts.php
├── admin-employee-schedule.php
├── admin-news.php
├── admin-pto.php
├── admin-csv.php
├── ai.php
├── ai-api.php
├── bootstrap.php
├── client-create-event.php
├── client-dashboard.php
├── donate.php
├── employee-dashboard.php
├── employee-pto.php
├── employee-schedule.php
├── events.php
├── faq.php
├── forgot-password.php
├── index.php
├── login.php
├── logout.php
├── map.php
├── map-api.php
├── news.php
├── parks.php
├── register.php
├── reset-password.php
├── search.php
├── css/
│   └── styles.css
├── includes/
│   ├── constants.php
│   ├── header.php
│   └── footer.php
├── js/
│   ├── app.js
│   └── dashboard-charts.js
├── db/
│   ├── schema.sql
│   └── seed.sql
├── README.md
└── todo - done - data - pgs - sql - etc .xlsx
```

The `.idea/` folder is only IDE metadata. It is not required to run the project and should be removed from a clean final submission. The spreadsheet is a planning/checklist artifact, not a runtime dependency.

---

## Important project files

### `bootstrap.php`

This is the shared backend setup file. Most dynamic PHP pages require it.

It handles:

- session startup
- secure-ish session cookie settings for local project use
- PDO database connection
- current user lookup
- login and logout helpers
- role protection helpers
- flash messages
- HTML escaping helper `e()`
- date formatting helpers
- input helpers for `POST` and `GET`
- mock card validation
- CSV export filename helper

The database settings are currently inside the `db()` function:

```php
$host = '127.0.0.1';
$port = '3306';
$dbname = 'nys_parks';
$username = 'root';
$password = '';
```

### `includes/header.php` and `includes/footer.php`

These are the shared layout partials. They keep the public navigation, account/login links, footer columns, and common page shell consistent across the flat PHP pages.

### `css/styles.css`

This is the shared custom stylesheet. It contains the site shell, hero sections, cards, dashboards, filter panels, map layout, event/news tiles, footer, and responsive polish.

### `js/app.js`

This is the shared JavaScript file for common front-end behavior.

It handles:

- active navigation highlighting based on `data-page`
- confirmation prompts for delete/review actions
- news search/filter behavior
- news “Open article / Close article” collapse behavior
- FAQ search/filter behavior
- donation payment method UI
- AI chat form UI and API call handling

### `js/dashboard-charts.js`

This supports dashboard chart rendering and chart fallback behavior. It also has helper logic for reservation payment form toggles.

### `db/schema.sql`

This creates the full `nys_parks` database from scratch.

### `db/seed.sql`

This inserts demo records for parks, users, fields, events, news, bookings, attendance, payments, employee schedules, and PTO requests.

---

## Database setup in XAMPP

### 1. Copy the project folder

Copy the full project folder into XAMPP `htdocs`.

Example:

```text
C:\xampp\htdocs\p7r18_base_p7r19_register_final
```

### 2. Start services

Open XAMPP Control Panel and start:

- Apache
- MySQL

### 3. Create/import the database

Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

Then import these files in this order:

1. `db/schema.sql`
2. `db/seed.sql`

The schema file already includes:

```sql
DROP DATABASE IF EXISTS nys_parks;
CREATE DATABASE nys_parks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nys_parks;
```

So it will rebuild the database cleanly.

### 4. Confirm DB settings

Open `bootstrap.php` and confirm these values match your local MySQL setup:

```php
$host = '127.0.0.1';
$port = '3306';
$dbname = 'nys_parks';
$username = 'root';
$password = '';
```

For a normal XAMPP install, `root` with a blank password should work.

### 5. Open the site

Use:

```text
http://localhost/p7r18_base_p7r19_register_final/index.php
```

---

## Deployment setup on XfinityFree.com

I also deployed the project on XfinityFree.com using the same basic process as the local XAMPP setup.

### 1. Upload the source code

In the XfinityFree.com hosting control panel, open **File Manager** and upload the project source code into the site `htdocs` folder.

The uploaded files should include the PHP pages, `css/`, `js/`, `includes/`, and `db/` folders.

### 2. Import the database

Open phpMyAdmin from the XfinityFree.com hosting control panel and import the SQL files in this order:

1. `db/schema.sql`
2. `db/seed.sql`

This creates and fills the `nys_parks` database tables the same way the local XAMPP setup does. On shared hosting, you may need to remove or skip the `DROP DATABASE`, `CREATE DATABASE`, and `USE` lines from `db/schema.sql` because the host usually creates the database for you. In that case, select the assigned database in phpMyAdmin first, then import the table/schema statements and seed data.

### 3. Update hosted database settings

Open `bootstrap.php` and update the SQL server information to match the hosting control panel values:

```php
$host = 'your-host-name';
$port = '3306';
$dbname = 'your-database-name';
$username = 'your-database-username';
$password = 'your-database-password';
```

The required values are the host name, database name, username, password, and port. These are usually different from the local XAMPP defaults.

### 4. Hook up the domain and open the site

After the source code is in `htdocs`, the database has been imported through phpMyAdmin, and `bootstrap.php` has the hosted database settings, connect the hosted domain to the account/site.

Once the domain is connected, the project can be opened from the live domain instead of the local XAMPP URL.

---

## Seeded login accounts

The seeded password for all demo accounts is:

```text
Password123!
```

Main demo accounts:

| Role | Email | Purpose |
|---|---|---|
| Admin | `admin@nysparks.local` | Admin dashboard, employees, approvals, news manager, CSV exports |
| Employee | `employee@nysparks.local` | Employee dashboard, schedule, PTO requests |
| Client | `client@nysparks.local` | Client dashboard, RSVPs, event requests, donations |

Additional seeded employees:

| Name | Email | Role |
|---|---|---|
| Casey Morgan | `casey.morgan@nysparks.local` | Employee |
| Taylor Brooks | `taylor.brooks@nysparks.local` | Employee |

---

## Public vs private pages

The project has two main sides: public visitor pages and private role-based pages.

### Public pages

These pages can be viewed without logging in:

| File | Page | What it does |
|---|---|---|
| `index.php` | Home | Landing page with hero, search entry, featured parks, featured events, and public CTAs |
| `parks.php` | Explore Parks | Search/filter parks by keyword, region, and type using records from `parks` |
| `events.php` | Events | Shows public and private events from `events`, with filtering and RSVP actions |
| `map.php` | Map | Shows park locations and cards using `parks` data and optional Google Maps support |
| `news.php` | News | Shows published public updates from the `news` table with topic/region/search filters |
| `about.php` | About Us | Static public information page about the project/site |
| `faq.php` | FAQ | Searchable/filterable FAQ stored in a PHP array for now |
| `donate.php` | Donate | Public donation information page with mock card/in-person payment handling for logged-in clients |
| `ai.php` | AI Guide | Public AI assistant page that connects to `ai-api.php` |
| `search.php` | Search | Sitewide search across parks, events, and news |
| `login.php` | Login | User login page |
| `register.php` | Register | New client account registration |
| `forgot-password.php` | Forgot Password | Verifies a user by email/name for local reset flow |
| `reset-password.php` | Reset Password | Updates the password after verification |
| `logout.php` | Logout | Ends the session and returns the user to the public site |

Some public pages have optional logged-in behavior. For example, the events page can show RSVP actions when a client is logged in, and public events can also accept guest RSVP entries by email.

### Private pages

These pages require login and role checks through `require_login()` or `require_role()`.

| File | Role | What it does |
|---|---|---|
| `account.php` | Any logged-in user | Shared profile/account page with role-specific stats and action links |
| `client-dashboard.php` | Client | Client overview, RSVP records, bookings, payments, and event request actions |
| `client-create-event.php` | Client | Allows a client to submit a private event/booking request |
| `employee-dashboard.php` | Employee | Employee home dashboard with upcoming schedule and PTO overview |
| `employee-schedule.php` | Employee | Read-only employee shift schedule |
| `employee-pto.php` | Employee | Submit/cancel PTO requests and view PTO history |
| `admin-dashboard.php` | Admin | Main admin overview with metrics, charts, and links to admin tools |
| `admin-employee-accounts.php` | Admin | Create, update, disable, and reactivate employee accounts |
| `admin-news.php` | Admin | Create, update, search, and delete news records |
| `admin-bookings.php` | Admin | Review, approve, deny, and link booking requests to private events |
| `admin-employee-schedule.php` | Admin | Create, update, and delete employee shift schedules with overlap validation |
| `admin-pto.php` | Admin | Approve or deny employee PTO requests |
| `admin-csv.php` | Admin | Export database datasets as CSV downloads |

---

## Main site flow

### Public visitor flow

A visitor can start on the home page and move through the public content:

1. Visit `index.php`
2. Search for parks or browse featured sections
3. Go to `parks.php` to filter parks
4. Go to `events.php` to browse programs and events
5. Go to `news.php` to read updates and public notices
6. Use `map.php` for park location browsing
7. Use `faq.php` for common questions
8. Register or log in if they want account actions

### Client flow

A client can:

1. Register on `register.php`
2. Log in through `login.php`
3. View their dashboard on `client-dashboard.php`
4. RSVP to public events from `events.php`
5. Create a private event request through `client-create-event.php`
6. Track booking status and payment status on the dashboard
7. Donate through `donate.php`
8. Update profile details in `account.php`

### Employee flow

An employee can:

1. Log in through `login.php`
2. View their dashboard on `employee-dashboard.php`
3. Review their shift schedule on `employee-schedule.php`
4. Submit PTO through `employee-pto.php`
5. Cancel pending PTO requests if needed
6. Update profile details in `account.php`

### Admin flow

An admin can:

1. Log in through `login.php`
2. View metrics on `admin-dashboard.php`
3. Create/update/disable employee accounts on `admin-employee-accounts.php`
4. Add/update/delete public news records on `admin-news.php`
5. Approve or deny booking requests on `admin-bookings.php`
6. Create, update, or delete employee schedules on `admin-employee-schedule.php`
7. Approve or deny PTO requests on `admin-pto.php`
8. Export CSV datasets through `admin-csv.php`

---

## Feature overview

### Public parks directory

`parks.php` loads park records from the `parks` table and supports filtering by:

- keyword
- region
- park type

Park cards include public-facing information such as region, type, description, summary, image, and amenities.

### Events page

`events.php` loads events from the `events` table and joins parks for park names/regions. It supports public event browsing, logged-in client RSVP behavior, and guest RSVP by email for public events.

Important event behavior:

- events have timing states like upcoming, live, past, or cancelled
- event listings can use image fields from `events` or fall back to park images
- RSVP records are stored in `attendance`
- logged-in clients can RSVP using their account email
- public visitors can RSVP to public events by entering an email address
- duplicate active RSVP records are prevented by the unique attendance constraint
- capacity is checked in PHP before a new RSVP is accepted
- RSVP cancellation updates attendance status instead of deleting the row

### News page

`news.php` loads records from the `news` table.

News records include:

- title
- topic
- published date
- region
- summary
- full content
- image URL and alt text
- tag
- status

The page supports:

- keyword search
- topic filter buttons
- region filter
- update count display
- open/close article expansion in the card

The public news cards are database-backed. Unlike the FAQ page, news content is not hardcoded in the PHP page.

### FAQ page

`faq.php` currently uses a PHP array named `$faqItems`. It is not database-backed yet.

The page still has dynamic front-end behavior:

- search box
- topic filter buttons
- result count
- Bootstrap accordion items

This was kept simple so the FAQ can be edited directly in the page. A future version could move FAQs into a new database table without changing the public layout much.

### Donation flow

`donate.php` supports a mock donation workflow. Public donation information is visible without logging in, but submitting the mock donation form requires a logged-in client account.

Important details:

- donations are stored in `payments`
- payment type is `donation`
- the flow supports card and in-person/pledge style options
- card validation is for class/demo use only
- CVV and full card data are not production-safe and should not be used like this in a real site
- card expiration is checked in PHP with `validate_card()`

### Client booking/event request flow

`client-create-event.php` allows clients to request private events.

The request is stored in `bookings` with a pending status.

The page validates:

- required fields
- selected park/field
- date/time ordering
- guest count
- field capacity
- overlapping active bookings for the same field/time range

Payment is not collected on `client-create-event.php`. The booking request stores a calculated reservation fee, and the client dashboard displays booking/payment status for review/demo purposes.

### Admin booking approval flow

`admin-bookings.php` lets admins approve or deny bookings.

When a booking is approved, the code creates or updates a linked private event in the `events` table and stores that event ID in the booking record.

This is the intended flow:

1. Client submits a booking request.
2. A `bookings` row is created with `booking_status = 'pending'`.
3. Admin reviews it.
4. If approved, a private `events` row is created or updated.
5. The booking gets `event_id`, `reviewed_by`, `reviewed_at`, and admin notes.

### Employee schedule flow

`admin-employee-schedule.php` lets admins create and update employee shifts.

The code checks for overlapping shifts for the same employee/date before inserting or updating a schedule.

Employees view schedules on:

- `employee-dashboard.php`
- `employee-schedule.php`

Employees cannot edit their own schedule from those pages. Admin schedule actions also support deleting/cancelling schedule records from the admin schedule page.

### PTO flow

Employees submit PTO through `employee-pto.php`.

The code checks:

- start date is not after end date
- required fields are present
- overlapping pending/approved PTO requests do not already exist

Admins approve/deny PTO in `admin-pto.php`.

Approval metadata is stored in:

- `reviewed_by`
- `reviewed_at`
- `admin_notes`

### Admin dashboard and reporting

`admin-dashboard.php` includes query-driven counts and dashboard sections.

It uses database queries for:

- park count
- event count
- approved bookings
- pending bookings
- pending PTO
- attendance counts
- upcoming RSVP guest totals
- active employee counts
- chart data for bookings, events, attendance, and donations

Employee account management and news management now live on dedicated admin pages: `admin-employee-accounts.php` and `admin-news.php`. The dashboard links to those tools instead of handling those mutations directly. Disabling an employee account also cancels that employee’s future scheduled shifts so disabled employees do not remain assigned to upcoming work.

### CSV export

`admin-csv.php` exports selected datasets as CSV downloads.

It supports optional `date_from` and `date_to` filters for date-based exports, using the appropriate date column for each selected dataset. It does not require filesystem write permissions because it sends the CSV directly as a download response.

Supported export-style datasets include:

- parks
- users
- employees
- fields
- events
- news
- bookings
- attendance
- payments
- employee schedules
- PTO requests

### AI Guide

`ai.php` provides the public AI guide interface.

`ai-api.php` handles the backend API call pattern using the OpenAI / ChatGPT API for the chat function. The front-end sends the user message and recent chat history. The API key configuration is commented near the top of `ai-api.php` so it can be edited for the local or hosted setup. If a live API key/backend is not configured, the page still provides a clear place for that feature.

### Map page

`map.php` loads park records and prepares map-friendly data such as name, region, address, latitude, longitude, image, and summary.

`map-api.php` contains a helper for checking whether a Google Maps API key is configured. The Google Maps API key configuration is commented near the top of `map-api.php` so it can be edited for the local or hosted setup.

---

## Current database overview

The current schema uses **10 tables**:

1. `parks`
2. `users`
3. `fields`
4. `events`
5. `news`
6. `bookings`
7. `attendance`
8. `payments`
9. `employee_schedules`
10. `pto_requests`

The older README said the database had 9 tables and did not include news. That is no longer accurate. The current project includes a full `news` table and the News page reads from it.

---

## Seed data overview

`db/seed.sql` includes sample data for demo/testing.

Approximate seeded content:

| Table | Seed data included |
|---|---:|
| `parks` | 27 parks |
| `users` | 5 users |
| `fields` | 6 facilities/fields |
| `events` | 24 events |
| `news` | 6 public news updates |
| `bookings` | 4 booking requests |
| `attendance` | 2 attendance/RSVP records |
| `payments` | 2 payments |
| `employee_schedules` | 4 schedule records |
| `pto_requests` | 4 PTO requests |

The seed data is designed so the dashboards and public pages have something to show right away.

---

## SQL schema summary

The full executable SQL is in `db/schema.sql`. This section explains what each table does and lists the most important keys, constraints, and indexes.

### `parks`

Stores public park/location records.

Important fields:

- `id`
- `name`
- `region`
- `park_type`
- address fields
- `hours`
- `total_fields`
- `max_capacity`
- `amenities`
- `description`
- `image_url`
- `image_alt`
- `card_summary`
- `latitude`
- `longitude`
- `is_featured`
- timestamps

Important constraints/indexes:

```sql
PRIMARY KEY (id)
UNIQUE (name)
CHECK (total_fields >= 0)
CHECK (max_capacity > 0)
INDEX idx_parks_region (region)
INDEX idx_parks_type (park_type)
INDEX idx_parks_featured (is_featured)
```

### `users`

Stores all users: clients, employees, and admins.

Important fields:

- `id`
- `first_name`
- `last_name`
- `email`
- `password_hash`
- `role`
- `phone`
- `birthdate`
- `organization`
- `notes`
- `park_id`
- `account_status`
- `last_login_at`
- `profile_image_url`
- `created_at`
- `updated_at`

Role values:

```sql
ENUM('client','employee','admin')
```

Status values:

```sql
ENUM('active','locked','disabled')
```

Important constraints/indexes:

```sql
PRIMARY KEY (id)
UNIQUE (email)
FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE SET NULL
INDEX idx_users_role_status (role, account_status)
INDEX idx_users_email_status (email, account_status)
INDEX idx_users_park (park_id)
```

### `fields`

Stores reservable fields/facilities that belong to parks.

Important fields:

- `id`
- `park_id`
- `name`
- `field_type`
- `capacity`
- `field_size_sqft`
- `availability_status`
- `notes`

Availability values:

```sql
ENUM('available','unavailable','maintenance')
```

Important constraints/indexes:

```sql
FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE CASCADE
CHECK (capacity > 0)
CHECK (field_size_sqft IS NULL OR field_size_sqft > 0)
UNIQUE KEY uq_field_name_per_park (park_id, name)
INDEX idx_fields_park_status (park_id, availability_status)
```

### `events`

Stores both public events and private events created from approved bookings.

Important fields:

- `id`
- `park_id`
- `field_id`
- `title`
- `description`
- `image_url`
- `image_alt`
- `card_summary`
- `category`
- `event_type`
- `start_datetime`
- `end_datetime`
- `capacity`
- `fee_amount`
- `is_featured`
- `event_status`
- `created_by`

Event type values:

```sql
ENUM('public','private')
```

Event status values:

```sql
ENUM('draft','published','closed','cancelled','completed')
```

Important constraints/indexes:

```sql
FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT
FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
CHECK (start_datetime < end_datetime)
CHECK (capacity > 0)
CHECK (fee_amount >= 0)
INDEX idx_events_status_dates (event_status, start_datetime)
INDEX idx_events_park_dates (park_id, start_datetime)
INDEX idx_events_type_status_dates (event_type, event_status, start_datetime)
INDEX idx_events_category (category)
INDEX idx_events_featured (is_featured)
```

### `news`

Stores public news feed items used by `news.php` and managed from `admin-news.php`.

Important fields:

- `id`
- `title`
- `topic`
- `published_date`
- `region`
- `summary`
- `content`
- `image_url`
- `image_alt`
- `card_summary`
- `tag`
- `is_featured`
- `news_status`

Topic values:

```sql
ENUM('alerts','community','events','parks','safety','support','conservation','volunteer','maintenance','education','seasonal')
```

Status values:

```sql
ENUM('draft','published','archived')
```

Important indexes:

```sql
INDEX idx_news_status_date (news_status, published_date)
INDEX idx_news_topic_date (topic, published_date)
INDEX idx_news_region (region)
INDEX idx_news_featured (is_featured)
```

### `bookings`

Stores client private event/field reservation requests.

Important fields:

- `id`
- `client_id`
- `park_id`
- `field_id`
- `event_id`
- `title`
- `booking_type`
- `attendee_email`
- `start_datetime`
- `end_datetime`
- `guest_count`
- `requested_setup`
- `event_description`
- `special_requests`
- `reservation_fee`
- `booking_status`
- review metadata

Booking status values:

```sql
ENUM('pending','approved','denied','cancelled','confirmed','completed')
```

Important constraints/indexes:

```sql
FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT
FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE SET NULL
FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE SET NULL
FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
CHECK (start_datetime < end_datetime)
CHECK (guest_count > 0)
CHECK (reservation_fee >= 0)
UNIQUE KEY uq_bookings_event (event_id)
INDEX idx_bookings_status_dates (booking_status, start_datetime)
INDEX idx_bookings_client_status (client_id, booking_status)
INDEX idx_bookings_park_dates (park_id, start_datetime)
INDEX idx_bookings_reviewed_by (reviewed_by)
```

### `attendance`

Stores event RSVP/attendance records.

Important fields:

- `id`
- `event_id`
- `user_id`
- `attendee_email`
- `guest_count`
- `attendance_status`
- `registered_at`
- `checked_in_at`

Attendance status values:

```sql
ENUM('registered','cancelled','attended','no_show')
```

Important constraints/indexes:

```sql
FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
CHECK (guest_count > 0)
UNIQUE KEY uq_attendance_event_email (event_id, attendee_email)
INDEX idx_attendance_event_status (event_id, attendance_status)
INDEX idx_attendance_user (user_id)
INDEX idx_attendance_email (attendee_email)
```

### `payments`

Stores mock reservation and donation payments.

Important fields:

- `id`
- `user_id`
- `booking_id`
- `payment_type`
- `payer_name`
- `payer_email`
- `amount`
- `payment_method`
- `card_num`
- `exp_month`
- `exp_year`
- `cvv`
- `payment_status`
- `transaction_ref`

Payment type values:

```sql
ENUM('reservation','donation')
```

Payment method values:

```sql
ENUM('card','in_person')
```

Payment status values:

```sql
ENUM('pending','completed','failed','refunded')
```

Important constraints/indexes:

```sql
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
CHECK (amount > 0)
CHECK (payment_method <> 'card' OR (card_num IS NOT NULL AND card_num REGEXP '^[0-9]{13,19}$'))
CHECK (payment_method <> 'card' OR (exp_month IS NOT NULL AND exp_month BETWEEN 1 AND 12))
CHECK (payment_method <> 'card' OR (exp_year IS NOT NULL AND exp_year BETWEEN 2026 AND 2100))
CHECK (payment_method <> 'card' OR (cvv IS NOT NULL AND cvv REGEXP '^[0-9]{3,4}$'))
UNIQUE (transaction_ref)
INDEX idx_payments_type_status (payment_type, payment_status)
INDEX idx_payments_user (user_id)
INDEX idx_payments_booking (booking_id)
INDEX idx_payments_date (created_at)
```

Project note: this is mock payment storage for a class project. It is not production payment handling.

### `employee_schedules`

Stores employee shifts.

Important fields:

- `id`
- `employee_id`
- `park_id`
- `shift_date`
- `start_time`
- `end_time`
- `assignment`
- `schedule_status`
- `notes`
- `created_by`

Schedule status values:

```sql
ENUM('scheduled','cancelled','completed')
```

Important constraints/indexes:

```sql
FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (park_id) REFERENCES parks(id) ON DELETE RESTRICT
FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
CHECK (start_time < end_time)
INDEX idx_sched_employee_date (employee_id, shift_date)
INDEX idx_sched_park_date (park_id, shift_date)
INDEX idx_sched_status (schedule_status)
```

### `pto_requests`

Stores employee time-off requests.

Important fields:

- `id`
- `employee_id`
- `leave_type`
- `start_date`
- `end_date`
- `reason`
- `pto_status`
- `reviewed_by`
- `reviewed_at`
- `admin_notes`

PTO status values:

```sql
ENUM('pending','approved','denied','cancelled')
```

Important constraints/indexes:

```sql
FOREIGN KEY (employee_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
CHECK (start_date <= end_date)
INDEX idx_pto_employee_status (employee_id, pto_status)
INDEX idx_pto_dates_status (start_date, end_date, pto_status)
INDEX idx_pto_reviewed_by (reviewed_by)
```

---

## Important backend validation choices

Some rules are intentionally handled in PHP instead of triggers.

The schema comments call out these dynamic checks:

- card expiration must not be before the current month/year
- event capacity should not exceed selected field capacity
- booking guest count should not exceed selected field capacity
- active bookings should not overlap for the same field and time range
- attendance guest totals should not exceed event capacity

I kept these in PHP because they depend on current date/time or workflow context and are easier to explain/test in a simple XAMPP project.

---

## Authentication and authorization

Authentication is session-based.

Important helper functions:

- `login_user($user)`
- `logout_user()`
- `current_user($db)`
- `require_login($db)`
- `require_role($db, $roles)`

Private pages use role checks at the top of the file. For example:

```php
$user = require_role($db, 'admin');
```

or:

```php
$user = require_role($db, 'client');
```

The shared account page uses:

```php
$user = require_login($db);
```

If a user is inactive, disabled, or not logged in, the helper functions redirect them appropriately.

---

## Form and security notes

This is a student/capstone project, but it still includes several safe patterns:

- uses PDO prepared statements for user input in database queries
- escapes output with `e()` before printing dynamic values
- hashes passwords with `password_hash()`
- verifies passwords with `password_verify()`
- uses role-based access checks for private pages
- prevents private page caching with headers in protected flows
- uses flash messages for status/error feedback

Important limitations:

- there is no CSRF token system yet
- payment handling is mock/demo only
- CVV/card fields should not be stored in a real production system
- password reset does not send email or use expiring reset tokens
- API key configuration is commented in `ai-api.php` and `map-api.php`; edit those values for your own OpenAI and Google Maps setup
- AI API configuration depends on the local/hosted setup and the OpenAI API key used for the ChatGPT-style chat function
- database credentials are currently local XAMPP defaults in `bootstrap.php`

---

## Password reset flow

This project does not send password reset emails.

The local reset flow is:

1. User opens `forgot-password.php`
2. User enters email, first name, and last name
3. The page checks for an active matching user in `users`
4. If matched, the user ID is temporarily stored in session
5. User is redirected to `reset-password.php`
6. New password is saved with `password_hash()`
7. The reset session value is cleared
8. User logs in normally

This is simpler than token/email reset and works for a local class demo.

---

## Payments and donation notes

Payments are intentionally mock-only.

The current payment design supports:

- reservation payments
- donations
- card method
- in-person method
- transaction reference values
- payment statuses

The schema has card checks for number length, expiration month/year, and CVV format. The PHP also checks that the expiration is not in the past.

For a real production site, this would need a payment processor like Stripe, Square, or another PCI-compliant provider. This project should not be used to store real card numbers.

---

## CSV export notes

The CSV export page is admin-only.

The export is generated directly in PHP and downloaded immediately. Optional `date_from` and `date_to` filters can narrow exports by date where the selected dataset has a date column. This avoids folder permission problems because the server does not need to write CSV files to disk.

This is useful for the project because it demonstrates structured data output without adding storage folders or export log tables.

---

## Notes on pages that are not database-backed yet

Not every page uses database tables.

- `faq.php` stores FAQ content in a PHP array.
- `about.php` is mainly static content.
- Shared headers and footers are now included through `includes/header.php` and `includes/footer.php`.

The FAQ/static pages were kept simple so the project remains easy to follow in a flat PHP submission.

---

## Main upgrades in this build

This section is kept from the original README and updated to match the current project.

- Root-level PHP pages that keep the original static page structure as closely as possible
- Original class names, Bootstrap layout, navigation, footer, and shared stylesheet/script retained
- Dynamic PHP/MySQL wiring added where needed
- Shared account/profile page unified for all roles
- Password reset simplified to a verify-user then reset flow using the `users` table and session state
- Dashboard metrics are query-driven
- Booking/PTO approvals write review metadata
- Booking and schedule overlap validation added
- Payments kept as mock / card-validation-only / in-person support
- CSV export works as a direct download and does not require file-system write permissions
- News page now uses the `news` database table
- Dedicated admin pages handle employee account management and news create/update/delete management
- Database currently uses 10 tables
- FAQ remains a PHP array and can be moved to a database later

---

## Manual testing checklist

Before submitting or demoing, I should click-test these flows in XAMPP:

### Public pages

- Home page loads
- Parks page loads and filters parks
- Events page loads and filters events
- News page loads published news and article buttons open/close
- FAQ page search and topic filter works
- Map page loads park cards/map content
- Donate page loads
- Search page returns parks/events/news results

### Account flows

- Register new client
- Login
- Logout
- Forgot password to reset password flow
- Account/profile update

### Client flows

- Client dashboard loads
- RSVP to a public event
- Cancel RSVP
- Submit private event request
- View booking status
- Submit donation as logged-in client

### Employee flows

- Employee dashboard loads
- Employee schedule loads
- Submit PTO request
- Cancel pending PTO request

### Admin flows

- Admin dashboard loads
- Create employee
- Update employee
- Disable employee
- Create news item
- Update news item
- Delete news item
- Approve booking
- Deny booking
- Confirm approved booking creates/updates private event
- Create employee schedule
- Update employee schedule
- Confirm schedule overlap prevention
- Delete schedule
- Approve PTO
- Deny PTO
- Download CSV export

---

## Common troubleshooting

### Blank page or database error

Check:

- Apache is running
- MySQL is running
- database imported successfully
- database name is `nys_parks`
- credentials in `bootstrap.php` match your MySQL setup

### Login does not work

Check that `db/seed.sql` was imported after `db/schema.sql`.

Use:

```text
admin@nysparks.local
Password123!
```

### News page is empty

Check that the `news` table exists and has published rows:

```sql
SELECT * FROM news WHERE news_status = 'published';
```

### FAQ changes do not appear

FAQ is hardcoded in `faq.php`, not in the database. Edit the `$faqItems` array directly.

### Map does not show Google map

The project has map fallback/helper behavior. If a Google Maps API key is required, configure that separately. The park data itself still comes from the `parks` table.

### Chart does not render

`dashboard-charts.js` depends on Chart.js being loaded. If Chart.js cannot load from CDN, the page shows fallback text.

---

## Future improvements

These are realistic improvements that could be added after the capstone version:

- move FAQ content into a `faqs` database table
- add CSRF tokens to all POST forms
- add stronger server-side validation messages by field
- connect donation/reservation payment to a real payment processor
- replace local password reset with email/token-based reset
- add admin CRUD for parks and events
- expand employee schedule tools beyond the current create/update/delete workflow
- add attendance check-in tools for employees/admins
- continue improving API key setup comments and deployment configuration for OpenAI and Google Maps
- add pagination for news, events, and admin tables
- add image upload support instead of external image URLs

---

## Submission note

This build is intentionally optimized for:

- simple XAMPP setup
- readable PHP files
- clear SQL structure
- database-backed capstone workflows
- public/private page separation
- easy demo accounts
- easier grading and review

The project is not meant to be a production NYS Parks system. It is a polished class/capstone version that shows the main site flow, database design, and role-based workflows in a way that can be run locally.
