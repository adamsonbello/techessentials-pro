-- Créer la base (si pas encore existante)
CREATE DATABASE IF NOT EXISTS techessentials CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techessentials;

-- Table des reviews (produits testés)
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  slug VARCHAR(100) UNIQUE NOT NULL, -- ex: "anker-737-review"
  title_en VARCHAR(255) NOT NULL,
  title_fr VARCHAR(255) NOT NULL,
  image VARCHAR(255) NOT NULL,
  excerpt_en TEXT,
  excerpt_fr TEXT,
  content_en LONGTEXT,
  content_fr LONGTEXT
);

-- Table des avis utilisateurs
CREATE TABLE IF NOT EXISTS review_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  review_id INT NOT NULL,
  username VARCHAR(100) NOT NULL,
  rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
  comment TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (review_id) REFERENCES reviews(id) ON DELETE CASCADE
);
