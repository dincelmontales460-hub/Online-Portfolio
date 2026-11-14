# Dincel Montales Portfolio

This portfolio has a PHP page, separate CSS and JavaScript files, and MySQL-backed rating and comment CRUD.

## Run locally with XAMPP

1. Start Apache and MySQL in XAMPP.
2. Import `setup.sql` through phpMyAdmin at `http://localhost/phpmyadmin`.
3. Put this folder in XAMPP's `htdocs` and open `http://localhost/dencil-montales/`, or run `php -S localhost:8000` from this folder and open `http://localhost:8000/`.

The default connection uses MySQL at `127.0.0.1`, database `portfolio_feedback`, username `root`, and an empty password. For other credentials, set `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. GitHub Pages cannot run PHP.

Visitors can save, change, or remove a rating. They can add comments, view comments from other visitors, and edit or delete comments created in their current browser session.
