<?php

/*
 * Ce fichier permet de créer une planification de backup automatique de la base de données.
 *
 * (c) Jonathan Buttigieg <jonathan.buttigieg@yahoo.fr>
 *
 */

// On crée la planification de notre tâche quotidienne
add_action('wp', 'backup_bdd_scheduled');
function backup_bdd_scheduled() {
	if ( !wp_next_scheduled( 'backup_bdd_daily_event' ) ) {
		wp_schedule_event( time(), 'daily', 'backup_bdd_daily_event' );
	}
}

// On crée la fonction de backup
// Note : le hook doit être égal au nom de votre planification
add_action( 'backup_bdd_daily_event', 'do_backup_bdd' );
function do_backup_bdd( ) {

	global $wpdb;

	$buffer          = ''; 										// Variable de sortie
	$backup_file     = 'db-' . date( 'd-m-Y-G-i-H' ) . '.sql'; 	// nom du fichier de backup
	$backup_dir      = 'backup-bdd'; 							// nom du dossier où sera stocké tous les backup
	$htaccess_file   = $backup_dir . '/.htaccess'; 				// chemin vers le fichier .htaccess du dossier de backup
	$backup_max_life = 604800;									// temps maximum de vie d'un backup - temps en secondes


	/*-----------------------------------------------------------------------------------*/
	/*	Gestion du dossier backup-bdd
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
	/*	On boucle chacune des tables
	/*-----------------------------------------------------------------------------------*/

	foreach ( $wpdb->tables() as $table ) {

		// On recupère la totalité des données de la table
		$table_data = $wpdb->get_results('SELECT * FROM ' . $table, ARRAY_A );

		// On ajoute un commentaire pour délimiter chaque table
		$buffer .= sprintf( "# Dump of table %s \n", $table );
		$buffer .= "# ------------------------------------------------------------ \n\n";

		// On supprime la table si elle existe déjà
		$buffer .= sprintf( "DROP TABLE IF EXISTS %s ;", $table );

		// On ajoute le SQL pour créer la table avec tous les champs
		$show_create_table = $wpdb->get_row( 'SHOW CREATE TABLE ' . $table, ARRAY_A );
		$buffer .= "\n\n" . $show_create_table['Create Table'] . ";\n\n";


		/*-----------------------------------------------------------------------------------*/
		/*	On ajoute chacune des entrées présentes dans la table
		/*-----------------------------------------------------------------------------------*/

		foreach ( $table_data as $row ) {

			$buffer .= 'INSERT INTO ' . $table . ' VALUES(';

			$values = '';
		    foreach ( $row as $key => $value )
			     $values .= '"' . $wpdb->escape( $value ) . '", ';


		    $buffer .= trim( $values, ', ' ) . ");\n";

		} // foreach

		$buffer .= "\n\n";

	} // foreach


	/*-----------------------------------------------------------------------------------*/
	/*	On sauvegarde le fichier
	/*-----------------------------------------------------------------------------------*/

	file_put_contents( $backup_dir . '/' . $backup_file, $buffer );


	/*-----------------------------------------------------------------------------------*/
	/*	On zip le fichier
	/*-----------------------------------------------------------------------------------*/

	if( class_exists( 'ZipArchive' ) ) {

		$zip = new ZipArchive();

		if( $zip->open( $backup_dir . '/' . $backup_file . '.zip', ZipArchive::CREATE ) === true ) {

			// On ajoute le fichier dans l'archive
			$zip->addFile( $backup_dir . '/' . $backup_file );
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