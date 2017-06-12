<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction pages
 *
 *
 * @package cms mini POPS
 * @subpackage the_loop - boucle de recherche
 * @version 1
 */


/***********************************************/
/*          Functions LOOP   			       */
/***********************************************/

/**
 * Boucle pages
 * @param  $args    array
 *                  'where'   array() : Listes des slugs de pages où chercher sous forme de tableau si vide recherche dans toutes les pages
 *                  'filter'  string  : Listes des champs recherchés séparer par des virgules ex: title,pubdate
 *                  'max'     integer : Nombre de résultat par défaut : 10
 *                  'order'   string  : Mode de tri "ASC" ( par défaut ), "DESC" ou "SHUFFLE"
 *                  'orderby' string  : Trier par "date" ( par défaut ), "auteur", "tag", tout champs valide dans le document
 *
 * @param  $pattern   string   nom de la boucle ( lié à have_page() )  
 * ex: the_loop('filter[author]=denis,jean,michel&order=asc&orderby=title');
 * @return array    retourne les résultats sous forme de tableau
 */
function the_loop( $args = array(), $pattern = null ){

    $pattern = (string) $pattern;

    $args = parse_args( $args, array(
        'where'   => get_all_page(),
        'max'     => 10,
        'order'   => 'ASC',
        'orderby' => 'pubdate'
        ) );

    /* Nettoyage "max" */
    $max = (int) $args['max'];
    unset($args['max']);

    /* Nettoyage "order" */
    $order = strtoupper($args['order']);
    unset($args['order']);

    /* Nettoyage "orderby" */
    $orderby = is_in( $args['orderby'], array('pubdate','author','tag') ) ? $args['orderby'] : 'pubdate';
    unset($args['orderby']);

    /* Nettoyage "where" */
    $where = array_flip($args['where']);
    unset($args['where']);

    /* Table de data mit de côté*/
    $next   = array();

    /* Préparation du filtre */
    foreach ($args as $filter => $query) {

        $filter = sanitize_key($filter);

        preg_match('/^(is_.*?)\((.*?)\)/', $query, $match ); // On cherche si une requete de recherche

        // Si requête particulière ( requête sur tableau, comparaison, intervalle, etat)
        if( !empty($match[0]) && function_exists($match[1]) ){

            if( is_in( $match[1], array('is_in','is_notin') ) ){
                $args[$filter] = array( '', explode(',', sanitize_list($match[2],',') ) );

            } elseif( is_in( $match[1], array('is_same','is_match','is_different','is_low','is_max','is_size','is_sup') ) ){
                $args[$filter] = array( '', trim($match[2]) );

            } elseif( is_same( $match[1], 'is_between' ) ){
                $args[$filter] = explode(',', sanitize_list($match[2],',') );
                $args[$filter] = array( '', $args[$filter][0], $args[$filter][1] );

            } else {
                $args[$filter] = null;
            }

            add_action( $filter.'_search', $match[1] );  // Ajout du hook pour chaque filtre
        
        } else {

            $query = trim($query);

            if( $query === '!'){
                // Requête qui test si la valeur n'est pas null
                $args[$filter] = array();
                add_action( $filter.'_search', function($value){ return strlen($value) === 0 ? false:true;} );  // Ajout du hook pour chaque filtre;

            } else {
                // Requête simple si vide il sert à detecter que le champs existe
                $args[$filter] = array('', '|\b'.trim($query).'\b|' );
                add_action( $filter.'_search', 'is_match' );  // Ajout du hook pour chaque filtre
            }
        }
    }


    /* Boucle principal de recherche */
    foreach ($where as $page => $key){

        // On boucle sur chaque filtre
        foreach ($args as $filter => $compare) {
        
            /* On ajoute la réference au mask du filtre */
           $compare[0] = get_the_page($filter, $page);
            /* On applique le filtre */
            if( false === do_action($filter.'_search', $compare, true) )
                unset($where[$page]);
        }

        /* on prépare le trie si la table existe toujours */
        if( isset($where[$page]) ){

            /* on commence par décharger la table */
            unset($where[$page]);
            /* On filtre par "orderby" */
            $order_by = get_the_page($orderby, $page);
            if( strlen($order_by) === 0 )    $next[] = $page;
            else                             $where[$page] = $order_by;
        }
    }

  
    /* On filtre par "order" uniquement */
    if( is_same($order, 'ASC' ) ) asort($where);
    if( is_same($order, 'DESC') ) arsort($where);

    /* On supprimer les valeurs qui ont servit au trie puis on ajoute les données mit de côté*/
    $where = array_keys( $where );
    $where = array_merge( $where, $next );

    /* Limite de resultat */
    array_splice( $where, intval($max) );

    /* On envoie les donnée à have_page */
    mp_cache_data('mp_query_'. $pattern, $where);

    /* On informe que la requête est vide */
    if( empty($where) )
        mp_cache_data('mp_query_failed'. $pattern, true);

    return $where;
}



/**
 * have a page est associé directement à the loop
 * @param  $pattern    nom du pattern de la boucle
 *
 * ex: while( have_pages() ): the_page('title'); endwhile;
 * @return bool
 */
function have_pages( $pattern = null ){

    global $query;

    // Action executer une seul fois
    static $one_shot = true;
    if($one_shot){
        mp_cache_data('mp_reset_query', $query); // On sauvegarde le requête principale
        $one_shot = false;
    }

    // On charge la table envoyé par la boucle
    $pages = mp_cache_data('mp_query_'. $pattern);

    // Si la table est vidé on remet la requête principale
    if( empty($pages) ){
        $query = mp_cache_data('mp_reset_query');
        return false;
    }

    // On va à la première ligne de la table
    reset($pages);
    // On récupère la valeur de la table
    $query = current($pages);
    // On supprime la clé de la table
    unset($pages[key($pages)]);
    // On met à jour la table envoyé par la boucle
    mp_cache_data('mp_query_'. $pattern, $pages);

    return true;
}


/**
 * have_not_pages est associé directement à the loop
 * @param  $pattern    nom du pattern de la boucle
 *
 * ex: if( have_not_pages() ): echo 'vous n'avez pas de page à lire' ; endif;
 * @return bool
 */
function have_not_pages( $pattern = null ){

    return mp_cache_data('mp_query_failed'. $pattern);
}