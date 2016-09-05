<?php defined('ABSPATH') or die('No direct script access.');
/*
Theme Name: Rhythmicon
Theme URI:
Description: Theme by default
Version: 1.0
Author: Stephen Deletang
Author URI:
*/


// Déclaration des feuilles de style
add_inline_style('knacss', file_get_content(MP_TEMPLATE_DIR.'/assets/css/knacss.css' ) );
add_inline_style('style', file_get_content(MP_TEMPLATE_DIR.'/assets/css/style.css' ) );

add_action('enqueue_styles','load_theme_style');

function load_theme_style(){

    mp_enqueue_style('knacss');
    mp_enqueue_style('style');
}

