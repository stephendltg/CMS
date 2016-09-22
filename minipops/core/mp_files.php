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
    return @file($file);
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
