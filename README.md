# Projet-Pizza

<think>
**Preparing Markdown content**

The user wants the code formatted in Markdown, likely to copy-paste easily. They’ve previously received a README content, so I need to ensure I deliver that as a Markdown code block. I'll include necessary elements like headings, badges if applicable, and instructions, while also incorporating French language as requested. I think it's best to provide a concise yet complete output in a single code block, along with a screenshot of the tree matching. Let's get that ready!
</think>

```markdown
# PROJETPIZZA 🍕

Application PHP pour la prise de commandes de pizzas avec espace administrateur.

## 🚀 Fonctionnalités

- Création et suivi de commande côté client
- Authentification (login/logout)
- Historique de mes commandes
- Espace admin : tableau de bord, commandes du jour, détails, statistiques

## 🗂️ Arborescence

```
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
```
- `config/database.php` : connexion PDO/MySQL (hôte, base, user, mdp).
- `config/auth.php` : helpers d’authentification (session, rôles).
- `index.php` : page d’accueil / liste des pizzas.
- `commander.php` : création d’une commande.
- `detail_commande.php` : détails d’une commande (côté client).
- `mes_commandes.php` : historique de l’utilisateur connecté.
- `login.php` / `logout.php` : authentification.
- `admin/*` : vues et actions réservées à l’administrateur.

## 🧰 Prérequis

- PHP 8.x
- MySQL/MariaDB
- Serveur web (Apache/Nginx) ou serveur embarqué PHP
- Composer (si des dépendances existent)

## ⚙️ Configuration

1) Créer la base de données et importer le schéma (exemple minimal à adapter) :

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

2) Renseigner les identifiants DB dans `config/database.php` :

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

3) Configurer l’authentification dans `config/auth.php` (sessions, rôles, redirections).

## ▶️ Lancer en local

- Avec le serveur PHP intégré :
```bash
php -S localhost:8000
```
Puis ouvrir `http://localhost:8000/index.php`.

- Ou via Apache/Nginx en pointant le VirtualHost sur la racine du projet.

## 🔐 Bonnes pratiques sécurité

- `password_hash()` / `password_verify()` pour les mots de passe
- Requêtes préparées PDO et validation/assainissement des entrées
- Contrôle d’accès par rôle pour `/admin/*`
- Token CSRF pour les formulaires sensibles
- Régénération d’ID de session après login
- `display_errors=Off` en prod, logs séparés
- HTTPS + en-têtes de sécurité (HSTS, CSP minimale)

## 👩‍💻 Parcours utilisateur

- Visiteur : consulte `index.php`, choisit des pizzas, passe commande via `commander.php`
- Utilisateur connecté : suit ses commandes dans `mes_commandes.php`, voit `detail_commande.php`
- Admin : `admin/dashboard.php`, `admin/commandes_jour.php`, `admin/details_commande.php`, `admin/stats.php`

## 🧪 Tests

- Ajouter des tests (PHPUnit) pour `config/auth.php` et l’accès DB
- Scénarios manuels : inscription/login, création commande, changement de statut, vues admin

## 📦 Déploiement

- Variables d’environnement (DSN, user, mdp)
- Migrations SQL
- VHost HTTPS
- Droits d’écriture minimaux (éviter 777)

## 🗺️ Roadmap / TODO

- Panier persistant
- Emails de confirmation
- Filtrage/recherche des commandes
- Export CSV côté admin
- Graphiques de stats
- Internationalisation (i18n)

## 📄 Licence

Préciser la licence (MIT, GPL‑3.0, …).
```
