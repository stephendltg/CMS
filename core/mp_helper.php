<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction helper
 *
 *
 * @package cms mini POPS
 * @subpackage helper - extend function php
 * @version 1
 */

/***********************************************/
/*               Fonctions                      */
/***********************************************/

function detect_encoding( $string ) {
    $string       = (string) $string;
    if ( function_exists( 'mb_internal_encoding' ) ) {
      return strtolower ( mb_detect_encoding( $string , 'UTF-8, ISO-8859-1, windows-1251') );
    } else {
      foreach( array('utf-8', 'iso-8859-1', 'windows-1251') as $item )
        if( md5( iconv( $item , $item , $string ) ) == md5( $string ) ) return $item;
      return false;
    }
}

function encode_utf8( $string ){
    $string       = (string) $string;
    $encoding = detect_encoding( $string );
    if( is_same( $encoding , 'utf-8') ) return $string;
    return iconv( $encoding , 'utf-8' , $string );
}

function lang(){
    $lang = explode(',' , $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    return substr($lang[0],0,2);
}


function redirect( $location , $status = 302 ){

    $location = esc_url_raw($location);
    if ( !$location )  return false;

    header("Location: $location", true, $status);
    return true;
}


function arrayToObject($array){
  if( is_array($array) ){

    foreach($array as &$item)
        $item = arrayToObject($item);

    return (object) $array;
  }

  return $array;
}



/**
 * Convertir relative path en absolute url
 *
 * echo rel2abs("/dir/page.html"," http://www.example.com/");
 * // Output: http://www.example.com/dir/page.html
 *
 * echo rel2abs("/dir/page.html"," http://www.example.com/dir1/page2.html");
 * // Output: http://www.example.com/dir/page.html
 *
 * echo rel2abs("dir/page.html"," http://www.example.com/dir1/page2.html");
 * // Output: http://www.example.com/dir1/dir/page.html
 *
 * echo rel2abs("../dir/page.html"," http://www.example.com/dir1/dir3/page.html");
 * // Output: http://www.example.com/dir1/dir/page.html
 *
 *
 * @param string   $rel         path relative
 * @param string   $base        url base
 * @return string  url
 */

function rel2abs( $rel, $base = null ) {

    if($base === null ) $base = HOME.'/';

    if ( strpos( $rel,'//' ) === 0 )  return $scheme . ':' . $rel;

    /* return if already absolute URL */
    if ( parse_url( $rel, PHP_URL_SCHEME ) != '' )  return $rel;

    /* queries and anchors */
     if ( $rel[0] == '#' || $rel[0] == '?' )  return $base . $rel;

    /* parse base URL and convert to local variables: $scheme, $host, $path */
    extract( parse_url( $base ) );

    /* remove non-directory element from path */
    $path = preg_replace( '#/[^/]*$#', '', $path );

    /* destroy path if relative url points to root */
    if ( $rel[0] == '/' ) $path = '';

    /* dirty absolute URL // with port number if exists */
    if (parse_url($base, PHP_URL_PORT) != '')
        $abs = "$host:".parse_url($base, PHP_URL_PORT)."$path/$rel";
    else
        $abs = "$host$path/$rel";

    /* replace '//' or '/./' or '/foo/../' with '/' */
    $re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
    for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}

    /* absolute URL is ready! */
    return $scheme . '://' . $abs;
}


/***********************************************/
/*                Fonctions memory             */
/***********************************************/

/**
 * convertisseur pour mémoire
 * http://php.net/manual/fr/function.memory-get-usage.php
 * Argument $size ( valeur en octet )
 * @return string
 */
function convert($size)
{
    $unit=array('b','kb','mb','gb','tb','pb');
    return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

/**
 * get_cms_peak_memory
 * Retourne la mémoire maximale alloué par php
* Argument $real_usage = true pour obtenir la mémoire allouée par le système
 * @return string
 */
function get_cms_peak_memory( $real_usage = false ) {
    return convert( memory_get_peak_usage( $real_usage ) );
}

/**
 * get_cms_memory
 * Retourne la mémoire alloué par php
 * Argument $real_usage = true pour obtenir la mémoire allouée par le système
 * @return string
 */
function get_cms_memory( $real_usage = false ) {
    return convert( memory_get_usage( $real_usage ) );
}

/**
 * php_limit_memory
 * Retourne la mémoire limite alloué par php
 * Argument $force_limit_mem    Change la valeur de la memoire (mini 16M)
 * @return string
 */
function get_limit_memory( $force_limit_mem = '' ) {

    if( is_integer($force_limit_mem) && is_sup($force_limit_mem, 16) )
        @ini_set('memory_limit', $force_limit_mem.'M');

    return ini_get('memory_limit');
}

/**
 * php_upload_max_size
 * Retourne la mémoire alloué par php pour la taille des fichiers uploader
 * htaccess: php_value upload_max_filesize 4M ( upload_max_filesize doit etre inférieur à post_max_size si fichiers multiples )
 * @return string
 */
function get_upload_memory() {
    return ini_get('upload_max_filesize');
}

/**
 * php_post_max_size
 * Retourne la mémoire alloué par php pour les variable POST
 * htaccess: php_value post_max_size 10M ( memory_limit doit etre supêrieur à post_max_size )
 * @return string
 */
function get_post_memory() {
    return ini_get('post_max_size');
}

/**
 * php_time_execution
 * Retourne le temps max d'execution d'un script php en seconde
 * Argument force_max_time_execution    change la valeur du temps max d'execution d'un script (mini:30s)
 * @return string
 */
function get_max_time_execution( $force_max_time_execution = '' ) {

    if( is_integer($force_max_time_execution) && $force_max_time_execution > 30 )
        @ini_set('max_execution_time', $force_max_time_execution);

    return ini_get('max_execution_time');
}


/***********************************************/
/*                Fonctions file               */
/***********************************************/

/**
* Supprimer un répertoire et son contenu
* @param  string    $dir     Chemin absolu du répertoire
*/
function rmdir_recursive( $dir ) {
    foreach( glob($dir) as $file ){
        if( is_dir($file) ){ rmdir_recursive("$file/*"); rmdir($file); }
        else { unlink($file); }
    }
}


/***********************************************/
/*                Fonctions salt               */
/***********************************************/

/**
 * Génère un salt aléatoire
 * @return string
 */
function random_salt( $length = 8 ) {
    $length = (int) $length;
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $salt = substr( str_shuffle( $chars ), 0, $length );
    return $salt;
}
