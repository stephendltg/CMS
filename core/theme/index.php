<?php
/**
 * template: index.php
 *
 * @package miniPops
 * @subpackage Twenty_Fifteen
 * @version 1
 */
?>
<?php snippet('header'); ?>

	<main id="main" role="main">

        <article class="" >

            <header class="header" >
                <h1><?php get('Title') ?></h1>
            </header>

            <section class="content">
            <?php get('Content') ?>

            </section>

            <footer class="footer">
                <?php get('Author') ?>
            </footer>

        </article>

    </main>

<?php snippet('footer'); ?>
