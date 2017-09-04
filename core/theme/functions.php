<?php defined('ABSPATH') or die('No direct script access.');
/*
Theme Name: Rhythmicon
Theme URI:
Description: Theme by default
Version: 1.0
Author: Stephen Deletang
Author URI:
*/


/**
 * Implement the scss compiler.for this theme
 */
function mp_compass( $scss_name = 'style', $mode = 'compressed' ){

        // On charge la librairie
        require_once ( ABSPATH . INC . '/vendors/scss.inc.php' );

        $scss = new \Leafo\ScssPhp\Compiler();

        // On import le repertoire à la librairie
        $scss->setImportPaths(MP_TEMPLATE_DIR .'/assets/sass/');

        // mode
        switch ($mode) {
            case 'expanded':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Expanded');
                break;
            case 'compressed':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
                break;
            case 'compact':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Compact');
                break;
            case 'crunched':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Crunched');
                break;
            default:
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Nested');
                break;
        }

        // Compilation sass
        return $scss->compile('@import "'.$scss_name.'.scss";');
}


// Déclaration de la feuille de style uniquement si la compilation c'est bien passé.
//mp_transient_data('style_css', null);
add_inline_style('defaut-style', mp_transient_data('style_css', 'mp_compass', 0 ) );

// Déclaration script pour gérér les prefix naviguateur
add_inline_script('prefix-style', file_get_content(MP_TEMPLATE_DIR.'/assets/js/prefixfree.min.js') );