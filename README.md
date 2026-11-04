# Touche pas au klaxon

Application intranet pour proposer et consulter des trajets de covoiturage entre agences.

## Installation

Prérequis : PHP 8.2 ou supérieur avec PDO MySQL et mbstring, Composer 2, MySQL 8.0.16+ ou MariaDB 10.6+. Node.js et npm servent à recompiler les styles. Les fichiers compilés sont fournis.

1. Depuis la racine du projet, lancer `composer install`.
2. Créer une base vide nommée `klaxon` et un compte SQL local qui a les droits sur cette base. Par exemple, dans le client SQL administrateur :

   ```sql
   CREATE DATABASE klaxon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'klaxon'@'127.0.0.1' IDENTIFIED BY 'votre-mot-de-passe-local';
   GRANT ALL ON klaxon.* TO 'klaxon'@'127.0.0.1';
   ```

3. Définir les variables dans le terminal utilisé pour lancer les commandes. Exemple PowerShell :

   ```powershell
   $env:DB_DSN = 'mysql:host=127.0.0.1;port=3306;dbname=klaxon;charset=utf8mb4'
   $env:DB_USER = 'klaxon'
   $env:DB_PASSWORD = 'votre-mot-de-passe-local'
   php database/init.php
   php database/seed.php
   php -S 127.0.0.1:8000 -t public
   ```

4. Ouvrir http://127.0.0.1:8000. Si PHP ne charge pas encore PDO MySQL et mbstring, activer `extension=pdo_mysql` et `extension=mbstring` dans le fichier indiqué par `php --ini`. Sous Linux ou macOS, exporter les mêmes variables avec `export`.

Le serveur intégré sert à la démonstration locale. Pour un serveur intranet, définir `public/` comme racine web, rediriger les routes applicatives vers `public/index.php` et activer HTTPS. Remplacer les comptes de démonstration par le mécanisme d’authentification interne avant un usage réel.

## Comptes de démonstration

| Profil | Adresse email | Mot de passe local |
| --- | --- | --- |
| Employé | employe@example.test | Klaxon-Employe-2026! |
| Administrateur | admin@example.test | Klaxon-Admin-2026! |

L’accueil public montre les trajets futurs avec des places libres, par départ croissant. L’employé connecté peut voir le contact dans une fenêtre modale, créer un trajet et gérer ceux dont il est l’auteur. L’administrateur accède à tous les trajets, aux agences et à la liste des employés en lecture seule.

## Import des annexes

Le jeu de démonstration charge les villes fournies et deux comptes fictifs. Les quatre trajets de test sont créés avec des dates relatives lors du premier chargement. Un trajet complet et un trajet passé permettent de vérifier les filtres de l’accueil.

Pour reproduire le référentiel RH de l’annexe en local, copier `agences.txt` et `users.txt` dans `database/import/`, puis exécuter `php database/seed.php --annexes`. Les exports restent exclus du dépôt. Les comptes importés reçoivent un mot de passe aléatoire non communiqué. Utiliser les deux comptes de démonstration pour l’évaluation.

## Reprise du code

| Emplacement | Responsabilité |
| --- | --- |
| `public/index.php` | Point d’entrée, création des dépendances et session |
| `config/routes.php` | Liste des chemins et des méthodes HTTP |
| `src/Controller/` | Contrôle des droits, orchestration et redirections |
| `src/Model/` | Requêtes PDO et persistance |
| `src/Service/TripValidator.php` | Validation métier des trajets |
| `views/` | Gabarits et affichage échappé |
| `assets/site.scss` | Variables de la palette, import Bootstrap et styles |
| `database/` | Schéma SQL et script de données |
| `tests/` | Contrôles PHPUnit |

Les données de contact sont déterminées côté serveur à partir du compte auteur. Les écritures utilisent des formulaires POST avec un jeton CSRF et des requêtes préparées. Une écriture réussie renvoie vers la liste concernée avec un message flash. La base protège les agences liées à des trajets. Le fuseau utilisé est Europe/Paris.

Pour modifier la palette, éditer les variables Sass avant l’import Bootstrap, puis exécuter `npm ci` et `npm run build`.

## Vérifications

- `composer analyse` lance PHPStan au niveau 6.
- `composer test` exécute les tests unitaires de validation.
- Pour inclure les tests d’écriture, créer une base séparée `klaxon_test`, accorder les droits au compte de test et initialiser son schéma avec `database/init.php` en pointant temporairement `DB_DSN` vers cette base. Renseigner ensuite `TEST_DB_DSN`, `TEST_DB_USER` et `TEST_DB_PASSWORD`, puis relancer `composer test`. Les écritures de test sont annulées après chaque test.

Pour vérifier les droits à la main : en visiteur, contrôler que les détails sont masqués. En employé, créer un trajet puis le modifier et le supprimer. En administrateur, vérifier les quatre trajets initiaux, y compris les trajets complet et passé, puis créer, renommer et supprimer une agence de test.

