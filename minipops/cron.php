<?php
/**
 *
 * @package CMS mini POPS
 */

/*
ignore_user_abort(true);

if ( !empty($_POST) || defined('DOING_AJAX') || defined('DOING_CRON') )
	die();


define('DOING_CRON', true);

// On definit le repertoire racine
define( 'ABSPATH', dirname(__FILE__) . '/' );

// On définit le coeur du CMS
define( 'INC', 'core' );

// On définit le coeur du CMS
require_once(ABSPATH . INC . '/mp_load.php');

die();

*/

define( 'ABSPATH', dirname(__FILE__) . '/' );

ignore_user_abort(true);
header('Transfer-Encoding:chunked');
ob_flush();
flush();
$start = microtime(true);
$i = 0;
// Use this function to echo anything to the browser.
function vPrint($data){
    if(strlen($data))
        echo dechex(strlen($data)), "\r\n", $data, "\r\n";
    ob_flush();
    flush();
}
// You MUST execute this function after you are done streaming information to the browser.
function endPacket(){
    echo "0\r\n\r\n";
    ob_flush();
    flush();
}
do{
    echo "0";
    ob_flush();
    flush();
    if(connection_aborted()){
        // This happens when connection is closed
        file_put_contents(ABSPATH. 'test.tmp', sprintf("Conn Closed\nTime spent with connection open: %01.5f sec\nLoop itterations: %s\n\n", microtime(true) - $start, $i), FILE_APPEND);
        endPacket();
        exit;
    }
    usleep(50000);
    vPrint("I get echo'ed every itteration (every .5 second)<br />\n");
}while($i++ < 200);
endPacket();
?>