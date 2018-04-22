<?php defined('ABSPATH') or die('No direct script access.');
/**
 * template: index.php
 *
 * @package miniPops
 * @subpackage Rhythmicon
 * @version 1
 */

//_echo ( pops_map('map=rue du calvaire 44000 nantes') );


add_shortcode('test', 'lekleklee');
add_shortcode('manger', 'lekleklee');

//remove_all_shortcode();

$content = "
j'aim bien mager (test: la soupe)
oui mais bon (test: manger | class: test) mlmslms smls mqsqs ( manger: dsdsdsd) mlml
(audio: lire.mp3)
:mlmsldmlsdml
";

$content = "
j'aim bien mager (testss: la soupe)
oui mais bon  mlmslms smls mqsqs  mlml
(twitter: @stephen | class: test )
:mlmsldmlsdml
";

$test = do_shortcode( $content );
_echo($test);


?>

<?php snippet('header'); ?>

<?php //snippet('form'); ?>

    <table role="presentation">
      <!-- surtout pas d'attribut summary -->
        <tr>
            <td rowspan="2" class="main small-visible large-w66">

                <main id="main" role="main">

                     <?php the_breadcrumb() ?>

                    <article class="article mtl" >

                        <header class="header" >
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

            </td>
            <td class="nav small-visible">
                <?php snippet('menu') ?>
            </td>
        </tr>
        <tr>
            <td class="aside small-visible">
                <p><!-- test -->
                    <textarea>testset <!-- eurk -->dsqd</textarea>
                </p>
                <?php snippet('aside'); ?>
            </td>
        </tr>
    </table>

    <?php snippet('footer'); ?>


<?php mp_footer(); ?>
</body>
</html>
