<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage pops
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
    $pops = array('link','email','tel','image','file','twitter','youtube','audio', 'map');
    $pops = apply_filter('pops_shortcode' , $pops );

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


/***********************************************/
/*                      SHORTCODE              */
/***********************************************/


/**
 * Shortcode Audio
 *
 * mp_pops( '( audio :  *.mp3[, *.ogg] |  text : description | class : classe css )', $slug );
 *
 * ou
 *
 * $array = (
 *          'slug'  => nom du repertoire de la page,
 *          'audio' => *.mp3[, *.ogg],
 *          'text'  => 'texte',
 *          'class' => css
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_audio( $array ){

    // On réucpère la liste des fichiers
    $audio = explode( ',' , $array['audio'] );

    $path = str_replace('//', '/', CONTENT.'/'.$array['slug'].'/');
    $url  = rel2abs(str_replace(ABSPATH, '', $path) );

    // On vérifie que le premier fichier est un mp3 valide
    if( is_match($audio[0] , '([^\s]+(\.(?i)(mp3))$)') && file_exists($path.$audio[0]) )
        $mp3 = $audio[0];
    else return;

    // On vérifie que le deuxième fichier s'il existe qu'il soit du format ogg
    $ogg  = !empty($audio[1]) && is_match($audio[1] , '([^\s]+(\.(?i)(ogg))$)') ? '<source src="'. $url . $audio[1] .'" type="audio/ogg">' : '' ;

    // On associe la description
    $text  = !empty($array['text']) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';

    // On associe la classe Css
    $class = !empty($array['class']) ? ' class="'. $array['class'] .'"' : '';

    // Scheme du shortcode
    $schema = apply_filter('pops_audio_schema' ,'<figure%5$s><audio controls="controls"><source src=%1$s type="audio/mp3">%3$s<a href=%1$s download=%2$s>$mp3</a></audio>%4$s</figure>');

    return sprintf( $schema, $link_mp3, $url.$mp3, $ogg, $text, $class );
}


/**
 * Shortcode Email
 *
 * mp_pops( '( email :  s.deletang@yahoo.com |  text : texte | class : classe css | rel : me )' );
 *
 * ou
 *
 * $array = (
 *          'email' => email,
 *          'text'  => text,
 *          'rel'   => me,
 *          'class' => css
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_email( $array ){

    // On verifie de l'email est valid
    if( !is_email( $array['email'] ) ) return;
    else $email = $array['email'];

    // On associe le texte, class et rel
    $text   = !empty($array['text']) ? $array['text'] : '@'.substr( $email , 0 , strpos($email,'@') );
    $class  = !empty($array['class']) ? ' class="'. $array['class'] .'"' : '';
    $rel    = !empty($array['rel']) && is_same($array['rel'] , 'me') ? ' rel="'. $array['rel'] .'"' : '';

    // Scheme du shortcode
    $schema_with_rel = apply_filter('pops_email_schema_with_rel','<address%3$s><a href="mailto:%1$s"%4$s>%2$s</a></address>');
    $schema_no_rel   = apply_filter('pops_email_schema_no_rel','<a href="mailto:%1$s"%3$s%4$s>%2$s</a>');
    $schema          = !empty($rel) ? $schema_with_rel : $schema_no_rel;

    return sprintf( $schema, $email, $text, $class, $rel );
}


/**
 * Shortcode File
 *
 * mp_pops( '( file :  nom du fichier |  text : texte | class : classe css )', $slug );
 *
 * ou
 *
 * $array = (
 *          'slug'  => nom du repertoire de la page,
 *          'file'  => filename,
 *          'text'  => text,
 *          'class' => css
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_file( $array ){

    $path = str_replace('//', '/', CONTENT.'/'.$array['slug'].'/');
    $url  = rel2abs(str_replace(ABSPATH, '', $path) );

    // On verifie si le fichier est valid et autorisé au téléchargement
    if(
        is_match($array['file'], '([^\s]+(\.(?i)(jpe?g|png|gif|bmp|pdf|zip|mp4|webm|ogv|txt))$)')
        && file_exists($path.$array['file'])
    )
        $file  = $array['file'];
    else
        return;

    // On associe le texte, class et link_file
    $link_file  = $url . $file;
    $text       = !empty($array['text']) ? $array['text'] : $file;
    $class      = !empty($array['class']) ? ' class="'. $array['class'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filter('pops_file_schema', '<a href=%2$s download=%1$s%3$s>%4$s</a>');

    return sprintf( $schema, $file, $link_file, $class, $text );
}


/**
 * Shortcode Image
 *
 * mp_pops( '( image :  nom du fichier |  alt: texte |text : texte | class : classe css | ratio: 1OO )', $slug );
 *
 * ou
 *
 * $array = (
 *          'slug'  => nom du repertoire de la page,
 *          'image' => filename,
 *          'text'  => text,
 *          'class' => css,
 *          'ratio' => 0 à 100 %
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_image( $array ){

    $path = str_replace('//', '/', CONTENT.'/'.$array['slug'].'/');
    $url  = rel2abs(str_replace(ABSPATH, '', $path) );

    // On verifie si l'image est valide
    if(
        is_match( $array['image'] , '([^\s]+(\.(?i)(jpe?g|png|gif|bmp))$)' )
        && file_exists($path.$array['image'])
    )
        $image = $array['image'];
    else return;

    // On associe le texte, alt, class, path et url
    $url        = $url . $image;
    $path       = $path . $image;
    $alt        = !empty( $array['alt'] ) ? $array['alt'] : ' ';
    $text       = !empty( $array['text'] ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    $class      = !empty( $array['class'] ) ? ' class="'. $array['class'] .'"' : '';

    if( !empty($array['ratio'])
        && is_intgr($array['ratio'])
        && is_between($array['ratio'] , 0 , 100)
    ){
        // On récupère les dimenssions de l'image
        list( $width, $height ) = getimagesize($path);
        $ratio      = $array['ratio'];
        $height     = ' height='. $height*($ratio/100);
        $width      = ' width='. $width*($ratio/100);
    } else {
        $height = '';
        $width  = '';
    }

    // Scheme du shortcode
    $schema   = apply_filter('pops_image_schema', '<figure%4$s><img src="%1$s"%5$s%6$s alt="%2$s"/>%3$s</figure>');

    return sprintf( $schema, $url, $alt, $text, $class, $width, $height );
}


/**
 * Shortcode Link
 *
 * mp_pops( '( link :  liens |  title: texte | text : texte | class : classe css | rel: me/nofollow )' );
 *
 * ou
 *
 * $array = (
 *          'link'   => lien,
 *          'title'  => titre du lien,
 *          'text'   => text,
 *          'class'  => css,
 *          'rel'    => me/nofollow
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_link( $array ){

    // Si c'est une page ou 'home' on récupère le lien
    if( is_page( strtolower($array['link']) ) )
        $link = get_permalink($array['link']);
    if( is_same( strtolower($array['link']) , 'home' ) )
        $link = get_permalink();

    // On vérifie que le lien est valid si ce n'est ni une page ni la 'home'
    if( empty($link) ) {
        if( !is_url($array['link']) ) return;
        else $link = esc_url_raw($array['link']);
    }

    // On associe le texte, titre, class, rel
    $title      = !empty($array['title']) ? ' title="'. $array['title'] .'"' : '';
    $text       = !empty($array['text']) ? $array['text'] : esc_html($link);
    $class      = !empty($array['class']) ? ' class="'. $array['class'] .'"' : '';
    $rel        = !empty($array['rel']) && is_in($array['rel'] , array('me','nofollow')) ? ' rel="'. $array['rel'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filter('pops_link_schema', '<a href="%1$s"%2$s%3$s%4$s>%5$s</a>');

    return sprintf( $schema, $link, $title, $class, $rel, $text );
}


/**
 * Shortcode Map
 *
 * mp_pops( '( map :  lieu |  text: texte | zoom : 1-10 | class : classe css | heigh: hauteur | with : largeur )' );
 *
 * ou
 *
 * $array = (
 *          'map'   => lieu,
 *          'text'  => text,
 *          'zoom'  => 1-10,
 *          'class' => css,
 *          'height' => hauteur,
 *          'width' => largeur
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_map( $array ){

    // On associe lieu, text, zoom, class, height, width
    $map        = str_replace( ' ' , '+' , sanitize_words($array['map']) );
    $text       = !empty( $array['text'] ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '<figcaption>'. sanitize_words($array['map']) .'</figcaption>';
    $zoom       = !empty($array['zoom']) && is_intgr($array['zoom']) && is_between($array['zoom'] , 1 , 10) ? '&zoom='.($array['zoom']+10) : '';
    $height     = !empty($array['height']) && is_intgr($array['height']) && is_between($array['height'] , 200 , 640) ? ' height='.$array['height'] : '';
    $width      = !empty($array['width']) && is_intgr($array['width']) && is_between($array['width'] , 200 , 640) ? ' width='.$array['width'] : '';
    $size       = !empty($height) && !empty($width) ? '&size='.$array['width'].'x'.$array['height'] : '&size=640x640';
    $class      = !empty( $array['class'] ) ? ' class="'. $array['class'] .'"' : '';

    $key_api    = apply_filter('pops_map_google_key_api', 'AIzaSyCKyegO4Pf19zi7yUjrQF8CuXBl85Ic3dI'); //https://console.developers.google.com

    return "<figure$class><a href='https://www.google.fr/maps/place/$map'><img src='http://maps.googleapis.com/maps/api/staticmap?center=$map$zoom$size&key=$key_api'$width$height/></a>$text</figure>";
}

/**
 * Shortcode tel
 *
 * mp_pops( '( tel :  numero de telephone |  text: texte | class : classe css )' );
 *
 * ou
 *
 * $array = (
 *          'tel'   => numero de tél,
 *          'text'  => text,
 *          'class' => css,
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_tel( $array ){

    // On valide le numéro de téléphone
    if( !is_match($array['tel'] , '#^0[1-68]([-. ]?[0-9]{2}){4}$#') ) return;

    $tel        = $array['tel'];
    $text       = !empty($array['text']) ? $array['text'] : $tel;
    $class      = !empty($array['class']) ? ' class="'. $array['class'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filter('pops_tel_schema', '<a href="tel:%1$s"%3$s>%2$s</a>');

    return sprintf( $schema, $tel, $text, $class );
}


/**
 * Shortcode Twitter
 *
 * mp_pops( '( twitter :  peusdo twitter |  text: texte | class : classe css | rel : me )' );
 *
 * ou
 *
 * $array = (
 *          'tel'   => numero de tél,
 *          'text'  => text,
 *          'class' => css,
 *          'rel'   => me
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_twitter( $array ){

    // On valide le pseudo twitter
    if( !is_match($array['twitter'], '/@([A-Za-z0-9_]{1,15})/') ) return;

    $twitter     = str_replace( '@' , '' ,  $array['twitter'] );
    $text        = !empty($array['text']) ? $array['text'] : $array['twitter'];
    $class       = !empty($array['class']) ? ' class="'. $array['class'] .'"' : '';
    $rel         = !empty($array['rel']) && is_same($array['rel'] , 'me') ? ' rel="'. $array['rel'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filter('pops_twitter_schema', '<a href="https://twitter.com/%1$s"%3$s%4$s>%2$s</a>');

    return sprintf( $schema, $twitter, $text, $class, $rel );
}

/**
 * Shortcode Youtube
 *
 * mp_pops( '( youtube :  url video |  text: texte | class : classe css )' );
 *
 * ou
 *
 * $array = (
 *          'youtube' => url video,
 *          'text'    => text,
 *          'class'   => css,
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_youtube( $array ){

    // On vérifie si url valid
    if( !is_url($array['youtube']) ) return;

    $youtube     = str_replace ( 'watch?v=' , '' , basename($array['youtube']) );
    $text        = !empty($array['text']) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    $class       = !empty($array['class']) ? 'class="'. $array['class'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filter('pops_youtube_schema', '<figure%3$s><iframe src="//youtube.com/embed/%1$s" width=560 height=315 frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true"></iframe>%2$s</figure>');

    return sprintf( $schema, $youtube, $text, $class );
}
