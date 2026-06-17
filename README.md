# GreenHarvest Farm

Fresh Organic Products from Our Farm to Your Doorstep.

GreenHarvest Farm is a complete PHP e-commerce web application for a modern organic farm. Customers can browse products, view product details, add items to a cart, checkout, select a payment method, and place orders. The admin can manage products, upload product images, view orders, and review dashboard analytics.

## Project Context

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

## Security Notes

- PDO prepared statements are used for database queries.
- Output is escaped with `htmlspecialchars`.
- Product image upload validates file type.
- Allowed image types: JPG, JPEG, PNG, WEBP.
- Uploaded files are renamed to avoid conflicts.
- Admin pages are protected by session login.

## Screenshots Checklist

- Login page
  
<img width="1867" height="902" alt="image" src="https://github.com/user-attachments/assets/6fde3eb8-179d-4e3a-9223-557f347dbf29" />

- Homepage
  <img width="1870" height="907" alt="image" src="https://github.com/user-attachments/assets/3210d3c4-7608-48b6-a40d-3a334e939d0b" />

- Products page
  <img width="1872" height="906" alt="image" src="https://github.com/user-attachments/assets/90d2a7dc-8f6f-45a3-93a3-82b5d960b2e0" />

- Product details page
  <img width="1876" height="906" alt="image" src="https://github.com/user-attachments/assets/efdaf290-a16b-416e-9cdc-b1706e91fce4" />

- Cart page
  <img width="1873" height="898" alt="image" src="https://github.com/user-attachments/assets/6add01cb-5058-4a56-bf53-076fdfcb874e" />

- Checkout page with payment methods
  <img width="1866" height="897" alt="image" src="https://github.com/user-attachments/assets/cb32b125-e201-46a9-a258-a45d8f2079ef" />

- Order success page
  <img width="1891" height="911" alt="image" src="https://github.com/user-attachments/assets/f6d1f0f4-024a-4008-9988-990af58c14bf" />

- Customer dashboard
  <img width="1867" height="898" alt="image" src="https://github.com/user-attachments/assets/a06411a0-1200-482a-817f-68c9bffef871" />

- Admin dashboard
  <img width="1872" height="898" alt="image" src="https://github.com/user-attachments/assets/95ba1783-74e6-4999-81ed-d5f051203440" />

- Admin products page
  <img width="1872" height="901" alt="image" src="https://github.com/user-attachments/assets/a96872aa-4932-40a1-9c34-a7b37810f234" />

- Add product page with image upload
  <img width="1875" height="906" alt="image" src="https://github.com/user-attachments/assets/e280114d-3c38-4f8b-ba12-b9e0ccafa1db" />

- Admin orders page
  <img width="1876" height="906" alt="image" src="https://github.com/user-attachments/assets/2bc0a51e-ac0c-4fa9-a0f2-2f4f3dae23ff" />



## Future Improvements

- Real MTN MoMo and Airtel Money API integration
- Email order notifications
- Admin payment status update
- Product reviews
- Customer order history
- Delivery tracking

  ## Live linke :
  https://github.com/abdelrhman-hamed7/greenharvest-farm
