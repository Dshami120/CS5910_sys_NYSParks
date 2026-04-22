NYS PARKS HOMEPAGE STARTER
==========================

What is included:
- index.php (homepage)
- includes/config.php
- includes/db.php
- includes/helpers.php
- includes/header.php
- includes/navbar.php
- includes/footer.php
- assets/css/style.css
- assets/js/main.js
- database/homepage_notes.sql

How to run in XAMPP:
1. Copy the folder "nys-parks-homepage" into your htdocs folder.
2. Start Apache and MySQL in XAMPP.
3. Visit:
   http://localhost/nys-parks-homepage/

Database notes:
- config.php is already set for the default XAMPP MySQL account:
  host = localhost
  user = root
  password = empty string
  db name = nys_parks

What happens if the DB is not ready yet?
- The homepage still works.
- It shows fallback demo parks and events.
- A small message on the page tells you whether MySQL is connected.

Why this structure?
- It keeps header, navbar, and footer reusable.
- It is easier to expand page-by-page for your capstone.
- It is beginner-friendly and heavily commented.

Suggested next pages to build:
1. parks.php
2. events.php
3. park-details.php
4. event-details.php
5. donate.php
6. guide.php
7. login.php / register.php


AI chatbot setup (OpenAI API):
- The AI page is ai-guide.php
- The backend endpoint is api/chatbot.php
- Recommended setup: create an OPENAI_API_KEY environment variable for Apache/PHP
- On Windows, after setting the variable, restart Apache in XAMPP
- The chatbot endpoint keeps the API key on the server and never exposes it in browser JavaScript


=============================
AUTH PAGES ADDED IN V10
=============================
New files:
- login.php
- register.php
- logout.php
- account.php
- includes/auth.php

Demo login accounts (when Users table is not ready yet):
- client.demo@nysparks.local / Password123!
- employee.demo@nysparks.local / Password123!
- admin.demo@nysparks.local / Password123!

Live registration notes:
- register.php creates CLIENT accounts only
- employee and admin accounts should be created later in the admin portal
- live login/registration will use the Users table when it exists in MySQL


SQL / database import
---------------------
Use the files inside /database in this order:
1. 00_create_database.sql
2. 01_schema.sql
3. 02_seed.sql

Demo users inserted by the seed:
- client.demo@nysparks.local / Password123!
- employee.demo@nysparks.local / Password123!
- admin.demo@nysparks.local / Password123!

XML tools
---------
Admin users can open admin-xml.php to:
- export parks XML
- export events XML
- export bookings XML
- import parks from XML

Sample XML files are included in /database/xml.
