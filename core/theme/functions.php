<?php defined('ABSPATH') or die('No direct script access.');
/**
 * functions.php
 *
 * @package miniPops
 * @subpackage Theme : Rhythmicon
 * @version 1
 */

// on Ajoute les meta pour le theme
add_action('mp_head','Rhythmicon_head_meta_theme', 10 );

function Rhythmicon_head_meta_theme(){
    $meta  ='<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->'."\n";
    $meta .= '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable = no">'."\n";
    echo $meta;
}

// Déclaration des feuilles de style
enqueue_style('knacss', TEMPLATEURL.'/assets/css/knacss.css' );
enqueue_style('style', TEMPLATEURL.'/assets/css/style.css' );
