<?php

define( 'ABSPATH', dirname (dirname( __FILE__ ) ) . '/' );


// Gestion des erreurs
$errors = array();

// On vérifie que le serveur apache est présent
$is_apache = (string) strpos( $_SERVER['SERVER_SOFTWARE'], 'Apache' );
if ( $is_apache != '0' ){
    $errors['apache'] = ' est absent. Ce CMS nécessite fonctionne sur un serveur apache.';
}

// On vérifie que le module rewrite d'apache est actif
if ( function_exists('apache_get_modules') ) {
    if ( ! in_array( 'mod_rewrite', apache_get_modules() ) ){
        $errors['mod_rewrite'] = ' n\'est pas présent. Ce CMS nécessite le mod_rewrite.';
    }
}

// On vérifie la version de PHP
$php_version = phpversion();
if ( version_compare( $php_version, "5.3.0", "<" ) ) {
    $errors['php'] = $php_version.' ,ce cms a besoin au minimum de la version 5.3 .';
}

// On vérifie la persmission du repertoire du cms
if ( ! is_writable( ABSPATH ) ) {
    $errors[ ABSPATH ] = ' n\'est pas accessible en écriture, il sera impossible de créer un fichier config !';
}

// On vérifie la persmission du fichier config
$is_config = file_exists ( ABSPATH . 'config.php' );
if ( $is_config && !is_writable( ABSPATH . 'config.php' ) ) {
    $errors['config.php'] = ' n\'est pas accessible en écriture !';
}

// On vérifie la permission du fichier htaccess
$is_htaccess = file_exists ( ABSPATH . '.htaccess' );
if ( $is_htaccess && !is_writable( ABSPATH . '.htaccess' ) ) {
    $errors['htaccess'] = ' n\'est pas accessible en écriture !';
}

// On vérifier les permissions des repertoires à analyser
$dir_array = array('content');
foreach ( $dir_array as $dir ) {
    if ( !is_writable( ABSPATH . $dir.'/' ) ) {
        $errors[$dir] = ' n\'est pas accessible en écriture !';
    }
}



// On applique le contexte du document
header( 'Content-Type: text/html; charset=utf-8' );


if ( empty($errors) ) {

    // On verifie si c'est une nouvelle installation
    if ( $is_config ){

        echo 'déjà installer , donc voir s\'il faut réparer';

        // On vérfie les définitions obligatoire


    } else {

        // On lance la création du fichier config

    }


} else{

}

