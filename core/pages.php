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
/*          Hook de nettoyage des champs       */
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

add_filter('page_template' , function($template){
	return ( is_filename($template) && file_exists(TEMPLATEPATH.'/'.$template.'.php') ) ? $template : '';
} );

add_filter('excerpt' , function($excerpt){
	return (!empty($excerpt)) ? $excerpt : '';
} );


/***********************************************/
/*          Functions pages 			       */
/***********************************************/

/**
 * Charge une page et parser les données ( mot clé réservé: markdown )
 * @param  $dir_page		$dir_page nom du repertoire de la page type blog ou blog/post ( identique à get_url_queries )
 * @param  $markdown    $markdown: champs actif ou non
 * @return array    	Données contenu dans le fichier
 */
function file_get_page( $dir_page , $markdown = true ) {

	$dir_page  = (string) $dir_page;
	$markdown = (bool) $markdown;
	$page	  = array();

    $page['slug']   = $dir_page;
    $page['url']    = esc_url_raw( get_permalink( $dir_page ) );
    $page['title']  = basename($dir_page) ;

    $fields = array('title','description','keywords','author','date','robots','tags','template','excerpt');
    $fields = apply_filter('page_fields_custom' , $fields );

    $file = encode_utf8( file_get_contents(CONTENT .'/'. $dir_page .'/'. basename($dir_page) .'.txt') );

    foreach( $fields as $field ) {
        if( preg_match('/^[ \t]*' . $field . '[ \t]*:(.*)$/mi', $file , $match ) && $match[1] ) {
            $page[$field] = esc_attr( strip_all_tags( trim( $match[1] ) ) );
            $page[$field] = apply_filter( 'page_'.$field , $page[$field] );
            if ( empty($page[$field]) ) $page[$field] = null;
        }
	   else $page[$field] = null;
    }

    if ( $markdown ) {

        // On cherche le champ markdown si non present on retourn false
        $text = explode( 'markdown:' , $file );
        if( !isset( $text[1]) ) return false;

        // On nettoie le contenu de markdown et si vide on retourne flase
        $content = esc_attr( trim( $text[1]) );
        unset($text);

        $content = pops( $content , CONTENT_URL.'/'.$dir_page );

        # commentaires
        $content = str_replace(array('&#039;&#039;', "``"),
                                array('&#8220;', '&#8221;'), $content);

        // On parse markdown
        $Extra = new Parsedown();
        $content = $Extra->text( $content );

        // On nettoie toutes les urls lie à href
        $clean_all_url = function($array){
			 return 'href="'.esc_url_raw($array[2]).'"';
        };

        $content = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $content );

        // On remet les chevrons pour la balise code
        $content = str_replace( '&amp;', '&' , $content );

        # Traits de séparation
        $content = str_replace(array('---', '--'),
                               array('&#8212;', '&#8211;'), $content);

        # trois petits points et puis lalala
        $content = str_replace('...', '&#8230;', $content);

        $page['content'] = apply_filter( 'after_parse_page' , $content );
        unset($content);
    }

    if ( $markdown && empty($page['excerpt']) && !empty($page['content']) )
        $page['excerpt'] = strip_all_tags(excerpt(strip_tags($page['content'],'<p><a><em><strong>'),55,'words'));

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
            if ( strtolower($field) === 'markdown' ) { $end_text = PHP_EOL .'----' . PHP_EOL . PHP_EOL . 'markdown: ' . PHP_EOL . PHP_EOL . $value; }
            else { $text .= PHP_EOL .'----' . PHP_EOL . PHP_EOL . strtolower($field) . ': ' . $value . PHP_EOL; }
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
