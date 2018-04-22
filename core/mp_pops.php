<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage shortcode
 * @version 1
 */



/***********************************************/
/*   Recherche shortcode dans contenu          */
/***********************************************/


/**
 * On recherche les shortcodes dans le contenu
 * @param  $content   Contentu ou recherché les pops
 * @param  $slug      nom du repertoire de la page type blog ou blog/post ( identique au résultat de  get_url_queries )
 * @return strin      Retourne le contenu
 */
function mp_pops( $content , $slug = '' ){

    $content   = (string) $content;
    $slug      = (string) $slug;

    // Liste shortcode
    $pops = array('link','email','tel','image','gallery','file','twitter','youtube','audio', 'map');
    //$pops = apply_filters('pops_shortcode' , $pops );
    $pops = array_merge( apply_filters( 'pops_shortcode', array() ), $pops );

    // On boucle sur la recherche des shortcode et on affecte à la fonction associé si elle existe en lui passant les paramètres du shortcode
    foreach ( $pops as $name ) {

        // Callback du shortcode trouvé ( fonction anonyme )
        $shortcode_replace_callback = function( $array ) use ( $slug , $name ) {

            // On nettoie shortcode trouvé
            $pops_params = trim(rtrim(ltrim($array[0] , '(') , ')'));

            // On récupère les paramètres du shortcode
            $pops_params = explode( '|' , $pops_params );

            // On créer les paramètres indispensable à passer au shortcode
            $params['slug'] = $slug;

            // On construit la table des paramètres du shortcode
            foreach( $pops_params as $pops_param ){

                // On récupère le nom du paramètre
                $pops_param_name = strtolower( trim( substr( $pops_param , 0 , strpos($pops_param,':') ) ) );

                // On récupère la valeur du paramètre
                $pops_param_value = trim( substr( $pops_param , strpos($pops_param,':')+1 , size($pops_param) ) );

                // On associe nom et valeur sur la table de paramètre
                $params[$pops_param_name] = $pops_param_value;
            }

            // On lance le shortcode
            $func = "pops_$name";
            return $func( $params );

        };

        // Recherche des shortcodes si et seulement si la fonction du shortcode existe
        if( function_exists("pops_$name") )
            $content = preg_replace_callback( '/\([ \t]*'. $name .'[ \t]*:(.*?)\)/i' , $shortcode_replace_callback  , $content );

    }

    return $content;
}







/**
 * On parse les arguments des pops
 * @param  array      arguments
 * @param  array      params par défaut
 * @return array      arguments
 */
function pops_parse_args( $args = array() , $params = array() ){

  $args = parse_args( $args, parse_args($params) );

  if( !empty( $args['text'] ) )
    $args['text'] = sanitize_allspecialschars($args['text']);
  else
    $args['text'] = '';

  if( !empty($args['class']) )
    $args['class'] = ' class="'.sanitize_html_class($args['class']).'"';
  
  if ( !empty( $args['slug'] ) )
    $args['slug']  = trim( $args['slug'] , '/' );
  else
    $args['slug']  = isset($GLOBALS['query']) ? $GLOBALS['query'] : '';

  if ( !empty( $args['limit'] ) )
    $args['limit'] = intval($args['limit']);
  else
    $args['limit'] = 10;

  if ( !empty( $args['rel'] ) && is_in ($args['rel'] , array('me','nofollow') ) )
    $args['rel'] = ' rel="me"';
  else
    $args['rel'] = '';

  return $args;
}







/**
* Adds a new shortcode.
*
* @param string   $tag      Shortcode tag to be searched in post content.
* @param callable $callback The callback function to run when the shortcode is found.
*                           Every shortcode callback is passed three parameters by default,
*                           including an array of attributes (`$atts`), the shortcode content
*                           or null if not set (`$content`), and finally the shortcode tag
*                           itself (`$shortcode_tag`), in that order.
*/
function add_shortcode( $tag, $callback ) {

    $shortcode_tags = mp_cache_data('shortcode_tags');
  
    if ( '' == trim( $tag ) ) {
        $message = __( 'Invalid shortcode name: Empty name given.' );
        _doing_it_wrong( __FUNCTION__, $message );
        return;
    }
    
    if ( 0 !== preg_match( '@[<>&/\[\]\x00-\x20=]@', $tag ) ) {
        /* translators: 1: shortcode name, 2: space separated list of reserved characters */
        $message = sprintf( __( 'Invalid shortcode name: %1$s. Do not use spaces or reserved characters: %2$s' ), $tag, '& / < > [ ] =' );
        _doing_it_wrong( __FUNCTION__, $message );
        return;
    }

    $shortcode_tags[ $tag ] = $callback;

    mp_cache_data('shortcode_tags', $shortcode_tags );

  }






/**
 * Removes hook for shortcode.
 */
function remove_shortcode($tag){

    $shortcode_tags = mp_cache_data('shortcode_tags');

    unset($shortcode_tags[$tags]);

    mp_cache_data('shortcode_tags', $shortcode_tags );
}





/**
 * Removes all shortcode.
 */
function remove_all_shortcode(){

    mp_cache_data('shortcode_tags', null );
}






/**
* On cherche les shortcode dans du contenu
*
* @param string $content Contenu ou chercher les shortcodes.
* @return string Contenu avec le resultat du filtrage des shortcodes.
*/
function do_shortcode( $content ) {

    $shortcode_tags = mp_cache_data('shortcode_tags');
 
    if ( false === strpos( $content, '(' ) ) {
        return $content;
    }
    
    if (empty($shortcode_tags) || !is_array($shortcode_tags))
        return $content;
 

    foreach ( $shortcode_tags as $tag => $callback ) {

        $pattern = '/\([ \t]*'. $tag .'[ \t]*:(.*?)\)/i';

        $content = preg_replace_callback( $pattern , 'do_shortcode_tag' , $content );
    }
 
    // On rajoute les parenthèses si elles sont utilisé en retour d'un shortcode
    $content = strtr( $content, array( '&#40;' => '(', '&#41;' => ')' ) );
 
    return $content;
}





/**
* On construit le shortcode d'un tag avant de l'envoyer à l'execution et on récupère sa sortie
*
* @param array $m Regular expression match array
* @return string|false False on failure
*/
function do_shortcode_tag( $array ){

    $shortcode_tags = mp_cache_data('shortcode_tags');

    // On nettoie shortcode trouvé
    $attrs = trim( rtrim( ltrim( $array[0] , '(' ) , ')' ) );

    // On récupère les paramètres du shortcode
    $attrs = explode( '|' , $attrs );

    // On construit la table des paramètres du shortcode
    foreach( $attrs as $key => $attr ){

        // On récupère le nom du paramètre
        $attr_name = strtolower( trim( substr( $attr , 0 , strpos($attr,':') ) ) );

        // On récupère la valeur du paramètre
        $attr_value = trim( substr( $attr , strpos($attr,':')+1 , size($attr) ) );

        // On supprime le clé une fois la lecture effectué
        unset($attrs[$key]);

        // On associe nom et valeur sur la table de paramètre
        $attrs[$attr_name] = $attr_value;
    }

    // tag
    $tag = key($attrs);
 
    if ( ! is_callable( $shortcode_tags[ $tag ] ) ) {
        /* translators: %s: shortcode tag */
        $message = sprintf( __( 'Attempting to parse a shortcode without a valid callback: %s' ), $tag );
        _doing_it_wrong( __FUNCTION__, $message );
        return $array[0];
    }

    $output = call_user_func( $shortcode_tags[ $tag ], $attrs, $tag );

    return apply_filters( 'do_shortcode_tag', $output, $tag, $attr );
}






/**
* Combine user attributes with known attributes and fill in defaults when needed.
*
* @param array  $pairs     valeures par défaut
* @param array  $atts      paramètres passer par do_shortcode_tag
* @param string $shortcode Option
* @return array Combine et filtre les parametres.
*
*/
function shortcode_atts( $pairs, $attrs, $shortcode = '' ) {

    $attrs = parse_args( $attrs );
    $pairs = parse_args( $pairs );
    
    $out = array();

    foreach ($pairs as $name => $default) {

        if ( array_key_exists($name, $attrs) )
            $out[$name] = $attrs[$name];
        else
            $out[$name] = $default;
    }

    if ( $shortcode ) {
        $out = apply_filters( "shortcode_atts_{$shortcode}", $out, $pairs, $attrs, $shortcode );
    }

    if( !empty( $args['text'] ) )
    $args['text'] = sanitize_allspecialschars($args['text']);
  else
    $args['text'] = '';

  if( !empty($args['class']) )
    $args['class'] = ' class="'.sanitize_html_class($args['class']).'"';
  
  if ( !empty( $args['slug'] ) )
    $args['slug']  = trim( $args['slug'] , '/' );
  else
    $args['slug']  = isset($GLOBALS['query']) ? $GLOBALS['query'] : '';

  if ( !empty( $args['limit'] ) )
    $args['limit'] = intval($args['limit']);
  else
    $args['limit'] = 10;

  if ( !empty( $args['rel'] ) && is_in ($args['rel'] , array('me','nofollow') ) )
    $args['rel'] = ' rel="me"';
  else
    $args['rel'] = '';
 
    return $out;
}





/***********************************************/
/*        DECLARATION  DES SHORTCODES          */
/***********************************************/

// twitter
add_shortcode('twitter', 'twitter');

// Instagram
add_shortcode('instagram', 'instagram');



/***********************************************/
/*              LIST SHORTCODE                 */
/***********************************************/


/**
 * Shortcode Twitter
 *
 * mp_pops( '( twitter :  peusdo twitter |  text: texte | class : classe css | rel : me )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function twitter( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('twitter&class&text&rel',$param) );

    // Sanitize 
    $text  = sanitize_allspecialschars($text);
    $class = ' class="'.sanitize_html_class($class).'"';
    $rel   = is_in($rel, array('me','nofollow') ) ? $rel : '';     

    // On valide le pseudo twitter
    if( !is_match($twitter, '/@([A-Za-z0-9_]{1,15})/') ) return;

    $twitter  = str_replace( '@' , '' ,  $twitter );
    $text     = strlen($text) == 0 ? $twitter : $text;

    // Scheme du shortcode
    $schema = apply_filters('shortcode_twitter_schema', '<a href="https://twitter.com/%1$s"%3$s%4$s>%2$s</a>');

    return sprintf( $schema, $twitter, $text, $class, $rel );
}









/**
 * Shortcode Audio
 *
 * mp_pops( '( audio :  *.mp3[, *.ogg] |  text : description | class : classe css )', $slug );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_audio( $args ){

    $args = pops_parse_args( $args, 'class=my_audio');

    // On prépare le fichier audio pour la recherche
    $audio  = sanitize_file_name($args['audio']);
    $medias = basename($audio, '.mp3');

    if($medias === $audio)
        $medias = basename($audio, '.ogg');

    // On creuse dans la recherche
    $medias = get_attached_media(array('name'=>$medias,'where'=>$args['slug'],'type'=>'mp3,ogg'), 'uri');

    if( empty($medias) ) return;

    // Url de secours pour vieux naviguateur 
    $download = $medias[0];

    if( count($medias) == 2 )
        $medias = '<source src="'. $medias[0] .'" type="audio/mp3"><source src="'. $medias[1] .'" type="audio/ogg">';
    else
        $medias = '<source src="'. $medias[0] .'" type="audio/'.substr(strrchr($medias[0],'.'), 1).'">';

    // On associe la description
    $text  = strlen($args['text']) == 0 ? '' : sprintf( "<figcaption>$s</figcaption>" , $args['text'] );

    // Scheme du shortcode
    $schema = apply_filters('pops_audio_schema' ,'<figure%s><audio controls="controls">%s<a href=%s download=%s>$mp3</a></audio>%s</figure>');

    return sprintf( $schema, $args['class'], $medias, $download, basename($download), $text );
}


/**
 * Shortcode Email
 *
 * mp_pops( '( email :  s.deletang@yahoo.com |  text : texte | class : classe css | rel : me )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_email( $args ){

    $args = pops_parse_args( $args, 'class=my_email');

    // On verifie de l'email est valid
    if( !is_email( $args['email'] ) ) return;

    // On associe le texte, class et rel
    $text   = strlen($args['text']) == 0 ? '@'. sanitize_words(substr( $email , 0 , strpos($email,'@') ) ) : $args['text'];
    $args['email']  = str_replace('@', '[at]', $args['email']);

    // Scheme du shortcode
    $schema_with_rel = apply_filters('pops_email_schema_with_rel','<address%3$s><a href="mailto:?to=%1$s"%4$s>%2$s</a></address>');
    $schema_no_rel   = apply_filters('pops_email_schema_no_rel','<a href="mailto:%1$s"%3$s%4$s>%2$s</a>');
    $schema          = !empty($args['rel']) ? $schema_with_rel : $schema_no_rel;

    return sprintf( $schema, $args['email'], $text, $args['class'], $args['rel'] );
}


/**
 * Shortcode File
 *
 * mp_pops( '( file :  nom du fichier |  text : texte | class : classe css )', $slug );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_file( $args ){

    $args = pops_parse_args( $args, 'class=my_file');

    // On cherche ...
    $file = get_attached_media(array('file'=>$args['file'],'slug'=>$args['slug'],'type'=>'pdf,zip,ppt,pps,xls,doc,docx,txt'), 'uri');

    if( empty($file) ) return;

    // On associe le texte, class et link_file
    $text  = strlen($args['text']) == 0 ? $args['file'] : $args['text'];

    // Scheme du shortcode
    $schema   = apply_filters('pops_file_schema', '<a href=%2$s download=%1$s%3$s>%4$s</a>');

    return sprintf( $schema, $args['file'], $file[0], $args['class'], $text );
}


/**
 * Shortcode Image
 *
 * mp_pops( '( image :  nom du fichier |  alt: texte |text : texte | class : classe css | ratio: 1OO )', $slug );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_image( $args ){


    $args = pops_parse_args( $args, 'class=my_image&size=large');

    // On récupère l'image
    $url = get_the_image( array('file'=>$args['image'], 'size'=>$args['size'] , 'slug'=>$args['slug']), 'uri' );

    // On verifie si l'image existe
    if( !$url ) return;

    // On associe le texte, class
    $text  = strlen($args['text']) == 0 ? '' : sprintf("<figcaption>$s</figcaption>" , $args['text'] );

    // Scheme du shortcode
    $schema   = apply_filters('pops_image_schema', '<figure%s><img class="img" src="%s"/>%s</figure>', $args['image'], $args['slug'] );

    return sprintf( $schema, $args['class'], $url, $text );
}



/**
 * Shortcode Gallery
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_gallery( $args ){

    $args = pops_parse_args( $args, 'class=my_gallery');

    // On récupère l'image en mode large
    $images = get_the_image( array('size'=>'large','file'=>$args['gallery'],'slug'=>$args['slug'],'max'=>'auto'), 'uri' );

    // On verifie si l'image existe
    if( empty($images) ) return;

    // On cherche les différents formats
    $small  = get_the_image( array('size'=>'small','file'=>$args['gallery'],'slug'=>$args['slug'], 'max'=>'auto'), 'uri' );
    $medium = get_the_image( array('size'=>'medium','file'=>$args['gallery'],'slug'=>$args['slug'], 'max'=>'auto'), 'uri' );


    // On associe le texte, class
    $text  = strlen($args['text']) == 0 ? '' : sprintf( "<figcaption>$s</figcaption>" , $args['text'] );

    // Scheme du shortcode
    $scheme = '<img class="item-%s" srcset="%s 1024w, %s 640w, %s 320w" sizes="(min-width: 36em) 33.3vw, 100vw" src="%s">';

    // fluid+gouttière pour gallery
    // 'sizes="(min-width: 36em) calc(.333 * (100vw-[$gutter]em) ), 100vw"';

    $gallery = '';

    foreach ($images as $key => $image)
        $gallery .= sprintf( $scheme, $key, $image, $medium[$key], $small[$key], $small[$key] );
    
    return sprintf( "<figure%1$s>%2$s %3$s</figure>" , $args['class'] , $gallery , $text );
}


/**
 * Shortcode Link
 *
 * mp_pops( '( link :  liens |  title: texte | text : texte | class : classe css | rel: me/nofollow )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_link( $args ){

    $args = pops_parse_args( $args, 'class=my_link');

    // Si c'est une page ou 'home' on récupère le lien
    if( is_page( strtolower($args['link']) ) )
        $link = get_permalink($args['link']);
    if( is_same( strtolower($args['link']) , 'home' ) )
        $link = get_permalink();

    // On vérifie que le lien est valid si ce n'est ni une page ni la 'home'
    if( empty($link) ) {
        if( !is_url($args['link']) ) return;
        else $link = esc_url_raw($args['link']);
    }

    // On associe le texte, titre, class, rel
    $title  = !empty($args['title']) ? ' title="'. $args['title'] .'"' : '';
    $text   = strlen($args['text']) == 0 ? esc_html($link): $args['text'] ;

    // Scheme du shortcode
    $schema   = apply_filters('pops_link_schema', '<a href="%1$s"%2$s%3$s%4$s>%5$s</a>');

    return sprintf( $schema, $link, $title, $args['class'], $args['rel'], $text );
}


/**
 * Shortcode Map
 *
 * mp_pops( '( map :  lieu |  text: texte | zoom : 1-10 | class : classe css | heigh: hauteur | with : largeur )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_map( $args ){

    $args = pops_parse_args( $args, 'class=my_map');


    $class = $args['class'];

    // On associe lieu, text, zoom, class, height, width
    $map        = str_replace( ' ' , '+' , sanitize_words($args['map']) );

    $text = '<figcaption>'. strlen($args['text']) == 0 ? sanitize_words($args['map']) : $args['text'] .'</figcaption>';
    
    $zoom   = !empty( $args['zoom']) && is_intgr($args['zoom']) && is_between($args['zoom'] , 1 , 10) ? '&zoom='.($args['zoom']+10) : '';
    
    $height = !empty( $args['height']) && is_intgr($args['height']) && is_between($args['height'] , 200 , 640) ? ' height='.$args['height'] : '';
    
    $width  = !empty( $args['width']) && is_intgr($args['width']) && is_between($args['width'] , 200 , 640) ? ' width='.$args['width'] : '';
    
    $size   = !empty( $args) && !empty($width) ? '&size='.$array['width'].'x'.$args['height'] : '&size=640x640';

    $key_api = apply_filters('pops_map_google_key_api', 'AIzaSyCKyegO4Pf19zi7yUjrQF8CuXBl85Ic3dI'); //https://console.developers.google.com

    return "<figure$class><a href='https://www.google.fr/maps/place/$map'><img src='http://maps.googleapis.com/maps/api/staticmap?center=$map$zoom$size&key=$key_api'$width$height/></a>$text</figure>";
}

/**
 * Shortcode tel
 *
 * mp_pops( '( tel :  numero de telephone |  text: texte | class : classe css )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_tel( $args ){

    $args = pops_parse_args( $args, 'class=my_phone');

    // On valide le numéro de téléphone
    if( !is_match($args['tel'] , '#^0[1-678]([-. ]?[0-9]{2}){4}$#') ) return;

    // Scheme du shortcode
    $schema   = apply_filters('pops_tel_schema', '<a href="tel:%1$s"%3$s>%2$s</a>');

    return sprintf( $schema, $args['tel'], $text, $args['class'] );
}


/**
 * Shortcode Twitter
 *
 * mp_pops( '( twitter :  peusdo twitter |  text: texte | class : classe css | rel : me )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_twitter( $args ){

    $args = pops_parse_args( $args, 'class=my_twitter');

    // On valide le pseudo twitter
    if( !is_match($args['twitter'], '/@([A-Za-z0-9_]{1,15})/') ) return;

    $twitter  = str_replace( '@' , '' ,  $args['twitter'] );
    $text     = strlen($args['text']) == 0 ? $args['twitter'] : $args['text'];

    // Scheme du shortcode
    $schema   = apply_filters('pops_twitter_schema', '<a href="https://twitter.com/%1$s"%3$s%4$s>%2$s</a>');

    return sprintf( $schema, $twitter, $text, $args['class'], $args['rel'] );
}

/**
 * Shortcode Youtube
 *
 * mp_pops( '( youtube :  url video |  text: texte | class : classe css )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_youtube( $args ){

    $args = pops_parse_args( $args, 'class=youtube');

    // On vérifie si url valid
    if( !is_url($args['youtube']) ) return;

    $youtube = str_replace ( 'watch?v=' , '' , basename($args['youtube']) );
    $text = strlen($args['text']) == 0 ? '' : '<figcaption>'. $args['text'] .'</figcaption>';

    // Scheme du shortcode
    $schema   = apply_filters('pops_youtube_schema', '<figure%3$s><iframe src="//youtube.com/embed/%1$s" width=560 height=315 frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true"></iframe>%2$s</figure>');

    return sprintf( $schema, $youtube, $text, $args['class'] );
}


/**
 * Pop instagram
 * username ou tag: @username ou tag instagram 
 *
 * https://github.com/scottsweb/wp-instagram-widget
 *
 * mp_pops( '( instagram :  instagram  |  text: texte | class : classe css | limit = 10 )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_instagram( $args){

  $args = pops_parse_args( $args, 'class=instagram');

  $args['text']  = strlen( $args['text'] ) == 0 ? $args['instagram'] : $text;
 
  // On valide le tag ou pseudo instagram
  if( !is_match($args['instagram'], '/[@|#]([A-Za-z0-9_]{1,30})/') ) return;
 
  // On verifie si tag ou username
  switch ( substr( $args['instagram'], 0, 1 ) ) {
 
    case '#':
      $url = 'https://instagram.com/explore/tags/' . str_replace( '#', '', $args['instagram'] );
      break;
    default:
      $url = 'https://instagram.com/' . str_replace( '@', '', $args['instagram'] );
      break;
  }

  // Scheme du shortcode
  $schema   = apply_filters('pops_instagram_schema', '<a href="%1$s"%3$s%4$s>%2$s</a>');

  return sprintf( $schema, $url, $args['text'], $args['class'], $args['rel'] );
}