<?php defined('ABSPATH') or die('No direct script access.');
/**
 * snippet: header.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */
?>
<!doctype html>
<html <?php the_lang('lang="', '" prefix="og: http://ogp.me/ns#"') ?> class="no-js">
<head>
    <?php mp_head(); ?>
</head>

<body role="document" <?php body_class() ?>>

    <header class="header" role="banner">
        
        <figure class="header-logo w150p pas">
            <?php the_blog('logo') ?>
            
        </figure>
        
        <h1 class="site-title"><a href="<?php the_blog('home'); ?>"><?php the_blog('title') ?></a></h1>
        <?php snippet('menu') ?>
        
    </header>
    