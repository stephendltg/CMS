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
mp_enqueue_style('knacss', TEMPLATEURL.'/assets/css/knacss.css' );
mp_enqueue_style('style', TEMPLATEURL.'/assets/css/style.css' );


// On force l'optimisation des fichiers CSS (=>firewall)
add_filter( 'pre_option_site_optimize_files_css', function(){return true;} );