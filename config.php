<?php

/**
 * La configuration de votre cms.
 *
 * @package CMS mini POPS
 * @subpackage config
 * @version 1
 */

define ('DEBUG' , true );




// On definit les paramètres du site

define ('JSONDB', 'storage');


/** Definit l'url du site. */
//define ('HOME', 'http://localhost/www/miniPOPS');

/** Definit la zone horaire. */
define ('TIME_ZONE', 'Europe/Brussels');

/** Definit l'email de l'admin. */
define ('ADMIN_EMAIL', 's.deletang@laposte.net');

/** Definit titre du site. */
define ('SITE_TITLE', 'Stephen Deletang');

/** Definit la description du site. */
define ('SITE_DESCRIPTION', 'Un site personnel');

/** Definit les mots clés du site. */
define ('SITE_KEYWORDS', 'portfolio, projet');


/** Ne pas toucher et ne rien ajouter */
require_once(ABSPATH . 'load.php');
