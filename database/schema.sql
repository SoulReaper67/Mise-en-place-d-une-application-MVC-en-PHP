CREATE TABLE IF NOT EXISTS agences (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS employes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    telephone VARCHAR(30) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('employe', 'admin') NOT NULL DEFAULT 'employe'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS trajets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agence_depart_id INT UNSIGNED NOT NULL,
    agence_arrivee_id INT UNSIGNED NOT NULL,
    auteur_id INT UNSIGNED NOT NULL,
    depart DATETIME NOT NULL,
    arrivee DATETIME NOT NULL,
    places_totales SMALLINT UNSIGNED NOT NULL,
    places_disponibles SMALLINT UNSIGNED NOT NULL,
    FOREIGN KEY (agence_depart_id) REFERENCES agences(id) ON DELETE RESTRICT,
    FOREIGN KEY (agence_arrivee_id) REFERENCES agences(id) ON DELETE RESTRICT,
    FOREIGN KEY (auteur_id) REFERENCES employes(id) ON DELETE RESTRICT,
    CHECK (agence_depart_id <> agence_arrivee_id),
    CHECK (depart < arrivee),
    CHECK (places_totales > 0 AND places_disponibles <= places_totales),
    INDEX idx_trajets_depart (depart)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
