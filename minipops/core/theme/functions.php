<?php defined('ABSPATH') or die('No direct script access.');
/*
Theme Name: Rhythmicon
Theme URI:
Description: Theme by default
Version: 1.0
Author: Stephen Deletang
Author URI:
*/


// On compile la feuille de style
$url = mp_compass(MP_TEMPLATE_URL.'/assets/sass/style.scss', array() );

// Déclaration de la feuille de style uniquement si la compilation c'est bien passé.
if( $url != null )
	add_inline_style('defaut-style', file_get_content(MP_TEMPLATE_DIR.'/assets/sass/style.css') );


// Déclaration script pour gérér les prefix naviguateur
add_inline_script('prefix-style', file_get_content(MP_TEMPLATE_DIR.'/assets/js/prefixfree.min.js') );