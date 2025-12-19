

# Wallet-Service-API
PHP Developer Technical Task — laravel Task    
========================================================
HOW TO RUN WALLET SERVICE PROJECT (LOCAL SETUP)
========================================================

This file lists all commands required to run the project
step-by-step on a local machine.

--------------------------------------------------------
REQUIREMENTS
--------------------------------------------------------
- PHP 8.2 or higher
- Composer installed
- XAMPP installed
- MySQL service running (via XAMPP)
- Database user: root
- Database password: NONE (empty)

--------------------------------------------------------
STEP 1: INSTALL DEPENDENCIES
--------------------------------------------------------
Open terminal inside the project folder and run:

composer install

--------------------------------------------------------
STEP 2: ENVIRONMENT SETUP
--------------------------------------------------------
Create environment file:

copy .env.example .env

Generate application key:

php artisan key:generate

Clear cached config and routes:

php artisan optimize:clear

--------------------------------------------------------
STEP 3: DATABASE CONFIGURATION
--------------------------------------------------------
Open .env file and confirm the following settings:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wallet_service
DB_USERNAME=root
DB_PASSWORD=

IMPORTANT:
- Database password MUST be empty.
- MySQL must be running from XAMPP.

--------------------------------------------------------
STEP 4: RUN DATABASE MIGRATIONS
--------------------------------------------------------
Create database tables:

php artisan migrate

If Laravel asks:
"The database 'wallet_service' does not exist. Would you like to create it?"

Type:
yes

To reset database completely (optional):

php artisan migrate:fresh

--------------------------------------------------------
STEP 5: RUN THE APPLICATION
--------------------------------------------------------
Start the Laravel development server:

php artisan serve

--------------------------------------------------------
STEP 6: ACCESS THE PROJECT
--------------------------------------------------------
Open browser and visit:

Dashboard:
http://127.0.0.1:8000

Health Check:
http://127.0.0.1:8000/api/health

Expected response:
{ "status": "ok" }

--------------------------------------------------------
OPTIONAL COMMANDS (DEBUGGING)
--------------------------------------------------------
List all routes:

php artisan route:list

--------------------------------------------------------
Postman Collection
--------------------------------------------------------
A complete Postman collection is provided under:

postman/Wallet-Service.postman_collection.json

--------------------------------------------------------
PROJECT IS NOW RUNNING SUCCESSFULLY
--------------------------------------------------------
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://wpgulf.com/wp-content/uploads/2025/12/screencapture-127-0-0-1-8000-2025-12-19-13_01_01.png" width="1000" alt="Laravel Logo"></a></p>

