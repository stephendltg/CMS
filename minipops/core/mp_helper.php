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
/*               Fonctions                     */
/***********************************************/

/**
* Detecte le type d'encodage d'une chaine
* @param $string
*/
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

/**
* Encode une chaine en utf-8
* @param $string
*/
function encode_utf8( $string ){
    $string       = (string) $string;
    $encoding = detect_encoding( $string );
    if( is_same( $encoding , 'utf-8') ) return $string;
    return iconv( $encoding , 'utf-8' , $string );
}

/**
* Language du serveur
*/
function lang(){
    $lang = explode(',' , $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    return substr($lang[0],0,2);
}

/**
* Redirection vers url
* @param $location      url
* @param $status        etat de la redirection
*/
function redirect( $location , $status = 302 ){

    $location = esc_url_raw($location);
    if ( !$location )  return false;

    header("Location: $location", true, $status);
    return true;
}

/**
* Convertit un tableau en objet
* @param $array
*/
function arrayToObject($array){
  if( is_array($array) ){
    foreach($array as &$item)
        $item = arrayToObject($item);
    return (object) $array;
  }
  return $array;
}


/**
* PArse arguments
* @param $array
*/
function parse_args( $args, $defaults = '' ) {

    if ( is_object( $args ) )
        $r = get_object_vars( $args );
    elseif ( is_array( $args ) )
        $r =& $args;
    else{
        parse_str( $args, $r );
        if ( get_magic_quotes_gpc() )
            $r = map_deep( $r, 'stripslashes_str' );
    }
    if ( is_array( $defaults ) )
        return array_merge( $defaults, $r );
    return $r;
}



/**
* Applique une fonction callback de façon recursive
* @param $array
*/
function map_deep( $value, $callback ) {

    if ( is_array( $value ) ) {
        foreach ( $value as $index => $item ) {
            $value[ $index ] = map_deep( $item, $callback );
        }
    } elseif ( is_object( $value ) ) {
        $object_vars = get_object_vars( $value );
        foreach ( $object_vars as $property_name => $property_value ) {
            $value->$property_name = map_deep( $property_value, $callback );
        }
    } else {
        $value = call_user_func( $callback, $value );
    }

    return $value;
}

/**
* Ajoute des slash dans une chaine
* @param $string     chaine
*/
function backslashit( $string ) {
    if ( isset( $string[0] ) && $string[0] >= '0' && $string[0] <= '9' )
        $string = '\\\\' . $string;
    return addcslashes( $string, 'A..Za..z' );
}


/**
* Supprime les antislashs d'une chaîne et uniquement
* @param $string     chaine
*/
function stripslashes_str( $value ) {
    return is_string( $value ) ? stripslashes( $value ) : $value;
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
function convert($size){
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
function get_max_time_execution( $ForceMaxTimeExec = '' ) {
    if( is_integer($ForceMaxTimeExec) && $ForceMaxTimeExec > 30 )
        @ini_set('max_execution_time', $ForceMaxTimeExec);
    return ini_get('max_execution_time');
}



/***********************************************/
/*                Fonctions file               */
/***********************************************/

/**
* Function lecture d'un fichier
* @param  string    $file     Chemin absolu du fichier
* @return sring
*/
function file_get_content($file) {
    return @file_get_contents($file);
}

/**
* Function lecture d'un fichier stoker dans un tableau
* @param  string    $file     Chemin absolu du fichier
* @return array
*/
function file_get_content_array($file) {
    return @file($file);
}


/**
* Function enregistrement d'un fichier
* @param  string    $file     Chemin absolu du fichier
* @return array
*/
function file_put_content($file, $contents) {

    $fp = @fopen( $file, 'wb' );
    if ( ! $fp )
        return false;

    $contents = encode_utf8($contents);
  
    $data_length = strlen( $contents );
  
    $bytes_written = fwrite( $fp, $contents );
  
    fclose( $fp );
  
    if ( $data_length !== $bytes_written )
        return false;
  
    @chmod( $file, 0644 );
  
    return true;
}


/**
* Function remplace le contenu d'un fichier
* @param  string    $file     Chemin absolu du fichier
* @return array
*/
function file_replace_content($file, $old_content, $new_content) {
    
    if ( ! file_exists( $file ) )
        return false;

    $file_content  = get_contents( $file );

    $new_content  = preg_replace( $old_content, $new_content, $file_content );
    $replaced     = null !== $new_content && $new_content !== $file_content;
    $put_contents = put_contents( $file, $new_content );

    return $put_contents && $replaced;
}

/**
* Modifie un contenu entre deux marker
* @param  string    $file     Chemin absolu du fichier
* @return array
*/
function file_marker_contents( $file, $new_content = '', $args = array() ) {


    $args = parse_args( $args, array(
        'marker'   => '',
        'put'      => 'prepend',
        'text'     => '',
        'keep_old' => false,
    ));


    $file_content  = '';
    $comment_char  = basename( $file ) !== 'php.ini' ? '#' : ';';

    // Get the whole content of file and remove old marker content.
    if ( file_exists( $file ) ) {

        $pattern      = '/' . $comment_char . ' BEGIN MiniPops ' . $args['marker'] . '(.*)' . $comment_char . ' END MiniPops\s*?/isU';
        $file_content = file_get_contents( $file );
        if ( $args['keep_old'] )
            preg_match( $pattern, $file_content, $keep_old );

        $file_content = preg_replace( $pattern, '', $file_content );
    }

    if ( ! empty( $new_content ) ) {

        $content  = $comment_char . ' BEGIN MiniPops ' . $args['marker'] . PHP_EOL;

        if ( $args['keep_old'] && isset( $keep_old[1] ) )
            $content .= trim( $keep_old[1] ) . "\n";

        $content .= trim( $new_content ) . PHP_EOL;
        $content .= $comment_char . ' END MiniPops' . PHP_EOL . PHP_EOL;

        if ( '' !== $args['text'] && strpos( $file_content, $args['text'] ) !== false ) {

            if ( 'append' === $args['put'] )
                $content = str_replace( $args['text'], $args['text'] . PHP_EOL . $content, $file_content );
            elseif ( 'prepend' === $args['put'] )
                $content = str_replace( $args['text'], $content . PHP_EOL . $args['text'], $file_content );
            
        } else {

            if ( 'append' === $args['put'] )
                $content = $file_content . PHP_EOL . $content;
            elseif ( 'prepend' === $args['put'] )
                $content = $content . $file_content;
        }

        $file_content = $content;
    }

    return file_put_content( $file, $file_content );
}



/**
* Supprimer un répertoire et son contenu
* @param  string    $dir     Chemin absolu du répertoire
*/
function rrmdir( $dir ) {
    foreach( glob($dir) as $file ){
        if( is_dir($file) ){ rrmdir("$file/*"); rmdir($file); }
        else unlink($file);
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


/***********************************************/
/*          Functions parser                   */
/***********************************************/

/**
 * Encode en base64 pour un usage embarque en data uri(css,html) si fichier sinon encodage seulement
 * @param  $data : $file(chemin absolu) ou $string
 * @return string
 */
function datauri_encode( $data ) {
    $data = (string) $data;
    if ( file_exists( $data ) && !is_dir( $data ) ){
        $mime_type = mime_content_type( $data );
        return 'data:' . $mime_type . ';base64,' . base64_encode( file_get_contents( $data ) );
    }
    return 'data:' . $mime_type . ';base64,' . base64_encode( $data );
}

/**
* Extrait d'une chaine
* @param  $text     chaine à extraire
* @param  $length   longueur de l'extrait
* @param  $mode     mode characère ou mot
*/
function excerpt( $text , $length = 140 , $mode = 'chars' ) {

    $text   = (string) $text;
    $length = (int) $length;
    $mode   = (string) $mode;

    $text = strip_all_tags($text);

    if( is_same( strtolower($mode) , 'words' ) ){
        if( str_word_count($text , 0) > $length ) {
            $words = str_word_count($text, 2);
            $pos   = array_keys($words);
            $text  = substr( $text , 0 , $pos[$length]) . '...';
        }
        return $text;
    }
    else return substr( $text , 0 , $length );
}


/**
* Parse une chaine markdown en html
* @param  $markdown     chaine à parser
*/
function parse_markdown( $markdown ){

    $markdown = (string) $markdown;

    # commentaires
    $markdown = str_replace(array('&#039;&#039;', "``"), array('&#8220;', '&#8221;'), $markdown);

    // On parse markdown
    $Extra = new Parsedown();
    $markdown = $Extra->text( $markdown );

    // On nettoie toutes les urls dans href
    $clean_all_url = function($array){ return 'href="'.esc_url_raw($array[2]).'"'; };
    $markdown = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $markdown );

    // On remet les chevrons pour la balise code
    $markdown = str_replace( '&amp;', '&' , $markdown );

    # Traits de séparation
    $markdown = str_replace(array('---', '--'), array('&#8212;', '&#8211;'), $markdown);

    # trois petits points et puis lalala
    $markdown = str_replace('...', '&#8230;', $markdown);

    return $markdown;
}

/**
* Parse une chaine text en html
* @param  $text     chaine à parser
*/
function parse_text( $text ){

    $text = (string) $text;

    # commentaires
    $text = str_replace(array('&#039;&#039;', "``"), array('&#8220;', '&#8221;'), $text);

    // On nettoie toutes les urls lie à href
    $clean_all_url = function($array){ return 'href="'.esc_url_raw($array[2]).'"'; };
    $text = preg_replace_callback( '/href=([\'"])(.+?)([\'"])/i' , $clean_all_url , $text );

    # Traits de séparation
    $text = str_replace(array('---', '--'), array('&#8212;', '&#8211;'), $text);

    # trois petits points et puis lalala
    $text = str_replace('...', '&#8230;', $text);

    return $text;
}


/**
* Parse un fichier au format yaml dans un tableau
* @param  string    $path     Chemin absolu du fichier
* @return array
*/
function file_get_page( $path ){

    $path = (string) $path;

    $yaml = array();

    // function anonyme pour décoder les valeurs
    $decode_value = function ( $value ){

        $value = trim($value);
        $value = json_decode($value, true) ?: $value;
        if( $value === 'false' )      $value = false;
        elseif( $value === '~' )      $value = null;
        elseif( $value === 'null' )   $value = null;
        elseif( is_array($value) )    $value = serialize($value);
        return $value;
    };

    // On ouvre le fichier de la page, on l'encode en utf8 et on nettoie
    $file = esc_attr( encode_utf8( file_get_contents( $path ) ) );

    if( preg_match_all('/^[\s]*(\w*?)[ \t]*:[\s]*(.*?)[\s]*[-]{4}/mis', $file , $match ) ){
        $match[1] = array_map( 'strtolower', $match[1] );
        $yaml = array_combine($match[1], array_map($decode_value, $match[2]) );
        unset($match);
    }

    return $yaml;
}


/**
* Parse un tableau dans un fichier yaml
* @param  string    $path     Chemin absolu du fichier
* @return array
*/
function file_put_page( $path, $array ){

    $path = (string) $path;

    $text = '# generate by mini-pops'. PHP_EOL;

    foreach( $array as $field => $value ){

        if(     $value === true  )   $value = "true";
        elseif( $value === false )   $value = "false";
        elseif( $value === null  )   $value = "~";

        if( !empty($value) )
            $text .= PHP_EOL . sanitize_key($field) . ': ' . $value . PHP_EOL . PHP_EOL .'----' . PHP_EOL;
    }

    if( file_exists($path) && !is_writable($path) )     return false;
    if( !file_put_contents( $path , $text , LOCK_EX ) ) return false;
    @chmod( $path , 0644 );
    return true;
}
