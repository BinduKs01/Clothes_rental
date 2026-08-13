# Clothing Rental REST API

A complete production-ready REST API backend using Core PHP 8+, MySQL (PDO), and JWT authentication.

## Prerequisites

* XAMPP (or any PHP 8+ / MySQL environment)
* Composer

## Setup Instructions

1. **Clone/Download the repository** to your XAMPP `htdocs` directory (e.g., `htdocs/clothing-rental-api`).
2. **Install Dependencies**: Open a terminal in the project directory and run:
   ```bash
   composer install
   ```
3. **Database Configuration**:
   * Open phpMyAdmin (`http://localhost/phpmyadmin`).
   * Import the provided `database.sql` file. This will create the `clothing_rental` database and all required tables with some sample data.
   * If your MySQL credentials are not `root` with an empty password, update `config/database.php` accordingly.
4. **Start Apache and MySQL** from the XAMPP Control Panel.
5. **Test APIs in Postman**:
   * Import `postman_collection.json` into Postman.
   * Make sure the API base URL matches your local setup (e.g., `http://localhost/clothing-rental-api/api/...`).
   * For protected routes, use the Bearer Token received from the Login API.

## Features

* **Authentication**: JWT based Login and Registration.
* **Products**: Full CRUD and partial text search.
* **Categories**: Retrieve categories and filter products.
* **Wishlist**: Add, view, and remove. No duplicates allowed.
* **Orders**: Rental order placement with date validation, status management, and dynamic pricing based on days.
