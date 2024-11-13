# Multivendor E-Commerce System

This is a Laravel-based multivendor e-commerce platform that allows multiple vendors to manage their own stores, products, and orders. Customers can browse and purchase products from different vendors.

## Features

- **Vendor Dashboard**: Vendors can manage their products, view orders, and track sales.
- **Admin Panel**: Admin can manage vendors, users, and product categories, and view sales and analytics.
- **Customer Features**: Customers can register, browse products, add items to the cart, and place orders.
- **Order Management**: Complete order handling from cart to payment and delivery.
- **Product Reviews**: Customers can leave reviews and ratings for products.

## Installation

### Prerequisites

- **PHP**: Version 8.1 or higher
- **Composer**: Dependency manager for PHP
- **MySQL**: Database to store all system data

### Steps to Install

1. Clone the Repository:  
   `git clone https://github.com/Ashar-khursheed/horeca.git
   `cd multivendor-ecommerce`

2. Install Dependencies:  
   Use Composer to install all required PHP dependencies:  
   `composer install`

3. Configure Environment Variables:  
   Copy the `.env.example` file to `.env`:  
   `cp .env.example .env`  
   Open the `.env` file and configure the database, mail, and other necessary environment variables.

4. Generate Application Key:  
   Laravel requires an application key to be set for encryption. Run the following command to generate the key:  
   `php artisan key:generate`

5. Run Migrations:  
   Apply the database migrations to create the necessary tables:  
   `php artisan migrate`

6. Seed the Database (Optional):  
   If you have seed data for products, users, etc., you can run the seeder:  
   `php artisan db:seed`

7. Set Permissions:  
   Ensure that the `storage` and `bootstrap/cache` directories are writable:  
   `chmod -R 775 storage bootstrap/cache`

8. Serve the Application:  
   To run the application locally, use the following command:  
   `php artisan serve`  
   This will serve the application at `http://localhost:8000` by default.

## Features & Admin Access

- **Vendor Login**: Vendors can register and log in to manage their own stores.
- **Admin Login**: Admin login credentials can be set manually in the database or through `php artisan tinker` for initial setup.
- **Role Management**: Admin can assign and manage roles (admin, vendor, customer).

## Common Artisan Commands

- Migrate the Database:  
  `php artisan migrate`

- Seed the Database:  
  `php artisan db:seed`

- Clear Cache:  
  `php artisan cache:clear`

- Create a New Controller:  
  `php artisan make:controller VendorController`

- Run the Queue (if using Laravel queues):  
  `php artisan queue:work`

## Troubleshooting

- **Storage Permissions**: If you encounter any issues related to file uploads or storage, make sure the `storage` folder is writable.  
  `chmod -R 775 storage`

- **Mail Configuration**: Ensure your `.env` file has the correct SMTP or mail driver settings to send order confirmations, vendor notifications, etc.

## Contributing

Feel free to fork this repository and submit pull requests. Please ensure your changes are well-tested and documented.
