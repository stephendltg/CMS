<?php defined('ABSPATH') or die('No direct script access.');
/**
 * template: page.php
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
<body <?php body_class()?> role="document">

<!--[if lt IE 8]>
    <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
<![endif]-->

<div class="wrapper flex-container">

    <header role="banner" class = "error-header tiny-hidden">
        <?php the_blog('logo') ?>

    </header>

    <main role="main" id="error-main" class="error-main flex-item-fluid flex-item-first" >

        <article role="content" class="article txtcenter">

            <aside role="complementary" class="error-aside center">
                <?php the_page('content') ?>

            </aside>

            <header role="banner">
                <h1 class="error-title"><?php the_page('title') ?></h1>

            </header>
            
            <main role="main" id="main-article" class="error-content">
                <p><?php the_page('description') ?></p>

            </main>

            <footer role="contentinfo" classe="error-footer">
                <a class="button-home" href="<?php the_blog('home'); ?>"><?php _e('Home') ?></a>
            </footer>

        </article>

    </main>

</div>


<!-- Asynchronous google analytics; this is the official snippet.
     Replace UA-XXXXXX-XX with your site's ID and uncomment to enable.

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-XXXXXX-XX', 'auto');
  ga('send', 'pageview');

</script>
-->

</body>
</html>