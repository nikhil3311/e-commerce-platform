# ShopSphere Demo E-Commerce Website

> A considered, full-stack e-commerce experience built with core PHP, MySQL, CSS, and vanilla JavaScript.

ShopSphere is a online store for thoughtfully selected everyday products. It includes a responsive storefront, customer accounts, shopping tools, checkout, order history, reviews, returns, and an admin dashboard for managing the store.

Built by **Nikhil Patil** as part of [developwithnikhil.com](https://developwithnikhil.com).

## Highlights

- Responsive storefront with featured products, categories, best sellers, and product details
- Product search, sorting, category filters, stock visibility, ratings, and discounts
- Session-based customer registration and login with profile management
- Cart, wishlist, checkout, coupon codes, cash-on-delivery demo orders, and order history
- Product reviews and customer return requests
- Admin dashboard for products, categories, users, orders, returns, and shipping stickers
- MySQL relational schema with seeded catalog, customer, order, review, and coupon data
- Security-minded foundations including PDO prepared statements, CSRF protection, password hashing, output escaping, session regeneration, role checks, and server-side validation

## Tech stack

| Layer | Technology |
| --- | --- |
| Backend | PHP 8+ |
| Database | MySQL 8+ |
| Frontend | HTML, CSS, vanilla JavaScript |
| Local server | Apache through XAMPP |
| Database access | PDO with prepared statements |

## Run locally

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL
- PHP 8 or newer
- A web browser

### Setup

1. Clone or download this repository into `C:/xampp/htdocs/`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open phpMyAdmin and import [`database/database.sql`](database/database.sql).
4. Check the credentials in [`config/database.php`](config/database.php). The default XAMPP configuration expects the MySQL `root` user with an empty password.
5. Open the storefront:

	`http://localhost/e-commerce-platform/`

## Demo access

### Admin

- URL: `http://localhost/e-commerce-platform/admin/login.php`
- Email: `admin@shopsphere.test`
- Password: `admin123`

### Customer

- Email: `demo@shopsphere.test`
- Password: `demo123`

The database contains sample products, a delivered order, reviews, and the `RESET15` coupon. Demo payment methods do not process real payments.

> Change all seeded credentials before deploying this project publicly. Do not use the included database credentials in production.

## Project structure

```text
index.php                 Storefront homepage
admin/                    Admin authentication and store operations
api/                      Cart, wishlist, and newsletter endpoints
assets/css/               Application styles
assets/js/                Client-side interactions
config/                   Database configuration
database/database.sql     Schema and demo seed data
includes/                 Shared layout and application helpers
pages/                    Catalog, account, cart, checkout, and order pages
uploads/                  User-uploaded files
```

## Security and production notes

This is a demonstration project, not a production-ready commerce deployment. Before launch, add HTTPS, secure cookie flags, environment-based secrets, rate limiting, email delivery, a real payment gateway, upload hardening, monitoring, and a production backup strategy.

## About the developer

**Nikhil Patil** builds practical web experiences and full-stack projects.

- Portfolio: [developwithnikhil.com](https://developwithnikhil.com)
