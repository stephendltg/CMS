<?php defined('ABSPATH') or die('No direct script access.');
/**
 * template: index.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */

?>

<?php snippet('header'); ?>

<main id="main" role="main">

    <?php //the_breadcrumb() ?>

    <article class="article" >
        
        <section class="content">
            <?php the_page('content') ?>
        </section>

        <header class="entry-header" >
            <?php the_page('title', '<h1>', '</h1>') ?>
            
        </header>

        <section class="content">
            <?php the_page('content') ?>
        </section>

        <? if( is_page() ) : ?>
        <footer class="footer">
            <?php the_page('author') ?>
        </footer>
        <? endif ?>

    </article>

</main>
        
<?php snippet('footer'); ?>

<?php mp_footer(); ?>
</body>
</html>
