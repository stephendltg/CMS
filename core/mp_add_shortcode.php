<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage add shortcode
 * @version 1
 */


/***********************************************/
/*        DECLARATION  DES SHORTCODES          */
/***********************************************/

// twitter
add_shortcode('twitter', 'shortcode_twitter');

// Instagram
add_shortcode('instagram', 'shortcode_instagram');

// Instagram
add_shortcode('email', 'shortcode_email');

// link
add_shortcode('link', 'shortcode_link');

// link
add_shortcode('tel', 'shortcode_tel');

// youtube
add_shortcode('youtube', 'shortcode_youtube');

// map
add_shortcode('map', 'shortcode_map');

// image
add_shortcode('image', 'shortcode_image');

// file
add_shortcode('file', 'shortcode_file');

// audio
// add_shortcode('audio', 'shortcode_audio');


/***********************************************/
/*              LIST SHORTCODE                 */
/***********************************************/


/**
 * Twitter
 *
 * do_shortcode( '( twitter :  peusdo twitter |  text: texte | class : classe css | rel : me )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_twitter( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('twitter&class&text&rel',$param) );

    // Sanitize 
    $text  = sanitize_allspecialschars($text);
    $class = ' class="'.sanitize_html_class($class).'"';
    $class.= is_in($rel, array('me','nofollow') ) ? " rel='$rel'" : '';     

    // On valide le pseudo twitter
    if( !is_match($twitter, '/@([A-Za-z0-9_]{1,15})/') ) return;

    $twitter  = str_replace( '@' , '' ,  $twitter );
    $text     = strlen($text) == 0 ? $twitter : $text;

    // Scheme du shortcode
    $schema = apply_filters('twitter_schema', '<a href="https://twitter.com/%1$s"%3$s>%2$s</a>');

    return sprintf( $schema, $twitter, $text, $class );
}





/**
 * instagram
 * username ou tag: @username ou tag instagram 
 *
 * https://github.com/scottsweb/wp-instagram-widget
 *
 * do_shortcode( '( instagram :  instagram  |  text: texte | class : classe css | limit = 10 )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_instagram( $param){

    // paramètres du shortcode
    extract( shortcode_atts('instagram&class&text&rel',$param) );

    // Sanitize 
    $text  = sanitize_allspecialschars($text);
    $class = ' class="'.sanitize_html_class($class).'"';
    $class.= is_in($rel, array('me','nofollow') ) ? " rel='$rel'" : '';   
 
    // On valide le tag ou pseudo instagram
    if( !is_match($instagram, '/[@|#]([A-Za-z0-9_]{1,30})/') ) return;

    $text  = strlen( $text ) == 0 ? $instagram : $text;
 
    // On verifie si tag ou username
    switch ( substr( $instagram, 0, 1 ) ) {
     
        case '#':
          $url = 'https://instagram.com/explore/tags/' . str_replace( '#', '', $instagram );
          break;
        default:
          $url = 'https://instagram.com/' . str_replace( '@', '', $instagram );
          break;
    }

    // Scheme du shortcode
    $schema   = apply_filters('instagram_schema', '<a href="%1$s"%3$s>%2$s</a>');

    return sprintf( $schema, $url, $text, $class );
}






/**
 * Email
 *
 * do_shortcode( '( email :  s.deletang@yahoo.com |  text : texte | class : classe css | rel : me )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_email( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('email&class&text&rel',$param) );

    // Sanitize 
    $text  = sanitize_allspecialschars($text);
    $class = ' class="'.sanitize_html_class($class).'"';
    $class.= is_in($rel, array('me','nofollow') ) ? " rel='$rel'" : '';  

    // On verifie que l'email est valid
    if( !is_email( $email ) ) return;

    // On associe le texte, class
    $text   = strlen($text) == 0 ? '@'. sanitize_words(substr( $email , 0 , strpos($email,'@') ) ) : $text;
    $email  = str_replace('@', '[at]', $email);

    // Scheme du shortcode
    $schema   = apply_filters('email_schema','<a href="mailto:%1$s"%3$s>%2$s</a>');

    return sprintf( $schema, $email, $text, $class );
}



/**
 * Shortcode Link
 *
 * do_shortcode( '( link :  liens |  title: texte | text : texte | class : classe css | rel: me/nofollow )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_link( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('link&class&text&title',$param) );

    // Sanitize 
    $text   = sanitize_allspecialschars($text);
    $class  = ' class="'.sanitize_html_class($class).'"';
    $title  = esc_html($title);
    $class .= strlen($title) != 0 ? " title='$title'" : '';

    // Si c'est une page ou 'home' on récupère le lien
    if( is_page( strtolower($link) ) )
        $link = get_permalink($link);
    elseif( is_same( strtolower($link) , 'home' ) )
        $link = get_permalink();
    else
        $link = esc_url_raw($link);

    // On vérifie que le lien est valid si ce n'est ni une page ni la 'home'
    if( !is_url($link) ) return;

    // On associe le texte
    $text   = strlen($text) == 0 ? esc_html($link): $text;

    // Scheme du shortcode
    $schema   = apply_filters('link_schema', '<a href="%1$s"%2$s>%3$s</a>');

    return sprintf( $schema, $link, $class, $text );
}




/**
 * Shortcode tel
 *
 * do_shortcode( '( tel :  numero de telephone |  text: texte | class : classe css )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_tel( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('tel&class&text',$param) );

    // Sanitize 
    $text   = sanitize_allspecialschars($text);
    $class  = ' class="'.sanitize_html_class($class).'"';

    // On valide le numéro de téléphone
    if( !is_match($tel , '#^0[1-678]([-. ]?[0-9]{2}){4}$#') ) return;

    // On associe le texte
    $text   = strlen($text) == 0 ? esc_html($tel): $text;

    // Scheme du shortcode
    $schema   = apply_filters('tel_schema', '<a href="tel:%1$s"%3$s>%2$s</a>');

    return sprintf( $schema, $tel, $text, $class );
}





/**
 * Shortcode Youtube
 *
 * do_shortcode( '( youtube :  url video |  text: texte | class : classe css )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_youtube( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('youtube&class&text',$param) );

    // Sanitize 
    $text   = sanitize_allspecialschars($text);
    $class  = ' class="'.sanitize_html_class($class).'"';

    // On vérifie si url valid
    if( !is_url($youtube) ) return;

    $youtube = str_replace ( 'watch?v=' , '' , basename($youtube) );
    $text = strlen($text) == 0 ? '' : "<figcaption>$text</figcaption>";

    // Scheme du shortcode
    $schema   = apply_filters('youtube_schema', '<figure%3$s><iframe src="//youtube.com/embed/%1$s" width=560 height=315 frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true"></iframe>%2$s</figure>');

    return sprintf( $schema, $youtube, $text, $class );
}






/**
 * Shortcode Map
 *
 * do_shortcode( '( map :  lieu |  text: texte | zoom : 1-10 | class : classe css | heigh: hauteur | with : largeur )' );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_map( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('map&class&text&zoom&width&height',$param) );

    // Sanitize 
    $text   = sanitize_allspecialschars($text);
    $class  = ' class="'.sanitize_html_class($class).'"';
    $zoom   = intval($zoom);
    $height = intval($height);
    $width  = intval($width);

    // On associe lieu, text, zoom, class, height, width
    $map = str_replace( ' ' , '+' , sanitize_words($map) );

    $text   = '<figcaption>'. strlen($text) == 0 ? sanitize_words($map) : $text .'</figcaption>';
    $zoom   = is_between($zoom , 1 , 10) ? '&zoom='.($zoom+10) : '';
    $height = is_between($height , 200 , 640) ? " height=$height" : 0;
    $width  = is_between($width , 200 , 640)  ? " width=$width" : 0;
    $size   = $height > 0 && $width > 0 ? '&size='.$width.'x'.$height : '&size=640x640';

    $key_api = apply_filters('map_google_key_api', 'AIzaSyCKyegO4Pf19zi7yUjrQF8CuXBl85Ic3dI'); //https://console.developers.google.com

    return "<figure$class><a href='https://www.google.fr/maps/place/$map'><img src='http://maps.googleapis.com/maps/api/staticmap?center=$map$zoom$size&key=$key_api'$width$height/></a>$text</figure>";
}






/**
 * Shortcode Image
 *
 * do_shortcode( '( image :  nom du fichier |  alt: texte |text : texte | class : classe css | ratio: 1OO )', $slug );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_image( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('image&class&text&size=large',$param) );

    // Sanitize 
    $text   = sanitize_allspecialschars($text);
    $class  = ' class="'.sanitize_html_class($class).'"';
    $slug   = mp_cache_data('current_page');


    // On récupère l'image
    if( is_url($image) ){

        if( !is_array( getimagesize($image) ) )
            return;
 
    } else {

        if( !$image = get_the_image( array('file'=>$image, 'size'=>$size , 'slug'=>$slug), 'uri' ) )
            return;
    }

    // On associe le texte, class
    $text  = strlen($text) == 0 ? '' : "<figcaption>$text</figcaption>";

    // Scheme du shortcode
    $schema = apply_filters('image_schema', '<figure%1$s><img class="img" src="%2$s"/>%3$s</figure>');

    return sprintf( $schema, $class, $image, $text );
}




/**
 * Shortcode Audio
 *
 * do_shortcode( '( audio :  *.mp3[, *.ogg] |  text : description | class : classe css )', $slug );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_audio( $args ){

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
 * Shortcode File
 *
 * do_shortcode( '( file :  nom du fichier |  text : texte | class : classe css )', $slug );
 *
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function shortcode_file( $param ){

    // paramètres du shortcode
    extract( shortcode_atts('file&class&text',$param) );

    // Sanitize 
    $text   = sanitize_allspecialschars($text);
    $class  = esc_html($file) . ' class="'.sanitize_html_class($class).'"';
    $slug   = mp_cache_data('current_page');

    // On associe le texte, class et link_file
    $text  = strlen($text) == 0 ? esc_html($file) : $text;

    // On cherche ...
    if( !$file = get_attached_media( array('file'=>$file,'slug'=>$slug), 'uri' ) )
        return;

    // Scheme du shortcode
    $schema   = apply_filters('file_schema', '<a href=%1$s download=%2$s>%3$s</a>');

    return sprintf( $schema, $file[0], $class, $text );
}