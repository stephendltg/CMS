<?php
/**
 *
 * @package CMS
 */

/** On definit le repertoire racine  */
define( 'ABSPATH', dirname(__FILE__) . '/' );

/** On definit les parametres de retour d'erreur de php  */
error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING | E_RECOVERABLE_ERROR );

/** On verifie que le fichier config existe  */
if ( file_exists( ABSPATH . 'config.php') ) {

	require_once( ABSPATH . 'config.php' );

} else {

    //On tente de reconstruire le fichier config en passant le fichier config-sample.php
    if ( file_exists('core/setup-config.php') ) {

        $host  = $_SERVER['HTTP_HOST'];
        $uri   = rtrim( dirname( $_SERVER['PHP_SELF'] ) , '/\\' );
        header("Location: http://$host$uri/core/setup-config.php");
        die();

    }
    else {
        $protocol = $_SERVER["SERVER_PROTOCOL"];
        if ( 'HTTP/1.1' != $protocol && 'HTTP/1.0' != $protocol )
            $protocol = 'HTTP/1.0';
        header( "$protocol 503 Service Unavailable", true, 503 );
        header( 'Content-Type: text/html; charset=utf-8' );
?>
        <!DOCTYPE html>
        <html>
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>503</title>
        <style>
            body {
                background-color: #1b8caf;
                font-family: Helvetica, Arial, sans-serif;
            }
            h1, div {
                color: #fff;
                margin: auto;
                max-width: 400px;
                text-align: center;
            }
            h1 {
                font-size: 4em;
                padding-top: 15%;
                padding-bottom: 40px;
                text-transform: uppercase;
            }
            h2 {
                font-size: 1.2em;
            }
            p {
                padding: 5px;
            }
            div {
                background-color: #242424;
                border-radius: 5px;
                padding: 20px 0;
            }

        </style>
        </head>
        <body>
            <h1>503</h1>
            <div>
                <h2>HTTP Error 503: Service indisponible</h2>
                <p>La configuration de votre site est absente !</p>
            </div>
        </body>
        </html>
<?php
	   die();
    }
}
