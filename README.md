# GreenHarvest Farm

Fresh Organic Products from Our Farm to Your Doorstep.

GreenHarvest Farm is a complete PHP e-commerce web application for a modern organic farm. Customers can browse products, view product details, add items to a cart, checkout, select a payment method, and place orders. The admin can manage products, upload product images, view orders, and review dashboard analytics.

## Project Context

- Course: EWA408510 - E-Commerce and Web Application
- Project type: Final Examination Project-Based
- Business idea: Farm e-commerce website for GreenHarvest Farm
- Technology: PHP, PostgreSQL, PDO, HTML, CSS, JavaScript, Bootstrap 5
- Deployment target: Render Docker Web Service with Render PostgreSQL free database

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
- Render deployment configuration in `render.yaml`

## Admin Login

```text
Username: admin
Password: admin3017
```

## Folder Structure

```text
greenharvest-farm/
|-- admin/
|-- assets/
|-- css/
|-- database/
|   |-- greenharvest-postgres.sql
|   |-- greenharvest.sql
|-- includes/
|-- js/
|-- storage/
|-- uploads/
|-- about.php
|-- cart.php
|-- checkout.php
|-- home.php
|-- index.php
|-- login.php
|-- product.php
|-- products.php
|-- signup.php
|-- user-dashboard.php
|-- Dockerfile
|-- docker-compose.yml
|-- render.yaml
`-- README.md
```

## Local Docker Setup

Build and run the PHP app with PostgreSQL:

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

Remove the PostgreSQL database volume and start fresh:

```bash
docker compose down -v
docker compose up --build
```

## Database Files

- `database/greenharvest-postgres.sql` is the main database file for Render PostgreSQL and Docker PostgreSQL.
- `database/greenharvest.sql` is kept only as a backup MySQL version.

## Render Deployment

This project is configured for Render free PostgreSQL.

1. Push the latest code to GitHub.
2. Open Render and choose **New > Blueprint**.
3. Select this GitHub repository.
4. Render will read `render.yaml` and create:
   - Docker Web Service: `greenharvest-farm`
   - Free PostgreSQL database: `greenharvest-db`
5. Wait for the web service and database to finish deploying.
6. Import the PostgreSQL schema into the Render database.

To import using Docker on Windows PowerShell:

```powershell
cd "C:\Users\abdel\Documents\university 3\E commerce and web application\greenharvest-farm"
Get-Content database\greenharvest-postgres.sql | docker run --rm -i postgres:16-alpine psql "PASTE_RENDER_EXTERNAL_DATABASE_URL_HERE"
```

Use the **External Database URL** from the Render PostgreSQL dashboard for the import command. The app itself uses Render's internal `DATABASE_URL` automatically through `render.yaml`.

After import, open the Render web service URL and test:

```text
/login.php
/home.php
/products.php
/cart.php
/admin/dashboard.php
```

## Environment Variables

Local Docker uses these values automatically from `docker-compose.yml`:

```text
DB_DRIVER=pgsql
DB_HOST=db
DB_PORT=5432
DB_NAME=greenharvest_farm
DB_USER=greenharvest_user
DB_PASS=greenharvest_pass
ADMIN_USER=admin
ADMIN_PASS=admin3017
```

Render uses:

```text
DB_DRIVER=pgsql
DATABASE_URL=<provided automatically by Render PostgreSQL>
ADMIN_USER=admin
ADMIN_PASS=admin3017
PORT=80
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

Note: Render free web services have an ephemeral filesystem, so uploaded images may be lost after redeploy or restart. For a student demo this is acceptable evidence; for production, use persistent storage or cloud object storage.

## Security Notes

- PDO prepared statements are used for database queries.
- Output is escaped with `htmlspecialchars`.
- Product image upload validates file type.
- Allowed image types: JPG, JPEG, PNG, WEBP.
- Uploaded files are renamed to avoid conflicts.
- Admin pages are protected by session login.

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
- Render web service evidence
- Render PostgreSQL evidence

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
12. Render deployment
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
