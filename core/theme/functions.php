<?php defined('ABSPATH') or die('No direct script access.');
/*
Theme Name: Rhythmicon
Theme URI:
Description: Theme by default
Version: 1.0
Author: Stephen Deletang
Author URI:
*/

// on Ajoute les meta pour le theme
add_action('mp_head','rhythmicon_head_meta_theme', 10 );

function rhythmicon_head_meta_theme(){
    $meta  ='<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->'."\n";
    $meta .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable = no">'."\n";
    echo $meta;
}

// Déclaration des feuilles de style
mp_enqueue_style('knacss', TEMPLATEURL.'/assets/css/knacss.css' );
mp_enqueue_style('style', TEMPLATEURL.'/assets/css/style.css' );

// On optimise le code
add_filter('do_optimize', function(){return true;});
//add_filter('do_optimize_html', function(){return false;});
add_filter('do_optimize_css', function(){return true;});

// On désactive le cache
add_filter('do_cache', function(){return false;});

