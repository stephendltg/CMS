<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction YAML
 *
 *
 * @package cms mini POPS
 * @subpackage yaml
 * @version 1
 */


/***********************************************/
/*                Fonctions yaml               */
/***********************************************/


function walk_recursive_action ( array $array, callable $callback, $action ) {

    foreach ($array as $k => $v) {
        if (is_array($v)) {
            $array[$k] = walk_recursive_action($v, $callback);
        } else {
            if ($callback($v, $k)) {
                //unset($array[$k]);
                call_user_func_array( $action, array($v, $k) );
            }
        }
    }
    return $array;
}

/**
* Parse un fichier au format yaml dans un tableau
* @param  string    $path     Chemin absolu du fichier
* @return array
*/
function file_get_yaml( $path , $recursive = false ){

    $path = (string) $path;
    $recursive = (bool) $recursive;

    $yaml = array();

    // function anonyme
    $filter_by_type = function ( $value ){

        $value = trim( $value );
        if( is_same($value, 'false') )              return false;
        elseif( is_same($value, 'true') )           return true;
        elseif( is_in($value, array('null','~') ) ) return null;
        elseif( is_intgr($value) )                  return (int) $value;
        elseif( is_numeric($value) )                return (float) $value;
        elseif( is_same(stripos($value, '//'), 0 ) ) return null; // it's a comment

        return $value;

    };


    // On ouvre le fichier de la page, on l'encode en utf8 et on nettoie
    $file = esc_attr( encode_utf8( file_get_contents( $path ) ) );

    if( preg_match_all('/^[\s]*(\w*?)[ \t]*:[\s]*(.*?)[\s]*[-]{4}/mis', $file , $match ) ){

        $match[1] = array_map( 'strtolower', $match[1] );
        $yaml = array_combine($match[1], array_map($filter_by_type, $match[2]) );
        unset($match);

        if( $recursive ){

            foreach ($yaml as $field => $value) {

                $key_parent = '';

                if( preg_match_all('/^([ \t]*\w+)[ \t]*:[ \t]*(.*)/mi', $value , $match ) ){

                    foreach ($match[1] as $k => $v ) {
                        // Si 4 espace alors c'est une tabulation
                        $v = str_replace('    ', "\t" , $v);
                        // On determine le nombre de tabulation
                        $c = strripos($v, "\t") - stripos($v, "\t");
                        $y = stripos($v,'-');

                        if( $c === 0 && !$match[2][$k] ) $key_parent = trim($v);

                        if( $c === 1 && $key_parent ) $table[$key_parent][trim($v)] =  $filter_by_type( $match[2][$k] );

                        if( $c === 0 ) $table[trim($v)] = $filter_by_type( $match[2][$k] );
                    }

                } else $table = $filter_by_type( $value );

            $yaml[$field] = $table;
            unset($table);

            }
        }
    }

    return $yaml;
}




/**
* Parse un tableau dans un fichier yaml
* @param  string    $path     Chemin absolu du fichier
* @return array
*/
function file_put_yaml( $path , $array , $recursive = false ){

    $path      = (string) $path;
    $recursive = (bool) $recursive;

    $text = '# generate by mini-pops'. PHP_EOL;

    if( !$recursive ){

        foreach( $array as $field => $value )
            if( !empty($value) )
                $text .= PHP_EOL . strtolower($field) . ': ' . $value . PHP_EOL . PHP_EOL .'----' . PHP_EOL;

    } else {

        foreach ( $array as $field => $value) {

            if( !is_array($value) ){

                $text .= PHP_EOL . strtolower($field) . ': ' . $value . PHP_EOL . PHP_EOL .'----' . PHP_EOL;

            } else {

                $text .= PHP_EOL . strtolower($field) . ':'. PHP_EOL;

                foreach ( $value as $k => $v ) {

                    if(is_array($v)){

                        $text .= PHP_EOL .'    '. strtolower($k) . ':'. PHP_EOL;

                        foreach ($v as $i => $j) {

                            if( is_array($j) ) $j = null;
                            $text .= PHP_EOL .'        '. strtolower($i) . ': ' . $j . PHP_EOL;

                        }

                    } else {
                        $text .= PHP_EOL .'    '. strtolower($k) . ': ' . $v . PHP_EOL;
                    }
                }

                $text .= PHP_EOL .'----' . PHP_EOL;
            }
        }
    }

    if( file_exists($path) && !is_writable($path) ) return false;
    file_put_contents( $path , $text , LOCK_EX );
    @chmod( $path , 0644 );
    return true;
}
