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
/*               Lang                          */
/***********************************************/

/**
* Language du serveur
*/
function lang(){
    $lang = explode(',' , $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    return substr($lang[0],0,2);
}


/***********************************************/
/*               Redirect                      */
/***********************************************/

/**
* Redirection vers url
* @param $location      url
* @param $status        etat de la redirection 301: Move Permanently, 302: Found, 303: See Other, 307: Temporary Redirect
*/
function redirect( $location , $status = 302 ){

    $location = esc_url_raw($location);
    if ( !$location )  return false;

    header("Location: $location", true, $status);
    exit();
}


/***********************************************/
/*               Array                         */
/***********************************************/


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
* on filtre les valeurs ( null, '', false ) d'un tableau
* @param $array
*/
function filter_me($array) { 

    foreach ($array as &$value)
        if (is_array($value))   $value = filter_me($value); 
    return array_filter($array); 
} 


/**
* on convertit les booleans en chaine
* @param $array
*/
function encode_bool($something){

    if( !is_bool($something) )
        return $something;

    if( $something === true )   
        return "true";
    else
        return "false";
}


/**
* on decode les boolean dans une chaine
* @param $array
*/
function decode_bool($something){

    if( !is_string($something) )
        return $something;

    if( in_array($something, array('y','Y','yes','Yes','YES','true','True','TRUE','on','On','ON') ) )
        return true;
    elseif( in_array($something, array('n','N','no','No','NO','false','False','FALSE','off','Off','OFF') ) )
        return false;

    return $something;
}


/***********************************************/
/*               path                          */
/***********************************************/

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

    if($base === null ) $base = guess_url();

    if (substr($base, -1) != '/')
        $base .= '/';

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


/**
* Créer lien relatif à partir d'un lien
* @param   string      $link
* @return  string
*/
function abs2rel( $link ) {
    return preg_replace( '|^(https?:)?//[^/]+(/.*)|i', '$2', $link );
}



/***********************************************/
/*               Cache data                    */
/***********************************************/

/**
 * Enregistrer, récupérer ou supprimer une donnée statique.
 * Get:   Mettre juste la clé recherche en parametre
 * Set:   Mettre un second parametres avec la valeur de la clé
 * Delete: Mettre la valeur : null en second paramètres pour supprimer la clé
 *
 * @param (string) $key clé d'identification. 
 *
 * @return (mixed) La valeur enrégistrer ou null.
 */
function mp_cache_data( $key ) {

    static $data = array();

    $func_get_args = func_get_args();

    if ( array_key_exists( 1, $func_get_args ) ) {

        if ( null === $func_get_args[1] )
            unset( $data[ $key ] );
        else
            $data[ $key ] = $func_get_args[1];
    }

    return isset( $data[ $key ] ) ? $data[ $key ] : null;
}


/***********************************************/
/*               Encoding                      */
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

    if( is_same( $encoding , 'utf-8') ) 
        return $string;

    return iconv( $encoding , 'utf-8' , $string );
}


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
* Encode in Base32 based
* Requires 20% more space than base64 
* Use padding false when encoding for urls
*
* @return base32 encoded string
**/
function base32_encode($input, $padding = true) {

    if(empty($input)) return "";

    $map = array(
      'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
      'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
      'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
      'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
      '='  // padding character
    );
      
    $input = str_split($input);
    $binaryString = "";
      
    for($i = 0; $i < count($input); $i++) {
        $binaryString .= str_pad(base_convert(ord($input[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
    }
      
    $fiveBitBinaryArray = str_split($binaryString, 5);
    $base32 = "";
    $i=0;
      
    while($i < count($fiveBitBinaryArray)) {    
        $base32 .= $map[base_convert(str_pad($fiveBitBinaryArray[$i], 5,'0'), 2, 10)];
        $i++;
    }
      
    if($padding && ($x = strlen($binaryString) % 40) != 0) {
        if($x == 8) $base32 .= str_repeat($map[32], 6);
        else if($x == 16) $base32 .= str_repeat($map[32], 4);
        else if($x == 24) $base32 .= str_repeat($map[32], 3);
        else if($x == 32) $base32 .= $map[32];
    }
      
    return $base32;
}
    

/**
* Decode in Base32 based
*
* @return base32 decode string
**/
function base32_decode($input) {

    if(empty($input)) return;

    $map = array(
      'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
      'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
      'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
      'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
      '='  // padding character
    );
    
    $flippedMap = array(
      'A'=>'0', 'B'=>'1', 'C'=>'2', 'D'=>'3', 'E'=>'4', 'F'=>'5', 'G'=>'6', 'H'=>'7',
      'I'=>'8', 'J'=>'9', 'K'=>'10', 'L'=>'11', 'M'=>'12', 'N'=>'13', 'O'=>'14', 'P'=>'15',
      'Q'=>'16', 'R'=>'17', 'S'=>'18', 'T'=>'19', 'U'=>'20', 'V'=>'21', 'W'=>'22', 'X'=>'23',
      'Y'=>'24', 'Z'=>'25', '2'=>'26', '3'=>'27', '4'=>'28', '5'=>'29', '6'=>'30', '7'=>'31'
    );

      
    $paddingCharCount = substr_count($input, $map[32]);
    $allowedValues = array(6,4,3,1,0);
      
    if(!in_array($paddingCharCount, $allowedValues)) return false;
      
    for($i=0; $i<4; $i++){ 
        if($paddingCharCount == $allowedValues[$i] && 
            substr($input, -($allowedValues[$i])) != str_repeat($map[32], $allowedValues[$i])) return false;
    }
      
    $input = str_replace('=','', $input);
    $input = str_split($input);
    $binaryString = "";
      
    for($i=0; $i < count($input); $i = $i+8) {
    
        $x = "";
        
        if(!in_array($input[$i], $map)) return false;
        
        for($j=0; $j < 8; $j++) {
            $x .= str_pad(base_convert(@$flippedMap[@$input[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
        }
        
        $eightBits = str_split($x, 8);
        
        for($z = 0; $z < count($eightBits); $z++) {
            $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
        }

    }
      
    return $binaryString;
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
    
    } else 
        return substr( $text , 0 , $length );
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


/***********************************************/
/*                  js/css Minify              */
/***********************************************/
// Minifie js et css simplement
function mp_easy_minify( $str, $comments = true ){

    $str = apply_filters('pre_mp_easy_minify', $str);

    // On enlève les commentaires
    if($comments)
        $str = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str );

    /* remove tabs, spaces, newlines, etc. */
    $str = str_replace(array("\r\n", "\r", "\n", "\t"), '', $str);
    while ( stristr($str, '  ') )
        $str = str_replace('  ', ' ', $str);

    return apply_filters('mp_easy_minify', $str);
}


/***********************************************/
/*                  html Minify                */
/***********************************************/

function mp_minify_html($html){

    $html = apply_filters('pre_mp_minify_html', $html);

    // On recherche tous les tag ainsi que leur contenu ( tag => <p style="color:red">, text => "mon texte", tag => </p> )
    preg_match_all( '/<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si', $html, $matches, PREG_SET_ORDER );
    // On init le toogle tag
    $raw_tag = false;
    // On init le résultat
    $html    = '';

    // On boucle sur tous les éléments tag ou text trouvés
    foreach( $matches as $token ){

        // On normalise la variable tag
        $tag     = isset( $token['tag'] ) ? strtolower( $token['tag'] ) : null;
        // On associe le contenu
        $content = $token[0];

        if( is_null($tag) ){
            // On supprime les commentaires seulement s'il ne sont pas dans un textaera
            if($raw_tag != 'textarea')
                $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
        
        } else {

            // On minifie le contenu seulement s'il n'appartient pas à pre ou textaera
            if( $tag == 'pre' || $tag == 'textarea' ){
                $raw_tag = $tag;
            } else if( $tag == '/pre' || $tag == '/textarea' ){
                $raw_tag = false;
            } else {
                if ( $raw_tag ){
                    $strip = false;
                } else {
                    $strip   = true;
                    $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                    $content = str_replace(' />', '/>', $content);
                }
            }
        }

        // On supprimer les espaces inutiles
        if ($strip)
            $content = mp_easy_minify($content, false);

        $html .= $content;
    }

    do_action('mp_minify_html');

    return apply_filters('mp_minify_html', $html);
}