<?php defined('ABSPATH') or die('No direct script access.');
/**
 * Fonction tags
 *
 *
 * @package cms mini POPS
 * @subpackage tags - shortcode de formattage
 * @version 1
 */



function search_tags( $content , $page_content_url ){

    $content    = (string) $content;
    $page_content_url   = (string) $page_content_url;

    if( !is_url($page_content_url) ) $content;

    // Liste tag à remplacer
    $tags = array('link','email','tel','image','file','twitter','youtube','audio', 'map');
    $tags = apply_filter('tags_custom' , $tags );

    // On boucle sur la recherche des champs et on affecte à la fonction associé si elle existe en lui passant les paramètres
    foreach ( $tags as $name ) {
        // Callback tag inserer dans markdown
        $call_function_tag = function( $array ) use ( $page_content_url , $name ) {
            $tag_params = trim(rtrim(ltrim($array[0] , '(') , ')'));
            $tag_params = explode( '|' , $tag_params );
            $params['content_url'] = $page_content_url;
            foreach( $tag_params as $tag_param ){
                $tag_param_name = strtolower( trim( substr( $tag_param , 0 , strpos($tag_param,':') ) ) );
                $tag_param_value = trim( substr( $tag_param , strpos($tag_param,':')+1 , size($tag_param) ) );
                $params[$tag_param_name] = $tag_param_value;
            }
        $func = "tag_$name";
        return $func( $params );
        };

        // Recherche du tag
        if( function_exists("tag_$name") )
            $content = preg_replace_callback( '/\([ \t]*'. $name .'[ \t]*:(.*?)\)/i' , $call_function_tag  , $content );
    }
    return $content;
}



function tag_audio( $array ){
    $audio = explode( ',' , $array['audio'] );
    if( is_match( $audio[0] , '([^\s]+(\.(?i)(mp3))$)' ) ) $mp3 = $audio[0]; else return;
    $ogg        = ( !empty($audio[1]) && is_match($audio[1] , '([^\s]+(\.(?i)(ogg))$)') ) ? '<source src="'. $array['content_url'] . '/' . $audio[1] .'" type="audio/ogg">' : '' ;
    $link_mp3   = $array['content_url'] . '/' . $mp3;
    $text       = ( !empty($array['text']) ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $tag_audio  = "<figure$class><audio controls='controls'><source src='$link_mp3' type='audio/mp3'>$ogg<a href='$link_mp3' download='$mp3'>$mp3</a></audio>$text</figure>";
    $tag_audio  = apply_filter('tag_audio', $tag_audio );
    return $tag_audio;
}

function tag_email( $array ){
    if( !is_email($array['email']) ) return;
    $email      = $array['email'];
    $text       = ( !empty($array['text']) ) ? $array['text'] : '@'.substr( $email , 0 , strpos($email,'@') );
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $rel        = ( !empty($array['rel']) && is_same($array['rel'] , 'me')  )? ' rel="'. $array['rel'] .'"' : '';
    $tag_email  = ( !empty($rel)) ? "<address$class><a href='mailto:$email'$rel>$text</a></address>" : "<a href='mailto:$email'$class$rel>$text</a>";
    $tag_email  = apply_filter('tag_email', $tag_email );
    return $tag_email;
}

function tag_file( $array ){
    if( !is_match($array['file'] , '([^\s]+(\.(?i)(jpe?g|png|gif|bmp|pdf|zip|mp4|webm|ogv|txt))$)') ) return;
    $file       = $array['file'];
    $link_file  = $array['content_url'] . '/' . $array['file'];
    $text       = ( !empty($array['text']) ) ? $array['text'] : $file;
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $tag_file   = "<a href='$link_file' download='$file'$class>$text</a>";
    $tag_file   = apply_filter('tag_file', $tag_file );
    return $tag_file;
}

function tag_image( $array ){
    if( !is_match( $array['image'] , '([^\s]+(\.(?i)(jpe?g|png|gif|bmp))$)' ) ) return;
    $image      = $array['content_url'] . '/' . $array['image'];
    $alt        = ( !empty( $array['alt'] ) ) ? $array['alt'] : ' ';
    $text       = ( !empty( $array['text'] ) ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    list( $width, $height ) = getimagesize($image);
    $ratio      = ( !empty($array['ratio']) && is_intgr($array['ratio']) && is_between($array['ratio'] , 0 , 100) ) ? $array['ratio'] : 100;
    $height     = ' height='. $height*($ratio/100);
    $width      = ' width='. $width*($ratio/100);
    $class      = ( !empty( $array['class'] ) ) ? ' class="'. $array['class'] .'"' : '';
    $tag_image  = "<figure$class><img src='$image'$width$height alt='$alt'/>$text</figure>";
    $tag_image  = apply_filter('tag_image', $tag_image );
    return $tag_image;
}

function tag_link( $array ){
    global $is_mod_rewrite;
    if( is_page( strtolower($array['link']) ) ) $link = get_permalink($array['link']);
    if( is_same( strtolower($array['link']) , 'home' ) ) $link = HOME;
    if( empty($link) ) { if( !is_url($array['link']) ) return; else $link = $array['link']; }
    $title      = ( !empty($array['title']) ) ? ' title="'. $array['title'] .'"' : '';
    $text       = ( !empty($array['text']) ) ? $array['text'] : esc_html($link);
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $rel        = ( !empty($array['rel']) && is_in($array['rel'] , array('me','nofollow'))  )? ' rel="'. $array['rel'] .'"' : '';
    $tag_link   = "<a href='$link'$title$class$rel>$text</a>";
    $tag_link   = apply_filter('tag_link', $tag_link );
    return $tag_link;
}

function tag_map( $array ){
    // KEY API : https://console.developers.google.com
    $map        = str_replace( ' ' , '+' , sanitize_words($array['map']) );
    $text       = ( !empty( $array['text'] ) ) ? '<figcaption>'. $array['text'] .'</figcaption>' : '<figcaption>'. sanitize_words($array['map']) .'</figcaption>';
    $zoom       = ( !empty($array['zoom']) && is_intgr($array['zoom']) && is_between($array['zoom'] , 1 , 10) ) ? '&zoom='.($array['zoom']+10) : '';
    $height     = ( !empty($array['height']) && is_intgr($array['height']) && is_between($array['height'] , 200 , 640) ) ? ' height='.$array['height'] : '';
    $width      = ( !empty($array['width']) && is_intgr($array['width']) && is_between($array['width'] , 200 , 640) ) ? ' width='.$array['width'] : '';
    $size       = ( !empty($height) && !empty($width) ) ? '&size='.$array['width'].'x'.$array['height'] : '&size=640x640';
    $class      = ( !empty( $array['class'] ) ) ? ' class="'. $array['class'] .'"' : '';
    $tag_map    = "<figure$class><a href='https://www.google.fr/maps/place/$map'><img src='http://maps.googleapis.com/maps/api/staticmap?center=$map$zoom$size&key=AIzaSyCKyegO4Pf19zi7yUjrQF8CuXBl85Ic3dI'$width$height/></a>$text</figure>";
    $tag_map    = apply_filter('tag_map', $tag_map );
    return $tag_map;
}

function tag_tel( $array ){
    if( !is_match($array['tel'] , '#^0[1-68]([-. ]?[0-9]{2}){4}$#') ) return;
    $tel        = $array['tel'];
    $text       = ( !empty($array['text']) ) ? $array['text'] : $tel;
    $class      = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $tag_tel    = "<a href='tel:$tel'$class>$text</a>";
    $tag_tel    = apply_filter('tag_tel', $tag_tel );
    return $tag_tel;
}

function tag_twitter( $array ){
    if( !is_match($array['twitter'], '/@([A-Za-z0-9_]{1,15})/') ) return;
    $twitter     = str_replace( '@' , '' ,  $array['twitter'] );
    $text        = ( !empty($array['text']) ) ? $array['text'] : $array['twitter'];
    $class       = ( !empty($array['class']) ) ? ' class="'. $array['class'] .'"' : '';
    $rel         = ( !empty($array['rel']) && is_same($array['rel'] , 'me')  )? ' rel="'. $array['rel'] .'"' : '';
    $tag_twitter = "<a href='https://twitter.com/$twitter'$class$rel>$text</a>";
    $tag_twitter = apply_filter('tag_twitter', $tag_twitter );
    return $tag_twitter;
}

function tag_youtube( $array ){
    if( !is_url($array['youtube']) ) return;
    $youtube     = str_replace ( 'watch?v=' , '' , basename($array['youtube']) );
    $text        = ( !empty($array['text']) )? '<figcaption>'. $array['text'] .'</figcaption>' : '';
    $class       = ( !empty($array['class']) ) ? 'class="'. $array['class'] .'"' : '';
    $tag_youtube = "<figure $class ><iframe src='//youtube.com/embed/$youtube' width=560 height= 315 frameborder='0' webkitallowfullscreen='true' mozallowfullscreen='true' allowfullscreen='true'></iframe>$text</figure>";
    $tag_youtube = apply_filter('tag_youtube', $tag_youtube );
    return $tag_youtube;
}
