<?php
/**
 * Front to the Minipops application.
 *
 * @package CMS mini POPS
 */


/** On definit le repertoire racine  */
define( 'ABSPATH', dirname(__FILE__) . '/' );

/** On charge le coeur de minipops  */
require_once( ABSPATH . 'core/mp_load.php');

/** On charge la gestion des templates */
require_once( ABSPATH . 'core/mp_template_load.php');