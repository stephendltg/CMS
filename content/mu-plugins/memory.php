<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Affichage de la mémoire du CMS
 *
 * @package     cms
 * @subpackage  memory
 * @version 1
 */

add_action ( 'muplugins_loaded' , 'get_cms_memory' , 99 );


// Convertisseur ( http://php.net/manual/fr/function.memory-get-usage.php )
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}


function get_cms_memory() {

    return convert( memory_get_usage() );
}
