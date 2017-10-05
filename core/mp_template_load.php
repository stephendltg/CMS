<?php defined('ABSPATH') or die('No direct script access.');
/**
 * CHARGEMENT DU CMS mini POPS
 *
 *
 * @package CMS mini POPS
 * @subpackage template_load
 * @version 1
 */

//echo '<a href="webcal://'.guess_url().'/ical.php">calendar</a>';

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
elseif ( is_humans() ) :
	do_action( 'do_humans' );
	return;
endif;

if     ( is_404()            && $template = get_template('templates/404')     	) :
elseif ( is_home()           && $template = get_template('templates/home')     	) :
elseif ( is_page()           && $template = get_page_template()            		) :
elseif ( is_tag()            && $template = get_template('templates/tag')       ) :
else :
	$template = get_template('index');
endif;

if ( $template = apply_filters( 'template_include', $template ) ):
	do_action('TEMPLATE_REDIRECT');
    include( $template );
endif;

return;
