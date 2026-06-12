# GreenHarvest Farm

Fresh Organic Products from Our Farm to Your Doorstep.

GreenHarvest Farm is a complete PHP and MySQL e-commerce web application for a modern organic farm. Customers can browse products, view product details, add items to a cart, checkout, select a payment method, and place orders. The admin can manage products, upload product images, view orders, and review dashboard analytics.

## Project Context

- Course: EWA408510 - E-Commerce and Web Application
- Project type: Final Examination Project-Based
- Business idea: Farm e-commerce website for GreenHarvest Farm
- Technology: PHP, MySQL, PDO, HTML, CSS, JavaScript, Bootstrap 5

## Features

- Responsive modern farm e-commerce design
- Login page as the default entry page
- Continue without login guest option
- Customer sign up and login
- Customer dashboard
- Product listing with search and category filter
- Product details page
- Shopping cart with add, remove, update quantity, and totals
- Checkout with saved customer details for logged-in users
- Payment methods: Cash on Delivery, MTN MoMo, Airtel Money
- Order confirmation page
- Admin login and protected admin dashboard
- Product add/edit/delete with image upload
- Uploaded product images stored in `uploads/products/`
- Orders management and order details
- Dockerfile and docker-compose setup
- GitHub Actions CI workflow

## Admin Login

```text
Username: admin
Password: admin3017
```

## Folder Structure

```text
greenharvest-farm/
├── admin/
├── assets/
├── css/
├── database/
├── includes/
├── js/
├── storage/
├── uploads/
├── about.php
├── cart.php
├── checkout.php
├── guest.php
├── home.php
├── index.php
├── login.php
├── logout.php
├── order-success.php
├── product.php
├── products.php
├── signup.php
├── user-dashboard.php
├── Dockerfile
├── docker-compose.yml
└── README.md
```

## Local Setup With XAMPP

1. Start Apache and MySQL from XAMPP.
2. Open MySQL Workbench.
3. Import the SQL file:

```text
database/greenharvest.sql
```

4. Check database settings in:

```text
includes/db.php
```

Default local settings:

```text
Host: localhost
Port: 3307
Database: greenharvest_farm
User: root
Password: 1234
```

5. Start the PHP development server from the project folder:

```bash
cd greenharvest-farm
php -S localhost:8000
```

6. Open the project:

```text
http://localhost:8000/
```

## Docker Setup

Build and run:

```bash
docker compose up --build
```

Open:

```text
http://localhost:8080/
```

Stop containers:

```bash
docker compose down
```

Remove database volume and start fresh:

```bash
docker compose down -v
docker compose up --build
```

## GitHub Actions CI

The CI workflow is stored in:

```text
.github/workflows/ci.yml
```

It checks PHP syntax and verifies that the Docker image can build.

## Product Images

Product images are uploaded from the admin dashboard. The files are stored in:

```text
uploads/products/
```

The database stores the image path. Public pages display the database image path. If a product has no image, the website shows a placeholder.

## Security Notes

- PDO prepared statements are used for database queries.
- Output is escaped with `htmlspecialchars`.
- Product image upload validates file type.
- Allowed image types: JPG, JPEG, PNG, WEBP.
- Uploaded files are renamed to avoid conflicts.
- Admin pages are protected by session login.

## GitHub Upload Commands

```bash
git init
git add .
git commit -m "Initial GreenHarvest Farm e-commerce project"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/greenharvest-farm.git
git push -u origin main
```

## Deployment Notes

For a PHP and MySQL project, deploy to a hosting provider that supports PHP 8 and MySQL. After upload:

- Import `database/greenharvest.sql` into the production database.
- Update database credentials in environment variables or `includes/db.php`.
- Make sure `uploads/products/` is writable.
- Use the deployment URL in the final report.

## Screenshots Checklist

- Login page
- Signup page
- Homepage
- Products page
- Product details page
- Cart page
- Checkout page with payment methods
- Order success page
- Customer dashboard
- Admin dashboard
- Admin products page
- Add product page with image upload
- Admin orders page
- Admin order details page
- Docker running evidence
- GitHub Actions CI evidence

## Report Structure

1. Introduction
2. Project objectives
3. System users
4. Technologies used
5. System architecture
6. Database design
7. Main features
8. Screenshots
9. Security measures
10. Docker setup
11. GitHub and CI/CD
12. Deployment
13. Challenges faced
14. Future improvements
15. Conclusion

## Future Improvements

- Real MTN MoMo and Airtel Money API integration
- Email order notifications
- Admin payment status update
- Product reviews
- Customer order history
- Delivery tracking
