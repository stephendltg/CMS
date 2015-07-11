<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction base de donnée json.
 *
 *
 * @package cms mini POPS
 * @subpackage mpdb
 * @version 1
 */


/***********************************************/
/*                     JSON-functions          */
/***********************************************/

/**
 * Préparer donnée pour stockage json. Enlèves les caractères dangereux.
 *
 * @param  string    $str chaine de caractères
 * @param  boolean   $flag htmlspecialchars
 * @return string    Chaine de caractère nettoyée
 */
function esc_json( $str , $flag = true ) {

    // On redefini les variables
    if ( is_integer($str) ) return $str; else $str = (string) $str;
    $flag = (bool) $flag;

    // On supprime les caractères invisibles
    $non_displayables = array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08]/', '/\x0b/', '/\x0c/', '/[\x0e-\x1f]/');
    do {
        $cleaned = $str;
        $str = preg_replace( $non_displayables , '' , $str );
    } while ( $cleaned != $str );

    // htmlspecialchars
    if ($flag){ $str = htmlspecialchars( $str , ENT_QUOTES , CHARSET ); }

    return $str;
}


/**
 * esc_json_array_recursive ( callback function INSERT() et UPDATE() )
 *
 * @param  string   $value  Valeur de la table passée en paramètre
 * @param           $key    Clé de la table passée en paramètre
 */
function esc_json_array_recursive( $value , $key ){

    if ( is_array ( $value) ){
        $key = esc_json( $key );
        array_walk ( $value , 'esc_json_array_recursive' );
    } else {
        $key = esc_json( $key );
        $value = esc_json( $value );
    }
}


/**
 * Chargé un fichier JSON
 *
 * @param  string    $filename nom du fichier à charger - chemin absolu
 * @param  boolean   $force Si forcer on ne test pas l'existence du fichier
 * @return array     Donnée contenu dans le fichier
 */
function get_json( $filename , $force = false ) {

    // On redefini les variables
    $filename  = (string) $filename;
    $force = (bool)   $force;

    if ( $force ) {
        return json_decode( file_get_contents ( $filename ), true );
    } else {
        if ( file_exists( $filename ) && is_file( $filename ) ) {
            return json_decode( file_get_contents ( $filename ), true );
        } else {
            return false;
        }
    }
}


/**
 * sauvegarder un fichier JSON
 *
 * @param  string    $filename nom du fichier à charger - chemin absolu
 * @param  $data     Données à sauvegarder sous format de tableau (array)
 * @return boolean
 */
function set_json( $filename , $data ) {

    // On redefini les variables
    $filename  = (string) $filename;

    if ( !file_put_contents( $filename , json_encode( $data ) , LOCK_EX ) ) {
        return false;
    }

    @chmod( $filename , 0644 );

    return true;
}


/***********************************************/
/*              DIR & FILE fonction            */
/***********************************************/

/**
 * Création d'un dossier
 *
 * @param  string    $dirname Nom du dossier
 * @param  int       $chmod Mode d'accès du
 * @return boolean
 */
function CREATE_DIR( $dirname , $chmod = 0755 ) {

    global $is_apache;

    // On redefinit la variable
    $dirname = (string) $dirname;

    if ( is_dir( ABSPATH . $dirname ) || !$is_apache ) return false;

    $htaccess_file_content  = "Options - Indexes\n";
    $htaccess_file_content .= "<Files *.php> Deny from all </Files>";

    mkdir( ABSPATH . $dirname , $chmod );
    file_put_contents( ABSPATH . $dirname . '/.htaccess', $htaccess_file_content );

    return true;
}


/**
* Supprimer un fichier
*
*
* @param  string    $filename     Nom du fichier - chemin absolu
* @return boolean
*/
function ERASE( $filename ) {

    // On redefinit les variables
    $filename   = (string) $filename;

    if ( file_exists( $filename ) && ! is_dir( $filename ) )
        return unlink( $filename );
    return false;
}


/**
* Liste des fichiers dans un dossier par masque
*
*
* @param  string    $dir        chemin absolu du dossier
* @param  string    $mask       masque de filtrage ( ex: php, '*' pour tout type de fichier )
* @param  string    $file       Nom du fichier ( pas chemin absolu ex: 'options')
* @return array
*/
function INFO( $dir , $mask , $file = '*' ) {

    // On redefini les variables
    $dir      = (string) $dir;
    $mask     = (string) $mask;
    $filename = (string) $file;

    $tmp = array();

    foreach( glob( "$dir/$filename.$mask" ) as $filename ) {
        INSERT( $tmp , array( basename( $filename ) => array ( 'file_size' => filesize( $filename ) ) ) );
        INSERT( $tmp , array( basename( $filename ) => array ( 'filemtime' => filemtime( $filename ) ) ) );
        INSERT( $tmp , array( basename( $filename ) => array ( 'fileatime' => fileatime( $filename ) ) ) );
    }
    return $tmp;
}


/***********************************************/
/*                     CRUD ARRAY              */
/***********************************************/


/**
 * SEARCH ( callback function FIND() )
 *
 * @param           $value  Valeur de la table passée en paramètre
 * @param  string   $key    Clé de la table passée en paramètre
 * @param  string   $what   le "quoi" : on recherche quoi
 */
function SEARCH( $value , $key , $what ){

    global $QUERY_FIND;

    if ( is_array ( $value) ){
        array_walk ( $value , 'SEARCH' , $what );
    }
    if ( $key === $what ) {
        if ( is_array ( $value ) ) {
            $value = serialize( $value );
        }
        array_push ( $QUERY_FIND , $value );
    }
}

/**
 * FIND
 *
 * @param  array     $query   Données d'une table
 * @param  string    $field   string ou array : si array la recherche est de type resultat = seulement si champ1 & champ2 sont dans la même collection
 * @return array              Retourne un tableau contenant le ou les résultats même si vide
 */
function FIND( &$query , $field ) {

    // Appel variable super globale
    global $QUERY_FIND;

    // On purge les données de la variable superglobal $QUERY_FIND
    $QUERY_FIND = array();

    // On redefinit les variables
    $field = (string) $field;

    array_walk( $query , 'SEARCH' , $field );

    if ( count( $QUERY_FIND ) > 0 ) return $QUERY_FIND;

    return false;
}


/**
 * INSERT ( on insere une donnée s'il elle existe alors on ajoute la donnée à la suite )
 *
 * @param  array    $query   Données d'une table
 * @param  array    $field   Tableau de donnée à mettre à jour
 * @return array             Retourne la table mise à jour
 */
function INSERT( &$query , array $fields = null ) {

    if ( count( $fields ) > 0 ) {
        array_walk ( $fields , 'esc_json_array_recursive' );
        $query = array_merge_recursive ( $query , $fields );
        return true;
    }
    return false;
}


/**
 * UPDATE ( on met à jour une donnée )
 *
 * @param  array    $query   table de la base: peut etre de type $table['nom de la collection']
 * @param  array    $field   Tableau de donnée à mettre à jour
* @return  array             Retourne la table mise à jour
 */
function UPDATE( &$query , array $fields = null ) {

    $query_trigger = false;

    if ( count( $fields ) > 0 ) {

        foreach( $fields as $field => $value ){

            $search = FIND( $query , $field );
            if ( count( $search ) > 0 && $value != implode( '' , $search ) ){
                array_walk( $fields , 'esc_json_array_recursive' );
                $query = array_merge( $query , array( $field => $value ) );
                $query_trigger = true;
            }
        }
        if ($query_trigger) return true;
        return false;
    }
    return false;

}


/**
 * DELETE ( on supprime un champ et sa valeur associé )
 *
 * @param  array     $query    Données d'une table
 * @param  string    $field    Champs à supprimer
 * @return bool                Retourne true si tout ce passe bien
 */
function DELETE( &$query , $field ) {

    // On redefinit les variables
    $field = (string) $field;

    if ( count( FIND( $query , $field ) ) > 0 ){
        $query = array_diff_key( $query , array( $field => null  ) );
        return true;
    }
    return false;
}




/***********************************************/
/*                     mpdb function           */
/***********************************************/


/**
 * mpdb - la fonction d'accès à la base de donnée pour tout commande ou requete
 *
 * @param  array     $query_name    Nom d'une table de données
 * @param  string    $func          Actions à effectué
 * @param  string    $params        Paramères à passer au actions si disponible
 * @return                          Selon fonction passer en parametres
 */
function mpdb( $query_name , $func , $params = null ){

    static $query = array();
    static $query_statistic = array();

    // On redefini les variables
    $query_name = (string) $query_name;
    $func = (string) $func;

    // Fonctions private prepare
    $prepare = function( $query_name ) use ( &$query ){

        // On prepare une table temporaire
        $query_prepare = get_json( ABSPATH . JSONDB . '/' . $query_name . '.table.json' );

        // Si erreur dans le chargement de la table on retourne false
        if ( $query_prepare === false ) return false;

        // On ajoute la clé de sécurité ainsi que l'autoincremente
        $query[$query_name] = array_merge ( $query_prepare , array ( SECRET_KEY => array( base64_encode( ABSPATH . JSONDB . '/' . $query_name . '.table.json' ) , 0 ) ) );

        return true;

    };

    // Fonctions private execute
    // On execute les requetes sur la table seulement si elle a été modifié ( on limite l'écriture sur le serveur )
    $execute = function( $query ){

        if ( count( FIND( $query , SECRET_KEY ) ) > 0 ) {

            $filename = base64_decode( $query[ SECRET_KEY ][0] );

            if ( $query[SECRET_KEY][1] > 0 ) {
                $query = array_diff_key( $query , array ( SECRET_KEY => null) );
                if ( !set_json( $filename , $query ) ) { return false; }
            }

        } else { return false;}

        return true;

    };

    // Fonctions private create : créer une table
    // mpdb ('post' , 'CREATE' , array ('titre'=>'test')); || mpdb ('post' , 'CREATE');
    $create = function( $query_name , $params ){

        if ( ! file_exists( ABSPATH . JSONDB . '/' . $query_name . '.table.json' ) &&
            is_dir( dirname( ABSPATH . JSONDB ) ) &&
            is_writable( dirname( ABSPATH . JSONDB ) )
        ){
            // On nettoie la table des caractères dangereux sinon on créer une table vide
            ( is_array($params) ) ? array_walk ( $params , 'esc_json_array_recursive' ) : $params = array();

            // Creation de la nouvelle table
            return set_json( ABSPATH . JSONDB . '/' . $query_name . '.table.json' , $params );
        }
        return false;

    };

    switch ($func) {

        case 'FIND';
        case 'INSERT';
        case 'UPDATE';
        case 'DELETE';
            if ( array_key_exists( $query_name , $query ) ){

                // On recupere le resultat de l'action executée
                $result = $func( $query[$query_name] , $params );

                // On incremente l'autoincrement sur requete d'écriture
                if ($result == true ) {
                    $query[$query_name][SECRET_KEY][1]++;
                    INSERT ( $query_statistic , array ( $query_name => array( $func => json_encode($params) ) ) );
                }

                // On retourne le resultat de l'action
                return $result;

            } else {

                if ( ! $prepare( $query_name ) ) return false;

                // On recupere le resultat de l'actions executée
                $result = $func( $query[$query_name] , $params );

                // On incremente l'autoincrement sur requete d'écriture
                if ($result == true ) {
                    $query[$query_name][SECRET_KEY][1]++;
                    INSERT ( $query_statistic , array ( $query_name => array( $func => json_encode($params) ) ) );
                }

                // On execute les requetes sur la table
                $execute( $query[$query_name] );
                unset($query[$query_name]);

                // On retourne le resultat de l'action
                return $result;
            }

        break;

        case 'PREPARE';
            if ( array_key_exists( $query_name , $query ) ) return false;
            return $prepare( $query_name );
        break;

        case 'ERASE';
            return $func( ABSPATH . JSONDB . '/' . $query_name . '.table.json' );
        break;

        case 'EXECUTE';
            if ( ! array_key_exists( $query_name , $query ) ) return false;
            $result = $execute( $query[$query_name] );
            unset($query[$query_name]);
            return $result;

        break;

        case 'CREATE';
            return $create( $query_name , $params);
        break;


        case 'INFO';
            // if $query_name = '*' , on retourne info de toutes les tables
            return $func( ABSPATH.JSONDB , 'json' , $query_name.'.table'  );
        break;

        case 'STATISTIC';
            if ( $query_name === '*' )
                return $query_statistic;
            return $query_statistic[$query_name];
        break;

        default:
            return false;
    }
}
