<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage pops
 * @version 1
 */



function pops( $content , $page_content_url ){

    $content           = (string) $content;
    $page_content_url  = (string) $page_content_url;

    if( !is_url($page_content_url) ) $content;

    // Liste pops à remplacer
    $pops = array('link','email','tel','image','file','twitter','youtube','audio', 'map');
    $pops = apply_filter('pops_custom' , $pops );

    // On boucle sur la recherche des champs et on affecte à la fonction associé si elle existe en lui passant les paramètres
    foreach ( $pops as $name ) {
        // Callback pops inserer dans une chaine de caractère
        $call_function_pops = function( $array ) use ( $page_content_url , $name ) {
            $pops_params = trim(rtrim(ltrim($array[0] , '(') , ')'));
            $pops_params = explode( '|' , $pops_params );
            $params['content_url'] = $page_content_url;
            foreach( $pops_params as $pops_param ){
                $pops_param_name = strtolower( trim( substr( $pops_param , 0 , strpos($pops_param,':') ) ) );
                $pops_param_value = trim( substr( $pops_param , strpos($pops_param,':')+1 , size($pops_param) ) );
                $params[$pops_param_name] = $pops_param_value;
            }
        $func = "pops_$name";
        return $func( $params );
        };

        // Recherche du pops
        if( function_exists("pops_$name") )
            $content = preg_replace_callback( '/\([ \t]*'. $name .'[ \t]*:(.*?)\)/i' , $call_function_pops  , $content );
    }
    return $content;
}



function pops_audio( $array ){
    $audio = explode( ',' , $array['audio'] );
    if( is_match( $audio[0] , '([^\s]+(\.(?i)(mp3))$)' ) ) $mp3 = $audio[0]; else return;
    $ogg        = ( !empty($audio[1]) && is_match($audio[1] , '([^\s]+(\.(?i)(ogg))$)') ) ? '<source src="'. $array['content_url'] . '/' . $audio[1] .'" type="audio/ogg">' : '' ;
    $link_mp3   = $array['content_url'] . '/' . $mp3;
    $text       = ( !empty($array['text']) ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $pops_audio  = "<figure$class><audio controls='controls'><source src='$link_mp3' type='audio/mp3'>$ogg<a href='$link_mp3' download='$mp3'>$mp3</a></audio>$text</figure>";
    return $pops_audio;
}

function pops_email( $array ){
    if( !is_email($array['email']) ) return;
    $email      = $array['email'];
    $text       = ( !empty($array['text']) ) ? $array['text'] : '@'.substr( $email , 0 , strpos($email,'@') );
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $rel        = ( !empty($array['rel']) && is_same($array['rel'] , 'me')  )? ' rel="'. $array['rel'] .'"' : '';
    $pops_email  = ( !empty($rel)) ? "<address$class><a href='mailto:$email'$rel>$text</a></address>" : "<a href='mailto:$email'$class$rel>$text</a>";
    return $pops_email;
}

function pops_file( $array ){
    if( !is_match($array['file'] , '([^\s]+(\.(?i)(jpe?g|png|gif|bmp|pdf|zip|mp4|webm|ogv|txt))$)') ) return;
    $file       = $array['file'];
    $link_file  = $array['content_url'] . '/' . $array['file'];
    $text       = ( !empty($array['text']) ) ? $array['text'] : $file;
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $pops_file   = "<a href='$link_file' download='$file'$class>$text</a>";
    return $pops_file;
}

function pops_image( $array ){
    if( !is_match( $array['image'] , '([^\s]+(\.(?i)(jpe?g|png|gif|bmp))$)' ) ) return;
    $image      = $array['content_url'] . '/' . $array['image'];
    $alt        = ( !empty( $array['alt'] ) ) ? $array['alt'] : ' ';
    $text       = ( !empty( $array['text'] ) ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    list( $width, $height ) = getimagesize($image);
    $ratio      = ( !empty($array['ratio']) && is_intgr($array['ratio']) && is_between($array['ratio'] , 0 , 100) ) ? $array['ratio'] : 100;
    $height     = ' height='. $height*($ratio/100);
    $width      = ' width='. $width*($ratio/100);
    $class      = ( !empty( $array['class'] ) ) ? ' class="'. $array['class'] .'"' : '';
    $pops_image  = "<figure$class><img src='$image'$width$height alt='$alt'/>$text</figure>";
    return $pops_image;
}

function pops_link( $array ){
    global $is_mod_rewrite;
    if( is_page( strtolower($array['link']) ) ) $link = get_permalink($array['link']);
    if( is_same( strtolower($array['link']) , 'home' ) ) $link = HOME;
    if( empty($link) ) { if( !is_url($array['link']) ) return; else $link = $array['link']; }
    $title      = ( !empty($array['title']) ) ? ' title="'. $array['title'] .'"' : '';
    $text       = ( !empty($array['text']) ) ? $array['text'] : esc_html($link);
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $rel        = ( !empty($array['rel']) && is_in($array['rel'] , array('me','nofollow'))  )? ' rel="'. $array['rel'] .'"' : '';
    $pops_link   = "<a href='$link'$title$class$rel>$text</a>";
    return $pops_link;
}

function pops_map( $array ){
    // KEY API : https://console.developers.google.com
    $map        = str_replace( ' ' , '+' , sanitize_words($array['map']) );
    $text       = ( !empty( $array['text'] ) ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '<figcaption>'. sanitize_words($array['map']) .'</figcaption>';
    $zoom       = ( !empty($array['zoom']) && is_intgr($array['zoom']) && is_between($array['zoom'] , 1 , 10) ) ? '&zoom='.($array['zoom']+10) : '';
    $height     = ( !empty($array['height']) && is_intgr($array['height']) && is_between($array['height'] , 200 , 640) ) ? ' height='.$array['height'] : '';
    $width      = ( !empty($array['width']) && is_intgr($array['width']) && is_between($array['width'] , 200 , 640) ) ? ' width='.$array['width'] : '';
    $size       = ( !empty($height) && !empty($width) ) ? '&size='.$array['width'].'x'.$array['height'] : '&size=640x640';
    $class      = ( !empty( $array['class'] ) ) ? ' class="'. $array['class'] .'"' : '';
    $pops_map    = "<figure$class><a href='https://www.google.fr/maps/place/$map'><img src='http://maps.googleapis.com/maps/api/staticmap?center=$map$zoom$size&key=AIzaSyCKyegO4Pf19zi7yUjrQF8CuXBl85Ic3dI'$width$height/></a>$text</figure>";
    return $pops_map;
}

function pops_tel( $array ){
    if( !is_match($array['tel'] , '#^0[1-68]([-. ]?[0-9]{2}){4}$#') ) return;
    $tel        = $array['tel'];
    $text       = ( !empty($array['text']) ) ? $array['text'] : $tel;
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $pops_tel    = "<a href='tel:$tel'$class>$text</a>";
    return $pops_tel;
}

function pops_twitter( $array ){
    if( !is_match($array['twitter'], '/@([A-Za-z0-9_]{1,15})/') ) return;
    $twitter     = str_replace( '@' , '' ,  $array['twitter'] );
    $text        = ( !empty($array['text']) ) ? $array['text'] : $array['twitter'];
    $class       = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $rel         = ( !empty($array['rel']) && is_same($array['rel'] , 'me')  )? ' rel="'. $array['rel'] .'"' : '';
    $pops_twitter = "<a href='https://twitter.com/$twitter'$class$rel>$text</a>";
    return $pops_twitter;
}

function pops_youtube( $array ){
    if( !is_url($array['youtube']) ) return;
    $youtube     = str_replace ( 'watch?v=' , '' , basename($array['youtube']) );
    $text        = ( !empty($array['text']) )? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    $class       = ( !empty($array['class']) ) ? 'class="'. $array['class'] .'"' : '';
    $pops_youtube = "<figure $class ><iframe src='//youtube.com/embed/$youtube' width=560 height= 315 frameborder='0' webkitallowfullscreen='true' mozallowfullscreen='true' allowfullscreen='true'></iframe>$text</figure>";
    return $pops_youtube;
}
