<?php defined('ABSPATH') or die('No direct script access.');
/*
Theme Name: Rhythmicon
Theme URI:
Description: Theme by default
Version: 1.0
Author: Stephen Deletang
Author URI:
*/


// Déclaration de la feuille de style
mp_register_style('style', 'assets/sass/style.scss', array('css-dir'=> ABSPATH, 'css-url'=> MP_HOME) );

// Déclaration script pour gérér les prefix naviguateur
add_inline_script('prefix-style', file_get_content(MP_TEMPLATE_DIR.'/assets/js/prefixfree.min.js') );