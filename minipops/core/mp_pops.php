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
    $pops = array('link','email','tel','image','gallery','file','twitter','youtube','audio', 'map');
    $pops = apply_filters('pops_shortcode' , $pops );

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
 *          'audio' => *.mp3 [.ogg]
 *          'text'  => 'texte',
 *          'class' => css
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_audio( $args ){

    $args = parse_args( $args, array(
            'class' => 'my_audio',
            'text'  => null,
            'slug'  => isset($GLOBALS['query']) ? $GLOBALS['query'] : ''
            ));

    // On nettoie
    $slug  = trim( $args['slug'], '/');
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On prépare le fichier audio pour la recherche
    $audio = sanitize_file_name($args['audio']);
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
    $text  = strlen($text) == 0 ? '' : "<figcaption>$text</figcaption>";

    // Scheme du shortcode
    $schema = apply_filters('pops_audio_schema' ,'<figure%s><audio controls="controls">%s<a href=%s download=%s>$mp3</a></audio>%s</figure>');

    return sprintf( $schema, $class, $medias, $download, basename($download), $text );
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
function pops_email( $args ){

    $args = parse_args( $args, array(
            'class' => 'my_email',
            'text'  => null
            ));

     // On nettoie
    $slug  = trim( $args['slug'], '/');
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On verifie de l'email est valid
    if( !is_email( $args['email'] ) ) return;
    else $email = $args['email'];

    // On associe le texte, class et rel
    $text   = strlen($text) == 0 ? '@'. sanitize_words(substr( $email , 0 , strpos($email,'@') ) ) : $text;
    $rel    = !empty($args['rel']) && is_same($args['rel'] , 'me') ? ' rel="'. $args['rel'] .'"' : '';

    $email  = str_replace('@', '(at)', $email);

    // Scheme du shortcode
    $schema_with_rel = apply_filters('pops_email_schema_with_rel','<address%3$s><a href="mailto:?to=%1$s"%4$s>%2$s</a></address>');
    $schema_no_rel   = apply_filters('pops_email_schema_no_rel','<a href="mailto:%1$s"%3$s%4$s>%2$s</a>');
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
function pops_file( $args ){

    $args = parse_args( $args, array(
            'class' => 'my_file',
            'text'  => null,
            'slug'  => isset($GLOBALS['query']) ? $GLOBALS['query'] : ''
            ));

    // On nettoie
    $slug  = trim( $args['slug'], '/');
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On cherche ...
    $file = get_attached_media(array('file'=>$args['file'],'slug'=>$args['slug'],'type'=>'pdf,zip,ppt,pps,xls,doc,docx,txt'), 'uri');

    if( empty($file) ) return;

    // On associe le texte, class et link_file
    $text  = strlen($text) == 0 ? $args['file'] : $text;

    // Scheme du shortcode
    $schema   = apply_filters('pops_file_schema', '<a href=%2$s download=%1$s%3$s>%4$s</a>');

    return sprintf( $schema, $args['file'], $file[0], $class, $text );
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
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_image( $args ){

    $args = parse_args( $args, array(
        'class' => 'my_image',
        'text'  => null,
        'slug'  => isset($GLOBALS['query']) ? $GLOBALS['query'] : ''
        ));

    // On nettoie
    $slug  = trim( $args['slug'], '/');
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On récupère l'image
    $url = get_the_image( array('file'=>$args['image'], 'slug'=>$slug), 'uri' );

    // On verifie si l'image existe
    if( !$url ) return;

    // On associe le texte, class
    $text  = strlen($text) == 0 ? '' : "<figcaption>$text</figcaption>";

    // Scheme du shortcode
    $schema   = apply_filters('pops_image_schema', '<figure%s><img src="%s"/>%s</figure>');

    return sprintf( $schema, $class, $url, $text );
}

/**
 * Shortcode Gallery
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_gallery( $args ){

    $args = parse_args( $args, array(
        'class' => 'my_gallery',
        'text'  => null,
        'slug'  => isset($GLOBALS['query']) ? $GLOBALS['query'] : ''
        ));

    // On nettoie
    $slug  = trim( $args['slug'], '/');
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On récupère l'image en mode large
    $images = get_the_image( array('size'=>'large','file'=>$args['gallery'],'slug'=>$slug,'max'=>'auto'), 'uri' );

    // On verifie si l'image existe
    if( empty($images) ) return;

    // On cherche les différents formats
    $small  = get_the_image( array('size'=>'small','file'=>$args['gallery'],'slug'=>$slug, 'max'=>'auto'), 'uri' );
    $medium = get_the_image( array('size'=>'medium','file'=>$args['gallery'],'slug'=>$slug, 'max'=>'auto'), 'uri' );


    // On associe le texte, class
    $text  = strlen($text) == 0 ? '' : "<figcaption>$text</figcaption>";

    // Scheme du shortcode
    $scheme = '<img class="item-%s" srcset="%s 1024w, %s 640w, %s 320w" sizes="(min-width: 36em) 33.3vw, 100vw" src="%s">';

    // fluid+gouttière pour gallery
    // 'sizes="(min-width: 36em) calc(.333 * (100vw-[$gutter]em) ), 100vw"';

    $gallery = '';

    foreach ($images as $key => $image)
        $gallery .= sprintf( $scheme, $key, $image[$key], $medium[$key], $small[$key], $small[$key] );
    
    return "<figure $class >$gallery $text</figure>";
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
function pops_link( $args ){

    $args = parse_args( $args, array(
        'class' => 'my_link',
        'text'  => null
        ));

     // On nettoie
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

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
    $title      = !empty($args['title']) ? ' title="'. $args['title'] .'"' : '';
    $text       = strlen($text) ? esc_html($link): $text ;
    $rel        = !empty($args['rel']) && is_in($args['rel'] , array('me','nofollow')) ? ' rel="'. $args['rel'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filters('pops_link_schema', '<a href="%1$s"%2$s%3$s%4$s>%5$s</a>');

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
function pops_map( $args ){

    $args = parse_args( $args, array(
        'class' => 'my_map'
        ));

     // On nettoie
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On associe lieu, text, zoom, class, height, width
    $map        = str_replace( ' ' , '+' , sanitize_words($args['map']) );

    $text = '<figcaption>'. strlen($text) == 0 ? sanitize_words($args['map']) : $text .'</figcaption>';
    
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
function pops_tel( $args ){

    $args = parse_args( $args, array(
        'class' => 'my_phone',
        'text'  => null
        ));

     // On nettoie
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On valide le numéro de téléphone
    if( !is_match($args['tel'] , '#^0[1-68]([-. ]?[0-9]{2}){4}$#') ) return;

    $tel        = $args['tel'];
    $text       = strlen($text) ? $tel : $text;

    // Scheme du shortcode
    $schema   = apply_filters('pops_tel_schema', '<a href="tel:%1$s"%3$s>%2$s</a>');

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
 *          'twitter'   => numero de tél,
 *          'text'  => text,
 *          'class' => css,
 *          'rel'   => me
 *          )
 * @param  $array     Paramètres du shortcode
 * @return string     Retourne le contenu parsé par le shortcode
 */
function pops_twitter( $args ){

    $args = parse_args( $args, array(
        'class' => 'my_twitter',
        'text'  => null
        ));

     // On nettoie
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On valide le pseudo twitter
    if( !is_match($args['twitter'], '/@([A-Za-z0-9_]{1,15})/') ) return;

    $twitter  = str_replace( '@' , '' ,  $args['twitter'] );
    $text     = strlen($text) ? $args['twitter'] : $text;
    $rel      = !empty($args['rel']) && is_same($args['rel'] , 'me') ? ' rel="'. $args['rel'] .'"' : '';

    // Scheme du shortcode
    $schema   = apply_filters('pops_twitter_schema', '<a href="https://twitter.com/%1$s"%3$s%4$s>%2$s</a>');

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
function pops_youtube( $args ){

    $args = parse_args( $args, array(
        'class' => 'youtube',
        'text'  => null
        ));

     // On nettoie
    $text  = sanitize_allspecialschars($args['text']);
    $class = ' class="'.sanitize_html_class($args['class']).'"';

    // On vérifie si url valid
    if( !is_url($args['youtube']) ) return;

    $youtube     = str_replace ( 'watch?v=' , '' , basename($args['youtube']) );
    $text        = strlen($text) ? '' : '<figcaption>'. $text .'</figcaption>';

    // Scheme du shortcode
    $schema   = apply_filters('pops_youtube_schema', '<figure%3$s><iframe src="//youtube.com/embed/%1$s" width=560 height=315 frameborder="0" webkitallowfullscreen="true" mozallowfullscreen="true" allowfullscreen="true"></iframe>%2$s</figure>');

    return sprintf( $schema, $youtube, $text, $class );
}
