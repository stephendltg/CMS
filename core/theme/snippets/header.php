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

    <style>

        .box-label,.box-content{
            display: block;
            margin:0;
            padding: 20px;
            background-color: #eee;
        }

        /* Box */
        .box-toggle:checked ~ .box-content{
            transform: none;
         }
        .box-content{
            transform: perspective(500px) rotateX(-.25turn) scaleY(2);
            transform-origin: 50% 0;
            transition: .4s;
         }
        /* End box */


    </style>
</head>

<body role="document">

    <header class="header ptl pbl txtcenter mtl mbl" role="banner">
        <hgroup class="title mtl">
            <h1 class="site-title h3-like mbn"><a href="<?php the_blog('home'); ?>"><?php the_blog('title') ?></a></h1>
            <h2 class="site-subtitle h6-like mtn"><?php the_blog('subtitle') ?></h2>
        </hgroup>
    </header>

    <!-- box -->
    <div class="box">
        <label class="box-label">Open this box
            <input type="checkbox" class="box-toggle visually-hidden" />
            <span class="box-content">my message !</span>
        </label>
    </div>

    <!-- box -->
    <div class="box mtm">
        <input type="checkbox" id="box-toggle" class="box-toggle visually-hidden" />
        <label for="box-toggle" class="box-label" onclick>Open this box</label>
        <span class="box-content">
            <p> my message !</p>
        </span>
    </div>
