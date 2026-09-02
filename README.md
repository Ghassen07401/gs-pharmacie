# Système de Gestion de Pharmacie

Projet Technologies Web 2A — ESPRIT — Année 2025-2026

Application PHP 8 / MVC "fait maison" (aucun framework), utilisant **PDO uniquement**
(pas de MySQLi), avec back-office (Responsable, Pharmacien) et front-office (Client)
responsifs.

## Fonctionnalités couvertes

**Responsable Pharmacie**
- CRUD complet des médicaments (ajout, modification, suppression)
- Recherche multicritère (nom, catégorie, fabricant, prix, ordonnance requise, stock critique)
- Rapport des stocks critiques
- Rapport des expéditions / ventes récentes
- Validation/refus des ordonnances (comme le pharmacien)
- Gestion des comptes utilisateurs (CRUD, tous rôles)
- Gestion des interactions médicamenteuses
- Rapport des peremptions (produits perimes et echeances a venir)
- Export CSV et impression / PDF des trois rapports

**Pharmacien**
- Consultation et validation/refus des ordonnances clients
- Détection automatique des interactions médicamenteuses lors de la validation
- Blocage automatique de la délivrance d'un médicament périmé
- Enregistrement de nouvelles interactions médicamenteuses
- Historique de ses transactions (expéditions traitées)

**Client**
- Consultation du catalogue des médicaments disponibles (recherche incluse)
- Soumission d'une ordonnance (nouvelle ou renouvellement) avec plusieurs médicaments
- Alerte immédiate si des interactions médicamenteuses sont détectées entre les
  médicaments sélectionnés
- Historique de ses ordonnances et de ses achats

## Structure MVC

```
pharmacie-app/
├── config/          Configuration (BDD, constantes)
├── core/            Database (PDO singleton), Auth, Controller de base, Router (App)
├── models/          Utilisateur, Medicament, Ordonnance, Interaction, Transaction
├── controllers/      Un contrôleur par entité/fonctionnalité
├── views/           Vues organisées par rôle (responsable/pharmacien/client) + layout commun
├── database/         schema.sql (structure + données de démo)
└── public/           Point d'entrée (index.php), assets CSS/JS
```

Toutes les requêtes passent par `public/index.php` (Front Controller) :
`index.php?c=<controleur>&a=<action>`.

## Installation

### Prérequis
- PHP 8.0+ avec l'extension **pdo_mysql** activée
- MySQL ou MariaDB
- Serveur web (Apache/Nginx) ou le serveur intégré de PHP

### Étapes

1. **Créer la base de données** en important le schéma fourni :
   ```bash
   mysql -u root -p < database/schema.sql
   ```
   Cela crée la base `pharmacie_db`, les 7 tables, et insère des données de démonstration.

2. **Configurer la connexion** dans `config/config.php` :
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'pharmacie_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   ```

3. **Lancer le serveur** (pour un test rapide) :
   ```bash
   cd public
   php -S localhost:8000
   ```
   Puis ouvrir `http://localhost:8000/index.php`.

   Ou déployer le dossier `public/` comme racine web sur Apache/XAMPP/WAMP/MAMP
   (le reste de l'application — config, core, models, controllers, views — doit
   rester **en dehors** du dossier accessible publiquement, ou protégé, pour la sécurité ;
   ici tout est référencé par chemin relatif depuis `public/index.php`).

### Comptes de démonstration

Tous les comptes de démonstration utilisent le mot de passe : **`password123`**

| Rôle          | Email                       |
|---------------|------------------------------|
| Responsable   | responsable@pharmacie.tn     |
| Pharmacien    | pharmacien@pharmacie.tn      |
| Client        | client@pharmacie.tn          |

Un client peut aussi créer son propre compte via la page d'inscription.

## Détails techniques

- **Sécurité** : mots de passe hachés avec `password_hash()` (bcrypt), requêtes 100%
  préparées via PDO (`ATTR_EMULATE_PREPARES => false`), échappement systématique en
  sortie (`htmlspecialchars`), contrôle d'accès par rôle (`Auth::requireRole()`),
  vérification de propriété des ressources (un client ne peut voir que ses propres
  ordonnances/transactions), régénération de l'identifiant de session à la connexion
  (anti-fixation de session).
- **Protection CSRF** (`core/Csrf.php`) : un jeton aléatoire est stocké en session et
  inséré dans chaque formulaire ; il est vérifié avec `hash_equals()` au début de
  chaque traitement POST. Les suppressions passent obligatoirement par un formulaire
  POST : une simple URL ne peut plus modifier ni supprimer de données.
- **Routage** : seules les méthodes **publiques** et non magiques des contrôleurs sont
  routables (`ReflectionMethod::isPublic()`), et les paramètres `c` et `a` sont validés
  par expression régulière (pas de traversée de répertoire).
- **Intégrité métier** : une ordonnance déjà traitée ne peut pas être validée ou refusée
  une seconde fois (clause SQL `AND statut = 'en_attente'` + `rowCount()`), et le stock
  est vérifié avant validation puis décrémenté de façon conditionnelle
  (`WHERE stock >= :qte_min`) : il ne peut jamais devenir négatif, même en cas de
  validations simultanées. Si la génération de l'expédition échoue, l'ordonnance est
  automatiquement remise en attente.
- **Jointures** : les listes d'ordonnances, de transactions et d'interactions utilisent
  des `JOIN` SQL (ex : `ordonnances JOIN utilisateurs`, `ordonnance_medicaments JOIN
  medicaments`) plutôt que des requêtes multiples.
- **Transactions SQL** : la création d'une ordonnance (+ ses lignes de médicaments) et
  la génération d'une expédition (+ décrémentation du stock) sont enveloppées dans des
  transactions PDO (`beginTransaction`/`commit`/`rollBack`) pour garantir la cohérence.
- **Validation** : double validation, côté serveur (PHP, dans les contrôleurs) et côté
  client (JavaScript natif, `public/assets/js/validation.js`) — champs obligatoires,
  format email, longueur de mot de passe, nombres positifs, confirmation de mot de passe.
- **Responsive** : feuille de style CSS personnalisée (`public/assets/css/style.css`)
  avec menu latéral escamotable en mobile, grilles adaptatives, tableaux à défilement
  horizontal sur petits écrans.
- **Gestion des péremptions** : la colonne `date_expiration` pilote de vraies règles —
  un produit périmé est retiré du catalogue client et du formulaire de soumission,
  et sa délivrance est bloquée côté serveur au moment de la validation (le contrôle
  résiste à une requête POST forgée). Le responsable dispose d'un rapport dédié
  listant les produits périmés et les échéances à venir sur une fenêtre paramétrable.
- **Rapports exportables** : les rapports de stock critique, de péremptions et d'expéditions
  s'exportent en CSV (`fputcsv`, séparateur `;` et BOM UTF-8 pour Excel) et
  s'impriment via une feuille de style `@media print` — qui permet aussi
  l'enregistrement en PDF depuis le navigateur, sans aucune bibliothèque tierce.

## Pistes d'amélioration possibles

- Upload et stockage du scan de l'ordonnance (nécessite une colonne supplémentaire
  dans la table `ordonnances`)
- Pagination des listes longues (médicaments, ordonnances, utilisateurs)
- Notifications par email lors de la validation/refus d'une ordonnance (nécessite un
  serveur SMTP configuré)
