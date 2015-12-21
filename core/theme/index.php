<?php
/**
 * template: index.php
 *
 * @package miniPops
 * @subpackage Theme : Rhythmicon
 * @version 1
 */
?>
<?php snippet('header'); ?>

    <table role="presentation">
      <!-- surtout pas d'attribut summary -->
        <tr>
            <td rowspan="2" class="main small-visible large-w66">

                <main id="main" role="main">

                    <nav role="navigation" aria-label="Vous êtes ici : " id="breadcrumb" class="info mbl"><span>//</span>
                        <?php the_breadcrumb(' / ') ?>
                    </nav>

                    <article class="article mtl" >

                        <header class="header" >
                            <h1><?php the_page('title') ?></h1>
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

            </td>
            <td class="nav small-visible">
                <?php snippet('menu') ?>
            </td>
        </tr>
        <tr>
            <td class="aside small-visible">
                <?php snippet('aside'); ?>
            </td>
        </tr>
    </table>

    <?php snippet('footer'); ?>

<?php mp_footer(); ?>
</body>
</html>
