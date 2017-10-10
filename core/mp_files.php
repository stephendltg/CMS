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
    return @file($file, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
}


/**
* Function enregistrement d'un fichier
* @param  string    $file     Chemin absolu du fichier
* @return 
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
    
    if ( ! file_exists($file) )
        return false;

    $file_content  = file_get_content( $file );
    $new_content   = preg_replace( $old_content, $new_content, $file_content );
    $replaced      = null !== $new_content && $new_content !== $file_content;
    $put_contents  = file_put_content( $file, $new_content );

    return $put_contents && $replaced;
}

/**
* Modifie un contenu entre deux marker ( pour htaccess et php.ini)
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

        if( is_dir($file) ){ 

            rrmdir("$file/*");
            rmdir($file);

        } else unlink($file);
    }
}


/***********************************************/
/*               Cache file                 */
/***********************************************/

/**
 * Enregistrer, récupérer ou supprimer une donnée cache.
 * Get:   Mettre juste la clé recherche en parametre
 * Set:   Mettre un second parametres avec la valeur de la clé
 * Delete: Mettre la valeur : null en second paramètres pour supprimer la clé
 *
 * @param (string) $key clé d'identification. 
 *
 * @return (mixed) La valeur enrégistrer ou null.
 */
function mp_cache_file( $key ) {

    /* Condition pour purger le cache */
    if( is_null($key) )
        return rrmdir(MP_CACHE_DIR . '/*');

    /* Valide $key */
    $key = sanitize_file_name($key);
    if(strlen($key) == 0 )  return;

    $key = base32_encode($key);

    $func_get_args = func_get_args();

    if ( array_key_exists( 1, $func_get_args ) ) {

        if ( null === $func_get_args[1] ){

            unlink( MP_CACHE_DIR . '/' . $key );
            return null;

        } else {

            $cache = array( 'time' => 0 , 'value' => $func_get_args[1] );

            if( array_key_exists( 2, $func_get_args )  )
                $cache['time'] = time() + (int) $func_get_args[2] * MINUTE_IN_SECONDS;

            if( @file_put_contents( MP_CACHE_DIR .'/'. $key , base32_encode(serialize($cache)), LOCK_EX ) )
                return $func_get_args[1];
        }
    }


    if( file_exists(MP_CACHE_DIR . '/' . $key) ){

        if( ! $cache = unserialize( base32_decode( file_get_content( MP_CACHE_DIR . '/' . $key ) ) ) )
            return;

        if( $cache['time'] == 0 || $cache['time'] > time() )
            return $cache['value'];
        else
            unlink( MP_CACHE_DIR . '/' . $key );
    }

    return;

}


/**
* Arborescence d'un répertoire
* @param  string    $dir     Chemin absolu du répertoire
*/
function scan( $dir, $infos = false ){

    $files = array();

    if( file_exists($dir) ){

        foreach( scandir($dir) as $f ) {
            
            if( !$f || $f[0] == '.'  )
                continue;
            
            if( is_dir($dir . '/' . $f) ) {

                if( $infos )
                    $files[$f] = scan($dir . '/' . $f, $infos );
                else {
                    $files[] = array(
                        "name"  => $f,
                        "type"  => "folder",
                        "path"  => realpath( $dir . '/' . $f ),
                        "items" => scan($dir . '/' . $f )
                    );
                }

            } else {
                
                $files[] = ($infos) ? $f : array(
                    "name" => $f,
                    "type" => "file",
                    "path" => realpath( $dir . '/' . $f ),
                    "ext"  => pathinfo( $dir . '/' . $f , PATHINFO_EXTENSION), //PATHINFO_DIRNAME | PATHINFO_BASENAME | PATHINFO_EXTENSION | PATHINFO_FILENAME
                    "size" => filesize($dir . '/' . $f),
                    "time" => filemtime($dir . '/' . $f)
                );
            }
        }
    }
    return $files;
}


/**
* Parse un fichier au format yaml dans un tableau
* @param  string    $path     Chemin absolu du fichier
* @return array
*/
function file_get_page( $path ){

    $path = (string) $path;

    $yaml = array();

    // On ouvre le fichier de la page, on l'encode en utf8 et on nettoie
    $file = esc_attr( encode_utf8( file_get_content($path) ) );

    if( preg_match_all('/^[\s]*(\w*?)[ \t]*:[\s]*(.*?)[\s]*[-]{4}/mis', $file , $match ) ){
        $match[1] = array_map( 'strtolower', $match[1] );
        $yaml     = array_combine($match[1], array_map('decode_bool', $match[2]) );
        $yaml     = array_filter($yaml);
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

    // On encode les bool et on supprime les valeurs null et vide
    $array = array_filter( array_map( 'encode_bool', $array) );

    foreach( $array as $field => $value )
        $text .= PHP_EOL . sanitize_key($field) . ': ' . $value . PHP_EOL . PHP_EOL .'----' . PHP_EOL;
    

    return file_put_content($path , $text);
}
