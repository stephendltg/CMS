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

    if ( is_array ( $value) ) array_walk ( $value , 'SEARCH'  , $what );

    if ( $key === $what ) {
        if ( is_array ( $value ) ) $value = serialize( $value );
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

    global $QUERY_FIND;

    $field = (string) $field;

    // On purge les données de la variable superglobal $QUERY_FIND
    $QUERY_FIND = array();

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

            if ( !$search ) $search = array();

            if ( count( $search ) > 0 && $value != implode( '' , $search ) ){
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
 * REMOVE ( on supprime un champ et sa valeur associé )
 *
 * @param  array     $query    Données d'une table
 * @param  string    $field    Champs à supprimer
 * @return bool                Retourne true si tout ce passe bien
 */
function REMOVE( &$query , $field ) {

    $field = (string) $field;

    if ( !FIND( $query , $field ) ) return false;
    $query = array_diff_key( $query , array( $field => null  ) );
    return true;
}




/***********************************************/
/*                     mpdb function           */
/***********************************************/

/**
 * mpdb - la fonction d'accès à la base de donnée pour tout commande ou requete
 * @param  array    $query_name      Nom d'une table de données
 * @param  string   $func            Action à effectué
 * @param  string   [$params         = null] Paramètres à passer à l'action
 * @return  Retour d' action lancer
 */
function mpdb( $query_name , $func , $params = null ){

    static $query = array();
    static $query_statistic = array();

    $query_name = (string) $query_name;
    $func       = (string) $func;


    // Fonctions private

    /**
     * Prépare une table de base de donnée
     * @param  string $query_name  Nom de la table a préparer
     * @return boolean  True si table est prête sinon true
     */
    $prepare = function( $query_name , $params = ''  ) use ( &$query , &$query_statistic , &$query_access ){

        $file = glob(DATABASE_DIR . '/' . $query_name . '.table.json');
        if( empty( $file ) ) return cms_maintenance( 'Error file read permissions : database !' );

        // On prepare une table temporaire
        $query_prepare = json_decode( file_get_contents ( $file[0] ), true );
        unset($file);

        // On ajoute la clé de sécurité ainsi que l'autoincremente
        $query[$query_name] = array_merge ( $query_prepare , array ( SECRET_KEY => array( base64_encode( DATABASE_DIR . '/' . $query_name . '.table.json' ) , 0 ) ) );

        INSERT ( $query_statistic , array ( 'QUERY' => array('PREPARE' => $query_name ) ) );

        // Gestion de'accès de la base seulement si appel via mpdb('nombase' , 'PREPARE')
        if( $params === 'WRITE_PROTECTED' ) $query[$query_name]['WRITE_PROTECTED'] = true;
        else $query[$query_name]['WRITE_PROTECTED'] = false;

        return true;
    };


    /**
     * On execute les requetes sur la table seulement si elle a été modifié ( on limite l'écriture sur le serveur )
     * @param  array $query Tableau de la base de données preparée
     * @return boolean  si execution true sinon false
     */
    $execute = function( $query_table ) use ( &$query_statistic ){

        if ( count( FIND( $query_table , SECRET_KEY ) ) > 0 ) {

            $filename = base64_decode( $query_table[ SECRET_KEY ][0] );

            if ( $query_table[SECRET_KEY][1] > 0 ) {

                $query_table = array_diff_key( $query_table , array ( SECRET_KEY => null) );

                // On insert les données de statistique seulement si écriture
                INSERT ( $query_statistic , array ( 'QUERY' => array('EXECUTE' => $filename ) ) );

                if ( !file_put_contents( $filename , json_encode( esc_attr( $query_table ) ) , LOCK_EX ) )
                    return cms_maintenance('Error file write permissions : database !');
                @chmod( $file , 0644 );
            }

        } else { return false;}

        return true;

    };


    /**
     * Créer une table
     * ex: mpdb ('post' , 'CREATE_TABLE' , array ('titre'=>'test')); || mpdb ('post' , 'CREATE');
     * @param  string $query_name Nom de la table
     * @param  array  $params     Contenu de la table sous forme de tableau si présent
     * @return boolean  true si créer sinon false
     */
    $create = function( $query_name , $params ){

        $file = glob( DATABASE_DIR . '/' . $query_name . '.table.json' );

        if ( is_filename( $query_name ) && empty( $file ) ){

            // On passe le parametre uniquement si c'est un tableau
            if( !is_array( $params ) ) $params = array();

            // Creation de la nouvelle table
            if ( !file_put_contents( DATABASE_DIR . '/' . $query_name . '.table.json' , json_encode( esc_attr( $params ) ) , LOCK_EX ) )
                return cms_maintenance('Error write permissions : create database !');
            @chmod( $file , 0644 );
            unset($file);
            return true;
        }

        return false;

    };



    // Launch Action
    switch ($func) {

        case 'FIND';
        case 'INSERT';
        case 'UPDATE';
        case 'REMOVE';
            if ( array_key_exists( $query_name , $query ) ){

                if( $query[$query_name]['WRITE_PROTECTED'] === true && $func !== 'FIND' ) return false;

                // On recupere le resultat de l'action executée
                $result = $func( $query[$query_name] , $params );

                // On incremente l'autoincrement sur requete d'écriture
                if ( $result == true && $func !== 'FIND' ) $query[$query_name][SECRET_KEY][1]++;

                // On insere les données de statistiques
                INSERT ( $query_statistic , array ( $query_name => array( $func => json_encode($params) ) ) );

                // On retourne le resultat de l'action
                return $result;

            } else {

                if ( ! $prepare( $query_name ) ) return false;

                // On recupere le resultat de l'actions executée
                $result = $func( $query[$query_name] , $params );

                // On incremente l'autoincrement sur requete d'écriture
                if ( $result == true && $func !== 'FIND' ) $query[$query_name][SECRET_KEY][1]++;

                // On insere les données de statistiques
                INSERT ( $query_statistic , array ( $query_name => array( $func => json_encode($params) ) ) );

                // On execute les requetes sur la table
                $execute( $query[$query_name] );

                // On supprime les données temporaires
                unset( $query[$query_name] );

                // On retourne le resultat de l'action
                return $result;
            }

        break;

        case 'PREPARE';
            // On vérifie que la table temporaire n'existe pas déjà
            if ( array_key_exists( $query_name , $query ) ) return false;
            return $prepare( $query_name , $params );
        break;

        case 'DROP';
            return delete( DATABASE_DIR . '/' . $query_name . '.table.json' );
        break;

        case 'EXECUTE';
            // On vérifie que la table temporaire existe
            if( !array_key_exists( $query_name , $query ) ) return false;
            // On bloque une autre execution de sauvegarde au cas ou on a declenche une table en mode preserve read
            if( $query[$query_name]['WRITE_PROTECTED'] === true ) return false;
            $result = $execute( $query[$query_name] );
            // On preserve les données temporaire en vie si paramètre PRESERVE_READ
            if( $params === 'PRESERVE_READ' ){
                $query[$query_name]['WRITE_PROTECTED'] = true;
                return $result;
            }
            // On supprimer le données temporaires
            unset( $query[$query_name] );
            return $result;
        break;

        case 'CREATE_TABLE';
            return $create( $query_name , $params);
        break;

        case 'INFO';
            // if $query_name = '*' , on retourne info de toutes les tables
            return glob( DATABASE_DIR .'/'. $query_name .'.table.json'  );
        break;

        case 'STATISTIC';
            if ( $query_name === '*' ) return $query_statistic;
            return FIND( $query_statistic , $query_name );
        break;

        default:
            return false;
    }
}
