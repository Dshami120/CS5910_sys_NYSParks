# NYS Parks & Recreation Portal
Capstone Project

## Project Overview
This project is a multi-role web portal prototype for New York State Parks & Recreation. It is designed as a capstone project to demonstrate full-stack planning, role-based workflows, shared UI design, dashboard logic, and database-driven feature structure.

The site supports three primary user roles:
- Client
- Employee
- Admin

It also includes a set of public-facing pages for general visitors.

At its current stage, the project is a front-end prototype with page structure, navigation, styling, dashboard layouts, and placeholder workflow logic already in place. It is designed so PHP and SQL can be connected next to power authentication, CRUD actions, search/filtering, approvals, charts, and real user data.

## Project Goals
The main goals of this capstone are:
- Build a professional multi-page website with a consistent UI
- Support multiple user roles with different permissions and page access
- Simulate real business workflows for parks, events, staffing, and booking approvals
- Prepare the project for future backend integration using PHP and SQL
- Demonstrate dashboard design, admin workflows, and role-based navigation

## Current Roles and Permissions

### Public Visitor
A general site visitor can:
- View the homepage
- Browse parks
- Browse events
- View the map
- Use the AI page
- Read news
- View About and FAQ pages
- Access donate page
- Access login, register, and forgot password pages

### Client
A client can:
- Register for an account
- Log in through the shared login page
- View the client dashboard
- View booked events and booking request status
- Open the create-event request page and submit a booking request form

### Employee
An employee can:
- Log in through the shared login page
- View the employee dashboard
- View their schedule
- Create PTO requests

### Admin
An admin can:
- Log in through the shared login page
- View analytics and notifications on the admin dashboard
- Create, update, delete, and reset employee account information from the admin dashboard
- Manage employee schedules through the admin employee schedule page
- Approve or deny employee PTO requests
- Approve or deny client booking requests

Important role rules in the current design:
- Only clients register through the register page
- Admin accounts are intended to be created by the developer/backend
- Admin manages employee accounts
- There is no separate admin-schedule page in the final intended logic; schedule management is handled through `admin-employee-schedule.html`

## Current Site Structure

### Public Pages
- `index.html`
- `parks.html`
- `events.html`
- `map.html`
- `ai.html`
- `news.html`
- `about.html`
- `faq.html`
- `donate.html`
- `login.html`
- `forgot-password.html`
- `register.html`
- `logout.html`
- `account.html`

### Client Pages
- `client-dashboard.html`
- `client-create-event.html`

### Admin Pages
- `admin-dashboard.html`
- `admin-employee-schedule.html`
- `admin-pto.html`
- `admin-bookings.html`
- `admin-xml.html`

### Employee Pages
- `employee-dashboard.html`
- `employee-schedule.html`
- `employee-pto.html`

### Shared Files
- `styles.css`
- `app.js`

## Key Features Currently Implemented

### Shared Site Features
- Consistent global navigation
- Shared footer
- Shared stylesheet
- Shared JavaScript file
- Responsive Bootstrap-based layout
- Public site structure for main informational pages

### Client Features
- Dashboard for booked events and booking status
- Create event request form page
- Booking activity chart section
- Chart filter UI ready for backend wiring

### Admin Features
- Admin dashboard with:
  - analytics cards
  - bookings chart
  - notification panels
  - employee account CRUD UI
- Admin employee schedule management page
- PTO approval page
- Client booking approval page
- Chart filter UI ready for backend wiring

### Employee Features
- Employee dashboard
- Employee schedule page
- Employee PTO page
- Quick actions and internal navigation

### Auth Pages
- Login page
- Register page
- Forgot password page

These auth pages were intentionally restored to a preferred visual version and should be treated as the design reference for authentication screens.

## Technologies Used
- HTML5
- CSS3
- Bootstrap 5
- Bootstrap Icons
- JavaScript
- Planned: PHP
- Planned: MySQL / SQL database

## How the Project Works Right Now
Right now, the project functions as a static front-end prototype.

This means:
- pages load and navigate correctly
- dashboards display UI components
- forms and buttons exist visually
- charts are visual mockups
- filters are visual UI placeholders
- CRUD actions are represented in the interface
- approval workflows are represented in the interface

However, the following are not yet fully functional unless connected to backend logic:
- authentication
- authorization
- client-only registration enforcement
- saving account information
- creating real employee accounts
- schedule CRUD persistence
- PTO request persistence
- booking request persistence
- admin approvals
- chart data from the database
- filter queries against SQL data

## What Is Still Needed to Fully Run as a Real Application

### Backend
The next major step is building the backend in PHP so the forms and workflows actually work.

Recommended backend work:
- create login authentication logic
- create session-based role access control
- enforce client-only registration
- connect forms to SQL tables
- validate user input
- handle CRUD actions for employee accounts
- handle CRUD actions for schedules
- handle PTO request submission and approval
- handle booking request submission and approval
- populate charts from database queries
- wire filters to SQL-based results

### Database
A SQL database is needed to store and manage:
- users
- clients
- employees
- admins
- schedules
- PTO requests
- booking requests
- events
- parks
- analytics-related counts if desired

The exact schema should follow the capstone design/concept docs and database planning documents already created for the project.

### Suggested PHP Features
- `login.php`
- `register.php`
- `logout.php`
- `forgot-password.php`
- `create-booking.php`
- `approve-booking.php`
- `approve-pto.php`
- `employee-account-crud.php`
- `employee-schedule-crud.php`

## How to Run the Project Right Now

### Option 1: Open as Static Files
Because the current site is still front-end based, it can be run simply by opening `index.html` in a browser.

Best simple workflow:
1. Extract the project zip
2. Open the project folder
3. Open `index.html` in a browser

### Option 2: Use a Local Development Server
For cleaner local testing, use a simple local server.

Examples:
- VS Code Live Server
- XAMPP
- MAMP
- WAMP
- Python simple server

If using Python:
```bash
python -m http.server 8000
```

Then open:
```bash
http://localhost:8000
```

### Option 3: Use a PHP Local Server Later
Once backend files are added, use:
```bash
php -S localhost:8000
```

Then open:
```bash
http://localhost:8000
```

## File Notes

### `styles.css`
This is the shared stylesheet for the entire site.
It includes:
- theme variables
- nav styles
- auth page styles
- dashboard cards
- charts
- quick actions
- footer layout
- shared components

The project uses Bootstrap plus custom CSS. It is not pure Bootstrap.

### `app.js`
This is the shared JavaScript file.
At the current stage, JavaScript is mostly used for light front-end behavior and placeholder/demo support. It is not yet handling real backend workflows.

## Current Project Strengths
- Clear multi-role structure
- Strong visual consistency
- Good separation of client, employee, and admin workflows
- Dashboard and capstone-style business logic are already planned in the UI
- Easy to connect to PHP/SQL later
- Includes admin operational workflows, not just public pages

## Current Project Limitations
- Mostly static front-end right now
- No live database connection yet
- No actual authentication yet
- No real CRUD persistence yet
- Chart filters are UI only
- Approve/deny buttons are UI only
- Some values in dashboards are demo values
- Some features depend on future backend implementation

## Recommended Next Development Steps
1. Connect the project to a local PHP environment
2. Build the SQL schema based on the design/database docs
3. Implement authentication and session handling
4. Restrict pages by role
5. Connect all forms to backend handlers
6. Populate dashboard values from SQL queries
7. Implement filtering for charts and tables
8. Add validation, error handling, and success messages
9. Clean final naming and deployment structure if needed

## Capstone Framing
This project is intended to demonstrate:
- planning and design thinking
- database-oriented feature architecture
- user-role separation
- dashboard design
- administrative workflows
- front-end implementation quality
- readiness for backend integration

It should be presented as a structured prototype that already solves the UI/UX and workflow side of the system, while leaving live server/database behavior as the next implementation phase.

## Suggested Presentation Summary
This capstone project is a role-based parks and recreation management portal for New York State Parks. It includes public informational pages, a client booking workflow, an employee schedule and PTO workflow, and an administrative dashboard for analytics, account management, scheduling, and approvals. The current version is a front-end prototype built with HTML, CSS, Bootstrap, and JavaScript, and it is prepared for backend expansion with PHP and SQL.

## Author
Capstone project by the student/developer.

## License / Use
This project is intended for academic/capstone use unless otherwise specified.
