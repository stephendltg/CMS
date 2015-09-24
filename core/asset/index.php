<?php defined('ABSPATH') or die('No direct script access.');

var_dump(date('h', time() ));
//get('site-title');

?>

<!doctype html>
<html class="no-js" lang="<?php get('site-lang') ?>">
<head>

    <meta charset="<?php get('site-charset') ?>">
    <title><?php get('site-title') ?> | <?php get('title') ?></title>
    <meta name="description" content="<?php get('description') ?>" >
    <meta name="keywords" content="<?php get('keywords') ?>" >
    <meta name="author" content="<?php get('author') ?>">
    <meta name="robots" content="<?php get('robots') ?>">

    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">

    <?php mpops_head(); ?>

    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable = no">

</head>
<body role="document">

    <header id="header" role="banner">
        <figure class="tiny-w100 txtcenter pam">
            <img class="w150p" src="/IMG/lampe.svg" alt="La photo d'un artiste" title="La photo d'un artiste">
        </figure>
        <hgroup>
            <h1 id="title"><?php get('site-title') ?></h1>
            <h2 id="subtitle"><?php get('site-subtitle') ?></h2>
        </hgroup>

	</header>

    <nav role="navigation">
        <ul>
            <li class="inbl h4-like" ><a href="#biographie" title="Ma biographie">Biographie</a></li>
            <li class="inbl h4-like" ><a href="#album" title="Mes albums">Albums</a></li>
            <li class="inbl h4-like" ><a href="#evenements" title="Mes Evênements">Evenements</a></li>
        </ul>
    </nav>

	<main id="main" role="main">

        <article id="biographie">

            <header>
                <figure>
                    <img src="/IMG/guitare.svg" alt="Guitare" title="Guitare">
                </figure>
                <h1><?php get('title') ?></h1>
            </header>

            <section>
            <?php get('content') ?>

            </section>

            <footer>
                <?php get('author') ?>
            </footer>

        </article>

    </main>


	<footer id="footer" role="contentinfo">

        <section>

            <aside role="complementary"></aside>

            <div class="tiny-w100 w40" >
                Ab tamen possumus mentitum nam amet admodum hic legam quid. Quo pariatur
                fidelissimae, hic tamen tractavissent, commodo ex nisi quo nostrud do nisi
                appellat, appellat arbitrantur ne proident, multos ita ex nulla tempor ne o aute
                cernantur iudicem, eu fugiat despicationes. Officia tamen probant vidisse. Eram
                quo quibusdam te multos, probant ex illum doctrina. Sunt senserit ut
                eruditionem. Voluptate culpa cillum a dolor. Cernantur ubi quibusdam, est
                senserit graviterque, fabulas nisi commodo nescius, ubi legam sunt amet fabulas
                ab quae deserunt efflorescere iis an a lorem quis minim et an esse nam quis, ad
                aute consectetur.
            </div>

        </section>

        <p>
            <?php get('site-copyright') ?>
            <small>Développeur - intégrateur: <?php get('site-author') ?> | Graphiste : Fabienne Deletang | @2015 - Les contenus sont soumis aux droits d'auteurs</small>
        </p>

	</footer>

</body>
</html>
