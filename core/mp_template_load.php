<?php defined('ABSPATH') or die('No direct script access.');
/**
 * CHARGEMENT DU CMS mini POPS
 *
 *
 * @package CMS mini POPS
 * @subpackage template_load
 * @version 1
 */

//var_dump( $GLOBALS );

//echo '<a href="webcal://'.HOME.'/ical.php">calendar</a>';

if ( 'HEAD' === $_SERVER['REQUEST_METHOD'] ) exit(); // shutdown

if ( is_robots() ) :
	do_action( 'do_robots' );
	return;
elseif ( is_feed() ) :
	do_action( 'do_feed' );
	return;
elseif ( is_sitemap() ) :
	do_action( 'do_sitemap' );
	return;
elseif ( is_favicon() ) :
	do_action( 'do_favicon' );
	return;
endif;

if     ( is_404()            && $template = get_template('templates/404')     	) :
elseif ( is_home()           && $template = get_template('templates/home')     	) :
elseif ( is_page()           && $template = get_page_template()            		) :
//elseif ( is_tag()            && $template = get_template('tag')           	) :
//elseif ( is_author()         && $template = get_template('author')        	) :
else :
	$template = get_template('index');
endif;

if ( $template = apply_filter( 'template_include', $template ) ){
    include( $template );
}

return;
