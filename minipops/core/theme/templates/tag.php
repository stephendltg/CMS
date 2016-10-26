<?php defined('ABSPATH') or die('No direct script access.');
/**
 * template: page.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */
?>
<?php snippet('header'); ?>

    <table role="presentation">
      <!-- surtout pas d'attribut summary -->
        <tr>
            <td rowspan="2" class="main small-visible large-w66">

                <main id="main" role="main">

                    <?php the_breadcrumb() ?>


                    <?php search_tag('<h1>','</h1>') ?>

                    <?php while ( have_pages('tag') ) : ?>

                    <article class="article" >

                        <header class="header" >
                            <?php the_page('title', '<h1>', '</h1>'); ?>
                        </header>

                        <section class="description">
                            <?php the_page('excerpt') ?>
                            <?php the_thumbnail() ?>
                        </section>

                    </article>

                <?php endwhile ?>

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
