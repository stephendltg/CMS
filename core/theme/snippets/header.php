<!doctype html>
<html <?php html_attributes(); ?> class="no-js">
<head>
    <meta charset="<?php charset(); ?>">
    <title><?php title(); ?></title>
    <?php mpops_head(); ?>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable = no">
    <link rel="stylesheet" href="assets/css/knacss.css" media="all">

</head>
<body role="document">

    <header class="header" role="banner">
        <a class="logo" href="<?php blog('url'); ?>">
          <img src="assets/images/minipops.svg" alt="<?php blog('title'); ?>" />
        </a>
        <hgroup class="title">
            <h1 class="site-title"><?php blog('title'); ?></h1>
            <h2 class="site-subtitle"><?php blog('subtitle'); ?></h2>
        </hgroup>

    </header>

    <?php snippet('menu') ?>
