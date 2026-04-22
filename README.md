# NYS Parks & Recreation Portal — Flat PHP/MySQL Build

This version is rebuilt for simple local use in XAMPP and easy handoff for a capstone submission.

## Folder shape

Only these files/folders are used:

- root `*.php` page files
- `styles.css`
- `app.js`
- `schema.sql`
- `seed.sql`
- `README.md`
- `bootstrap.php` for DB connection, auth/session, and helper functions

There are no controller folders, view folders, storage folders, XML write folders, or public subfolders.

## Main upgrades in this build

- Root-level PHP pages that keep the original static page structure as closely as possible
- Original class names, Bootstrap layout, navigation, footer, and shared stylesheet/script retained
- Dynamic PHP/MySQL wiring added only where needed
- Shared account/profile page unified for all roles
- Password reset simplified to a verify-user then reset flow using the `users` table and session state
- Dashboard metrics are query-driven
- Booking/PTO approvals write review metadata
- Booking and schedule overlap validation added
- Payments kept as mock / card-validation-only / in-person support
- XML export works as a direct download and does not require file-system write permissions
- Database simplified to 9 tables

## Database tables

This build uses 9 tables:

1. `parks`
2. `users`
3. `fields`
4. `events`
5. `bookings`
6. `employee_schedules`
7. `pto_requests`
8. `payments`
9. `attendance`

Removed from the earlier draft:
- saved parks
- news posts
- password reset tokens
- XML export log table

## Default seeded accounts

Password for all seeded accounts:

`Password123!`

Users:
- admin: `admin@nysparks.local`
- employee: `employee@nysparks.local`
- client: `client@nysparks.local`

## Setup in XAMPP

1. Copy the folder into `htdocs`, for example:
   `C:\xampp\htdocs\nys-parks-final-flat`

2. Start Apache and MySQL in XAMPP.

3. Open phpMyAdmin and import `schema.sql`.

4. Import `seed.sql`.

5. Edit DB settings in:

   `bootstrap.phpdb.php`

   Default values are:

   - host: `127.0.0.1`
   - port: `3306`
   - dbname: `nys_parks`
   - username: `root`
   - password: empty string

6. Open:

   `http://localhost/nys-parks-final-flat/index.php`

## Main workflows

### Public
- Browse parks
- Browse events
- Read news/about/faq
- Donate with mock payment validation

### Client
- Register
- Log in
- Update account
- Submit booking requests
- View booking statuses on dashboard

### Employee
- Log in
- View dashboard
- View schedule
- Submit PTO requests

### Admin
- Log in
- View query-driven dashboard stats
- Create/update/disable employee accounts
- Create/delete employee schedules
- Approve/deny PTO requests
- Approve/deny booking requests
- Download XML exports directly

## Validation and backend notes

### Password reset
This build does **not** send email.
Instead:
- user enters email + first name + last name on `forgot-password.php`
- if that matches a user record, session verification is set
- user is redirected to `reset-password.php`
- password is updated directly in `users`

### Payments
Payments are mock-only for class/project use:
- card numbers are validated by digit length only
- expiration must be this month or later
- only last 4 digits are stored
- CVV is not stored
- in-person / pledge option is supported

### Approvals and audit data
- bookings store `reviewed_by`, `reviewed_at`, and `admin_notes`
- PTO requests store `reviewed_by`, `reviewed_at`, and `admin_notes`

### Overlap checks
- booking requests check for overlapping field reservations
- employee schedules check for overlapping shifts for the same employee
- PTO requests check for overlapping pending/approved requests

## Still worth testing manually before submission

Because this was generated as a project rebuild, click-test these in your XAMPP setup:

- register
- login/logout
- forgot password → reset password
- client booking request
- employee PTO request
- admin schedule create/delete
- admin booking approval
- admin PTO approval
- account/profile update
- XML export download

## Submission note

This build is intentionally optimized for:
- simpler file layout
- easier XAMPP use
- clearer PHP/SQL review
- closer alignment to the original static front end
