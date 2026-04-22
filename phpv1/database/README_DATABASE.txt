NYS Parks & Recreation Database Files
====================================

Import these in phpMyAdmin or the MySQL command line in this order:

1. 00_create_database.sql
2. 01_schema.sql
3. 02_seed.sql

Demo login accounts inserted by the seed:
- client.demo@nysparks.local / Password123!
- employee.demo@nysparks.local / Password123!
- admin.demo@nysparks.local / Password123!

The schema matches the PHP pages in this capstone build.
The Analytics_Daily table powers the admin traffic charts when populated.
The XML folder contains sample XML files for the admin XML import/export tools.
