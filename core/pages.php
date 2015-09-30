<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction pages
 *
 *
 * @package cms mini POPS
 * @subpackage pages - parser pages
 * @version 1
 */

/***********************************************/
/*          Functions divers 			       */
/***********************************************/

/**
* Extrait d'une chaine
* @param  $text 	chaine à extraire
* @param  $length 	longueur de l'extrait
* @param  $mode 	mode characère ou mot
*/
function excerpt( $text , $length = 140 , $mode = 'chars' ) {

    $test = (string) $text;
    $mode = (string) $mode;

    if( is_same( strtolower($mode) , 'words' ) ){
        if( str_word_count($text , 0) > $length ) {
          $words = str_word_count($text, 2);
          $pos   = array_keys($words);
          $text  = substr( $text , 0 , $pos[$length]) . '...';
        }
        return $text;
    } else {
        return substr( $text , 0 , $length );
    }
}

/***********************************************/
/* Hook de nettoyage des champs pré_définit    */
/***********************************************/

add_filter('page_title' , function($title){
	return sanitize_allspecialschars($title);
} );

add_filter('page_description' , function($description){
	return excerpt($description);
} );

add_filter('page_robots' , function($robots){
	return ( is_in( str_replace(' ','',$robots) , array('noindex','nofollow','noindex,nofollow','nofollow,noindex')) ) ? $robots : '';
} );

add_filter('page_date' , function($date){
	return (is_date($date)) ? $date : '';
} );

add_filter('page_keywords' , function($keywords){
	return str_replace(' ',', ',sanitize_words($keywords));
} );

add_filter('page_tags' , function($tags){
	return str_replace(' ',', ',sanitize_words($tags));
} );

add_filter('page_excerpt' , function($excerpt){
	return excerpt($excerpt,140,'words');
} );

add_filter('page_template' , function($template){
    return ( is_filename($template) ) ? $template : '';
} );

add_filter('page_markdown' , function( $markdown ){

    # commentaires
    $markdown = str_replace(array('&#039;&#039;', "``"),
                           array('&#8220;', '&#8221;'), $markdown);

    // On parse markdown
    $Extra = new Parsedown();
    $markdown = $Extra->text( $markdown );

    // On nettoie toutes les urls lie à href
    $clean_all_url = function($array){
        return 'href="'.esc_url_raw($array[2]).'"';
    };

    $markdown = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $markdown );

    // On remet les chevrons pour la balise code
    $markdown = str_replace( '&amp;', '&' , $markdown );

    # Traits de séparation
    $markdown = str_replace(array('---', '--'),
                           array('&#8212;', '&#8211;'), $markdown);

    # trois petits points et puis lalala
    $markdown = str_replace('...', '&#8230;', $markdown);

    return $markdown;
} );

add_filter('page_text' , function( $text ){

    // On nettoie toutes les urls lie à href
    $clean_all_url = function($array){
        return 'href="'.esc_url_raw($array[2]).'"';
    };

    $text = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $text );

    return $text;
} );

/***********************************************/
/*          Functions pages 			       */
/***********************************************/

/**
 * Charge une page et parser les données ( cahce de 7 jours )
 * @param  $dir_page	$dir_page nom du repertoire de la page type blog ou blog/post ( identique au résultat de  get_url_queries )
 * @return array    	Données contenu dans le fichier
 */
function file_get_page( $dir_page ) {

	$dir_page  = (string) $dir_page;
	$page	   = array();

    $cache = CONTENT_DIR .'/cache/'. $dir_page .'/'. basename($dir_page) .'.gz';

    // Gestion du cache
    if( file_exists($cache) ) {
        $last_write_cache = filemtime( $cache );
        $last_write_page  = filemtime( CONTENT .'/'. $dir_page .'/'. basename($dir_page) .'.txt' );
        // Si le cache est plus récent que la page on affiche le cache
        if( CACHE && $last_write_cache && ($last_write_cache > $last_write_page) )
            return $page = unserialize( gzdecode(file_get_contents($cache)) );
        else @unlink( $cache );
    }

    // On ouvre le fichier de la page
    $file = encode_utf8( file_get_contents( CONTENT .'/'. $dir_page .'/'. basename($dir_page) .'.txt') );

    // On affecte la valeurs title au cas ou non renseigné
    $page['title']  = basename($dir_page) ;

    // On récupère les champs et leur valeurs du fichier
    preg_match_all('/^[\s]*(\w*?)[ \t]*:[\s]*(.*?)[\s]*[-]{4}/mis', $file , $match );
    // Si la recherche est vide on retourne les valeurs mini
    if( empty($match) ) return $page;
    // On créer le tableau combiné des champs et valeurs
    $file = array_combine($match[1],$match[2]);
    // On purge les variables
    unset($match);
    // On nettoie la table et on applique le fitre correspondant
    foreach( $file as $field => $value ){
        $field = strtolower($field);
        $value = esc_attr(strip_all_tags($value));
        $fields_page_play_pops = apply_filter( 'fields_page_play_pops' , array('markdown','text') );
        if( is_in( $field, $fields_page_play_pops ) )
            $value = pops( $value, $dir_page );
        $page[$field] = apply_filter( 'page_'.strtolower($field) , $value );
    }
    // On purge les variables
    unset($file);

    // On affecte les données importante!
    $page['slug']   = $dir_page;
    $page['url']    = get_permalink( $dir_page );

    // On créer un cache de la page si CACHE activé
    if( CACHE ){
        @mkdir( CONTENT_DIR .'/cache/'.$dir_page.'/', 0755, true );
        file_put_contents( $cache , gzencode( serialize($page) ) , LOCK_EX );
        @chmod( $cache , 0644 );
    }

    return $page ;
}


/**
 * Sauvegarder une page
 * @param  $filename    nom du fichier à sauvegarder tel que get_url_queries : blog/post
 * @param  $array   	Données à sauvegarder sous format de tableau (array)
 * @param  $header  	header du fichier
 * @return boolean
 */
function file_put_page( $filename , $array = array() , $header = 'generate by mini-pops' ) {

    if( is_array( $array ) && !empty( $array ) && is_string( $header ) && is_filename( str_replace('/','',$filename) )  ) {
        $text = '# '. $header . PHP_EOL;
        $end_text = '';
        foreach( $array as $field => $value ){
            $text .= PHP_EOL . strtolower($field) . ': ' . $value . PHP_EOL . PHP_EOL .'----' . PHP_EOL;
        }
        $text .= $end_text;

        $dir = CONTENT .'/'. $filename;
        @mkdir( $dir , 0755 , true );
        if( !is_writable($dir .'/'. basename($filename).'.txt') ) return false;
        if ( !file_put_contents( $dir .'/'. basename($filename).'.txt' , $text , LOCK_EX ) ) return false;
        @chmod( $dir .'/'. basename($filename).'.txt' , 0644 );
        return true;
    }
    return false;
}
