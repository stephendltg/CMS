<?php
/**
 * template: index.php
 *
 * @package miniPops
 * @subpackage Twenty_Fifteen
 * @version 1
 */
?>
<? snippet('header'); ?>

	<main id="main" role="main">

        <article class="" >

            <header class="header" >
                <h1><?php get('title') ?></h1>
            </header>

            <section class="content">
            <?php get('content') ?>

            </section>

            <footer class="footer">
                <?php get('author') ?>
            </footer>

        </article>

    </main>

<? snippet('footer'); ?>
