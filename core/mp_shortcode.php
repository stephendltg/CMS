<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage shortcode
 * @version 1
 */



/**
* Adds a new shortcode.
*
* @param string   $tag      Shortcode tag to be searched in post content.
* @param callable $callback The callback function to run when the shortcode is found.
*                           Every shortcode callback is passed three parameters by default,
*                           including an array of attributes (`$atts`), the shortcode content
*                           or null if not set (`$content`), and finally the shortcode tag
*                           itself (`$shortcode_tag`), in that order.
*/
function add_shortcode( $tag, $callback ) {

    $shortcode_tags = mp_cache_data('shortcode_tags');
  
    if ( '' == trim( $tag ) ) {
        $message = __( 'Invalid shortcode name: Empty name given.' );
        _doing_it_wrong( __FUNCTION__, $message );
        return;
    }
    
    if ( 0 !== preg_match( '@[<>&/\[\]\x00-\x20=]@', $tag ) ) {
        /* translators: 1: shortcode name, 2: space separated list of reserved characters */
        $message = sprintf( __( 'Invalid shortcode name: %1$s. Do not use spaces or reserved characters: %2$s' ), $tag, '& / < > [ ] =' );
        _doing_it_wrong( __FUNCTION__, $message );
        return;
    }

    $shortcode_tags[ $tag ] = $callback;

    mp_cache_data('shortcode_tags', $shortcode_tags );
}






/**
 * Removes hook for shortcode.
 */
function remove_shortcode($tag){

    $shortcode_tags = mp_cache_data('shortcode_tags');

    unset($shortcode_tags[$tags]);

    mp_cache_data('shortcode_tags', $shortcode_tags );
}





/**
 * Removes all shortcode.
 */
function remove_all_shortcode(){

    mp_cache_data('shortcode_tags', null );
}






/**
* On cherche les shortcode dans du contenu
*
* @param string $content Contenu ou chercher les shortcodes.
* @return string Contenu avec le resultat du filtrage des shortcodes.
*/
function do_shortcode( $content ) {

    $shortcode_tags = mp_cache_data('shortcode_tags');
 
    if ( false === strpos( $content, '(' ) ) {
        return $content;
    }
    
    if (empty($shortcode_tags) || !is_array($shortcode_tags))
        return $content;
 

    foreach ( $shortcode_tags as $tag => $callback ) {

        $pattern = '/\([ \t]*'. $tag .'[ \t]*:(.*?)\)/i';

        $content = preg_replace_callback( $pattern , 'do_shortcode_tag' , $content );
    }
 
    // On rajoute les parenthèses si elles sont utilisé en retour d'un shortcode
    $content = strtr( $content, array( '&#40;' => '(', '&#41;' => ')' ) );
 
    return $content;
}





/**
* On construit le shortcode d'un tag avant de l'envoyer à l'execution et on récupère sa sortie
*
* @param array $m Regular expression match array
* @return string|false False on failure
*/
function do_shortcode_tag( $array ){

    $shortcode_tags = mp_cache_data('shortcode_tags');

    // On nettoie shortcode trouvé
    $attrs = trim( rtrim( ltrim( $array[0] , '(' ) , ')' ) );

    // On récupère les paramètres du shortcode
    $attrs = explode( '|' , $attrs );

    // On construit la table des paramètres du shortcode
    foreach( $attrs as $key => $attr ){

        // On récupère le nom du paramètre
        $attr_name = strtolower( trim( substr( $attr , 0 , strpos($attr,':') ) ) );

        // On récupère la valeur du paramètre
        $attr_value = trim( substr( $attr , strpos($attr,':')+1 , size($attr) ) );

        // On supprime le clé une fois la lecture effectué
        unset($attrs[$key]);

        // On associe nom et valeur sur la table de paramètre
        $attrs[$attr_name] = $attr_value;
    }

    // tag
    $tag = key($attrs);
 

    if ( ! is_callable( $shortcode_tags[ $tag ] ) ) {
        /* translators: %s: shortcode tag */
        $message = sprintf( __( 'Attempting to parse a shortcode without a valid callback: %s' ), $tag );
        _doing_it_wrong( __FUNCTION__, $message );
        return $array[0];
    }


    $output = call_user_func( $shortcode_tags[ $tag ], $attrs, $tag );

    return apply_filters( 'do_shortcode_tag', $output, $tag, $attr );
}






/**
* Combine user attributes with known attributes and fill in defaults when needed.
*
* @param array  $pairs     valeures par défaut
* @param array  $atts      paramètres passer par do_shortcode_tag
* @param string $shortcode Option
* @return array Combine et filtre les parametres.
*
*/
function shortcode_atts( $pairs, $attrs, $shortcode = '' ) {

    $attrs = parse_args( $attrs );
    $pairs = parse_args( $pairs );
    
    $out = array();

    foreach ($pairs as $name => $default) {

        if ( array_key_exists($name, $attrs) )
            $out[$name] = $attrs[$name];
        else
            $out[$name] = $default;
    }

    if ( $shortcode ) {
        $out = apply_filters( "shortcode_atts_{$shortcode}", $out, $pairs, $attrs, $shortcode );
    }
 
    return $out;
}