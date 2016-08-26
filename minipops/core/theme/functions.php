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

$knacss = file_get_contents(TEMPLATEPATH.'/assets/css/knacss.css');
$style  = file_get_contents(TEMPLATEPATH.'/assets/css/style.css');

mp_add_inline_style('knacss', $knacss);
mp_add_inline_style('style', $style);

$test = 'email=s.deletang@laposte.net';



