WP BackUp 0.1.3
=========

WP BackUp contient deux scripts qui permettent de planifier des tâches quotidiennes pour sauvegarder dans des dossiers les fichiers du site et un export de la base de données.

Donation
================

[![Donate to WP BackUp](https://www.paypalobjects.com/en_GB/i/btn/btn_donate_SM.gif "Donate to Donate to WP BackUp")](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=jonathan%2ebuttigieg%40yahoo%2efr&lc=FR&item_name=WP%20BackUp&no_note=0&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHostedGuest)

Installation
================

* Placer les fichiers **backup-website.php** et **backup-bdd.php** à la racine de votre thème.
* Dans le fichier **functions.php** de votre thème, vous devez inclure les deux fichiers comme ci-dessous :
	* `require_once( TEMPLATEPATH . '/backup-bdd.php' );`
	* `require_once( TEMPLATEPATH . '/backup-website.php' );`

Les paramètres
================

* **$backup_file**
	* Nom du fichier de backup.
	* Défaut : 
		* `'db-' . date( 'd-m-Y-G-i-H' ) . '.sql'` (pour la bdd) 
		* `'website-' . date( 'd-m-Y-G-i-H' )` (pour les fichiers du site)
* **$backup_dir**
	* Nom du dossier où sera stocké les backup.
	* Défaut : 
		* `'backup-bdd'` (pour la bdd) 
		* `'backup-website'` (pour les fichiers du site)
* **$htaccess_file**
	* Chemin vers le fichier .htaccess du dossier de backup.
	* Défaut : `$backup_dir . '/.htaccess'`
* **$backup_max_life**
	* Temps maximum de vie d'un fichier de backup - temps en secondes.
	* Défaut : `604800`

Changelog
================

0.1.3 
-----------

* Sécurité : Ajout du chmod 755 sur les dossiers de backup
* Correction d'un bug lors de la suppression des archives

0.1.2 
-----------

* Sécurité : ajout d'un token dans les noms des dossiers qui stockent les archives
* Suppression du fichier .sql pour garder uniquement la version .zip

0.1.1 
-----------

* Sécurité : on empêche l'accès direct aux fichiers

0.1
-----------

* initial commit