<?php

/*
 * Ce fichier permet de créer une planification de backup automatique des fichiers du site.
 *
 * (c) Jonathan Buttigieg <jonathan.buttigieg@yahoo.fr>
 *
 */

// On crée la planification de notre tâche quotidienne
add_action('wp', 'backup_website_scheduled');
function backup_website_scheduled() {
	if ( !wp_next_scheduled( 'backup_website_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'backup_website_daily_event' );
	}
}

// On crée la fonction de backup
// Note : le hook doit être égal au nom de votre planification
add_action( 'backup_website_daily_event', 'do_backup_website' );
function do_backup_website() {

	$backup_file     = 'website-' . date( 'd-m-Y-G-i-H' ); 			// nom de l'archive de backup
	$backup_dir      = 'backup-website'; 							// nom du dossier où sera stocké tous les backup
	$htaccess_file   = $backup_dir . '/.htaccess'; 					// chemin vers le fichier .htaccess du dossier de backup
	$backup_max_life = 604800;										// temps maximum de vie d'un backup - temps en secondes


	/*-----------------------------------------------------------------------------------*/
	/*	Gestion du dossier backup-website
	/*-----------------------------------------------------------------------------------*/

	// On créé le dossier backup-bdd si il n'existe pas
	if( !is_dir( $backup_dir ) ) mkdir( $backup_dir );


	// On ajoute un fichier .htaccess pour la sécurité
	// On interdit l'accès au dossier à partir du navigateur
	if( !file_exists( $htaccess_file ) ) {

		$htaccess_file_content  = "Order Allow, Deny\n";
		$htaccess_file_content .= "Deny from all";

		file_put_contents( $htaccess_file, $htaccess_file_content );

	} // if


	/*-----------------------------------------------------------------------------------*/
	/*	On zip les fichiers du site
	/*-----------------------------------------------------------------------------------*/

	if( class_exists( 'ZipArchive' ) ) {

		// On crée une class qui permettra de parcourir les dossiers du site
		class ZipRecursif extends ZipArchive {

			public function addDirectory( $dir ) {

				foreach( glob( $dir . '/*' ) as $file ) {

					is_dir( $file ) ? $this->addDirectory( $file ) : $this->addFile( $file );

				} // foreach

			} // function

		} // class


		$zip = new ZipRecursif;

		// On check si on peut se servir de l'archive
		if( $zip->open( $backup_dir . '/' . $backup_file . '.zip' , ZipArchive::CREATE ) === true ) {

			$zip->addDirectory( './' );
			$zip->close();

		} // if

	} // if


	/*-----------------------------------------------------------------------------------*/
	/*	On supprime les backup qui datent de plus d'une semaine
	/*-----------------------------------------------------------------------------------*/

	foreach ( glob( $backup_dir . '/*' ) as $file ) {

		if( time() - filemtime( $file ) > $backup_max_life )
			unlink($file);

	} // foreach

}

?>