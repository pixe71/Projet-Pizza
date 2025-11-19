# **PROJETPIZZA 🍕**

**The entire web interface is currently in French.**

PHP application for pizza order management with an admin area.

## 🚀 Features

* Customer-side order creation and tracking
* Authentication (login/logout)
* Order history
* Admin area: dashboard, daily orders, order details, statistics

## 🗂️ Project Structure

```
PROJETPIZZA/
├─ admin/
│  ├─ commandes_jour.php
│  ├─ dashboard.php
│  ├─ details_commande.php
│  └─ stats.php
├─ config/
│  ├─ database.php
│  └─ auth.php
├─ commander.php
├─ detail_commande.php
├─ index.php
├─ login.php
├─ logout.php
└─ mes_commandes.php
```

* `config/database.php`: PDO/MySQL connection (host, db, user, password).
* `config/auth.php`: authentication helpers (session, roles).
* `index.php`: homepage / list of pizzas.
* `commander.php`: order creation.
* `detail_commande.php`: order details (customer side).
* `mes_commandes.php`: logged-in user’s order history.
* `login.php` / `logout.php`: authentication.
* `admin/*`: views and actions restricted to administrators.

## 🧰 Requirements

* PHP 8.x
* MySQL/MariaDB
* Web server (Apache/Nginx) or PHP built-in server
* Composer (if dependencies are used)

## ⚙️ Configuration

1. Create the database and import the schema (minimal example to adapt):

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(190) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pizzas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(190) NOT NULL,
  price DECIMAL(6,2) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(8,2) NOT NULL,
  status ENUM('pending','preparing','delivering','done','cancelled') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  pizza_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(6,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (pizza_id) REFERENCES pizzas(id)
);
```

2. Fill in the DB credentials in `config/database.php`:

```php
<?php
$dsn = 'mysql:host=localhost;dbname=projetpizza;charset=utf8mb4';
$user = 'root';
$pass = 'secret';
$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];
$pdo = new PDO($dsn, $user, $pass, $options);
```

3. Configure authentication in `config/auth.php` (sessions, roles, redirects).

## ▶️ Run Locally

* Using the built-in PHP server:

```bash
php -S localhost:8000
```

Then open `http://localhost:8000/index.php`.

* Or through Apache/Nginx by pointing the VirtualHost to the project root.

## 🔐 Security Best Practices

* Use `password_hash()` / `password_verify()` for passwords
* PDO prepared statements and input validation/sanitization
* Role-based access control for `/admin/*`
* CSRF token for sensitive forms
* Session ID regeneration after login
* `display_errors=Off` in production, separate log files
* HTTPS + security headers (HSTS, minimal CSP)

## 👩‍💻 User Flow

* Visitor: browses `index.php`, chooses pizzas, places an order via `commander.php`
* Logged-in user: tracks orders in `mes_commandes.php`, views `detail_commande.php`
* Admin: `admin/dashboard.php`, `admin/commandes_jour.php`, `admin/details_commande.php`, `admin/stats.php`

## 🧪 Tests

* Add tests (PHPUnit) for `config/auth.php` and database access
* Manual scenarios: registration/login, order creation, status updates, admin views

## 📦 Deployment

* Environment variables (DSN, user, password)
* SQL migrations
* HTTPS VirtualHost
* Minimal write permissions (avoid 777)

## 🗺️ Roadmap / TODO

* Persistent cart
* Confirmation emails
* Order filtering/search
* CSV export for admin
* Statistics charts
* Internationalization (i18n)

## 📄 License

MIT

---

If you want, I can also produce a **fully English README.md** formatted for GitHub.
