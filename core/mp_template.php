<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Gestion des options du CMS mini POPS
 *
 * @package     cms mini POPS
 * @subpackage  template
 * @version 1
 */


/***********************************************/
/*        Fonctions snippets                   */
/***********************************************/

/**
 * Gestion de lecture d'un argument snippet
 * @return string || array
 */
function get_the_args( $field ) {

    $field = (string) $field;

    //var_dump($field);

    global $__args;

    $params = explode('->', $field);
    $size = size($params);

    $args = '';

    if( !empty( $__args[ $params[0] ] ) ){

        if( is_same( $size, 1 ) )
            $args = $__args[ $params[0] ];

        elseif( is_same( $size, 2 ) ){
            if( !empty( $__args[ $params[0] ][ $params[1] ] ) )
                $args = $__args[ $params[0] ][ $params[1] ];
        }

        elseif( is_same( $size, 3 ) ){
            if( !empty( $__args[ $params[0] ][ $params[1] ] ) && !empty( $__args[ $params[0] ][ $params[1] ][ $params[2] ] ) )
                $args = $__args[ $params[0] ][ $params[1] ][ $params[2] ];
        }

    }
    /*
    /\([ \t]*'. $name .'[ \t]*:(.*?)\)/i
    /&(\w+)/i
    */
    if( is_string($args) )
        $args = preg_replace_callback('/&([\w->]+)/i', function($matches){
            $matches = get_the_args($matches[1]);
            return is_array( $matches ) ? '' : $matches ;
        } , $args);

    return $args;
}



/**
 * Gestion affichage d'un argument snippet
 * @return echo
 */
function the_args( $field, $before = '', $after = '' ) {

    $field  = (string) $field;
    $before = (string) $before;
    $after  = (string) $after;

    $value = apply_filter( 'the_args_'.$field, get_the_args( $field ) );
    if ( is_array($value) || strlen($value) == 0 )  return;
    echo $before . $value . $after;
}


/**
 * Chargement d'un snippet
 * @return echo
 */
function snippet( $snippet ){
    $snippet = (string) $snippet;
    $snippets = glob( TEMPLATEPATH . '/snippets/' . $snippet .'.php' );
    if( !empty($snippets) ){
        if( glob( TEMPLATEPATH . '/snippets/' . $snippet .'.yaml' ) )
            $GLOBALS['__args'] = file_get_yaml( TEMPLATEPATH . '/snippets/' . $snippet .'.yaml', true );
        include( TEMPLATEPATH . '/snippets/' . $snippet .'.php' );
        unset($GLOBALS['__args']);
    }
    return;
}


/***********************************************/
/*        Fonctions get_template               */
/***********************************************/

function get_page_template() {
    // On modifie le template
    $template = get_the_page('template') ? get_template( 'templates/'. get_the_page('template') ) : '';
    return !empty($template)?: get_template( 'templates/page' );
}


function get_template( $template_name ) {

    $template = glob( TEMPLATEPATH . '/' . $template_name .'.php' );
    if( !empty($template) )
        return TEMPLATEPATH . '/' . $template_name .'.php';
    else return '';
}


/***********************************************/
/*        Fonctions for the blog               */
/***********************************************/

function the_blog( $field,  $before = '', $after = '' ) {

    $field = (string) $field;
    $before = (string) $before;
    $after = (string) $after;

    $value = apply_filter( 'the_blog_'.$field, get_the_blog($field) );
    if ( strlen($value) == 0 )  return;
    echo $before . $value . $after;
}

/**
 * On retourne l'url du site
 * @return url
 */
function get_home_url(){
    echo get_permalink();
}


/***********************************************/
/*        Fonctions link meta                  */
/***********************************************/

function mp_meta_charset(){
    echo '<meta charset="'.strtolower(CHARSET).'">'."\n";
}

function mp_meta_title(){
    $title = apply_filter('meta_title', get_the_blog('title') );
    if ( strlen($title) == 0 )  return;
    echo '<title>'.$title.'</title>'."\n";
}

function mp_meta_description(){
    $description = excerpt( apply_filter('meta_description', get_the_blog('description') ) );
    if ( strlen($description) == 0 )  return;
    echo '<meta name="description" content="'.$description.'">'."\n";
}

function mp_meta_keywords(){
    $keywords = sanitize_words( apply_filter('meta_keywords', get_the_blog('keywords') ) );
    $keywords = str_replace(' ', ', ', $keywords);
    if ( strlen($keywords) == 0 )  return;
    echo '<meta name="keywords" content="'.$keywords.'">'."\n";
}

function mp_meta_author(){
    $author = sanitize_words( apply_filter('meta_author', get_the_blog('author') ) );
    if ( strlen($author) == 0 )  return;
    echo '<meta name="author" content="'.$author.'">'."\n";
}

function mp_meta_robots(){
    $robots = sanitize_words( apply_filter('meta_robots', get_the_blog('robots') ) );
    $robots = str_replace(' ', ',' , $robots);
    if( is_in( $robots, robots_authorized() ) ) echo '<meta name="robots" content="'.$robots.'">'."\n";
    return;
}

function mp_meta_feed_link(){
    //<a type="application/rss+xml" href="http://www.xul.fr/rss.xml">Flux RSS de cette page</a>
    echo '<link rel="alternate" type="application/rss+xml" href="'.get_permalink('rss','feed').'" title="'. get_the_blog('title').'">'."\n";
}

function mp_meta_sitemap_link(){
    echo  '<link rel="sitemap" type="application/xml"  href="'.get_permalink('sitemap').'" title="'. get_the_blog('title').'" />'."\n";
}

function mp_meta_canonical_link(){
    global $query;
    if( !get_permalink($query) ) return;
    echo '<link rel="canonical" href="'. get_permalink($query) .'" />'."\n";
}

function mp_meta_favicon(){
    echo '<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">'."\n";
}


/***********************************************/
/*        hook mpops_head                      */
/***********************************************/

add_action('mp_head','mp_meta_charset', 1);
add_action('mp_head','mp_meta_title', 2);
add_action('mp_head','mp_meta_description', 3);
add_action('mp_head','mp_meta_keywords', 4);
add_action('mp_head','mp_meta_author', 5);
add_action('mp_head','mp_meta_robots', 6);
add_action('mp_head','mp_meta_feed_link', 7);
add_action('mp_head','mp_meta_sitemap_link', 8);
add_action('mp_head','mp_meta_canonical_link', 9);
add_action('mp_head','mp_enqueue_styles', 10 );
add_action('mp_head','mp_enqueue_scripts', 11 );
add_action('mp_head','mp_meta_favicon', 12 );


/***********************************************/
/*        hook mpops_footer                      */
/***********************************************/

add_action('mp_footer','mp_enqueue_styles', 1, array(true) );
add_action('mp_footer','mp_enqueue_scripts', 2, array(true) );


/***********************************************/
/*        Fonctions mpops_header               */
/***********************************************/

function mp_head(){
    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE
    return do_action('mp_head');
}


/***********************************************/
/*        Fonctions mpops_footer               */
/***********************************************/

function mp_footer(){
    static $one_shot = false;if($one_shot) return;else $one_shot = true; // FUNCTION SECURE
    return do_action('mp_footer');
}



/***********************************************/
/*        Fonctions affichage page             */
/***********************************************/

function the_page( $field,  $before = '', $after = '' ) {
    $field = (string) $field;
    $before = (string) $before;
    $after = (string) $after;
    $value = apply_filter( 'the_'.$field, get_the_page( $field ) );
    if ( strlen($value) == 0 )  return;
    echo $before . $value . $after;
}


/***********************************************/
/*        Fonctions affichage images           */
/***********************************************/
/**
 * the_images : afficher images recherché
 * @param  $where           array() : Listes des slugs de pages où chercher les images sous forme de tableau
 * @param  $name            string  : Listes des noms de medias recherchés séparer par des virgules ex: drums,loops
 * @param  $max             integer : Nombre de résultat par défaut : 10
 * @param  $image_schema    string  : Schema de retour affiché
 * @return array    retourne les résultats sous forme de tableau
 */
function the_images( $name ='' , $where = array(), $max = 10, $image_schema = '<img src="%1$s" alt="%2$s"/>'){
    $max = (integer) $max;
    $name = (string) $name;
    $image_schema = (string) $image_schema;
    $images = get_the_images( $name, $where, $max );
    if ( is_size($images, 0) )  return;
    $images = array_map( function($image)use($image_schema){
        $image_alt    = sanitize_words( apply_filter('the_image_alt' , substr( basename($image), 0, strpos(basename($image),'.') ), $image ) );
        return sprintf($image_schema, $image, $image_alt );} , $images );
    echo implode($images);
}


/***********************************************/
/*        Fonctions menu                       */
/***********************************************/

function get_the_menu( $slugs = '' ){
    if( is_array($slugs) ){
        $slugs = array_map( function($slug){ return is_page($slug) ? $slug : null ; }, $slugs);
        $slugs = array_filter($slugs);
    }
    else
        $slugs = get_childs_page( (string) $slugs );
    return $slugs;
}

function the_menu( $slugs = '', $before = '<ul class="menu">', $after = '</ul>', $menu_item = '<li class="menu-item%3$s"><a href="%1$s">%2$s</a></li>' ){
    $before = (string) $before;
    $after = (string) $after;

    $slugs = get_the_menu( $slugs );
    if ( is_size($slugs, 0) )  return;

    $slugs = array_map( function($slug)use($menu_item){
        $menu_title = apply_filter('the_menu_title', basename($slug), $slug );
        $active = is_same($slug, $GLOBALS['query'] ) ? ' active' : '';
        return sprintf( $menu_item, get_permalink($slug), $menu_title, $active );
    }, $slugs);

    echo $before.implode($slugs).$after;
}

/***********************************************/
/*        Fonctions loop                       */
/***********************************************/

function get_the_loop(){

}

function the_loop( ){

}

/***********************************************/
/*        Fonctions fils ariane                */
/***********************************************/

function the_breadcrumb( $separator = ' &rarr;&nbsp;' , $before = '', $after = '' ) {

    $separator = (string) $separator;
    $before = (string) $before;
    $after = (string) $after;
    $breadcrumb = '';
    $breadcrumb_schema_separator = sprintf( apply_filter('breadcrumb_schema_separator' , '<span aria-hidden="true">%s</span>' ) , $separator );
    $breadcrumb_schema = '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" title="%1$s" href="%2$s"><span itemprop="title">%1$s</span></a></span>';
    $breadcrumb_schema = apply_filter('breadcrumb_schema' , $breadcrumb_schema );

    if( is_page() ){
        $queries = get_the_page('slug');
        do {
            $slug = substr( $queries , 0 , strpos($queries,'/') );
            if( $slug === '' )
                $breadcrumb = sprintf( $breadcrumb_schema ,  get_the_blog('title')  , HOME ) . $breadcrumb_schema_separator . $breadcrumb;
            else
                $breadcrumb = ( is_page($slug) ) ? sprintf( $breadcrumb_schema , basename($slug) , get_permalink($slug) ) . $breadcrumb_schema_separator : '';
            $queries = str_replace( $slug.'/' , '', $queries );
        } while ( strlen($slug) > 0 );
        $breadcrumb .= get_the_blog('title');
    }

    if( is_home() )
        $breadcrumb = get_the_blog('title');

    if( is_404() )
        $breadcrumb = sprintf( $breadcrumb_schema , get_the_blog('title') , HOME ) . $breadcrumb_schema_separator . '404';

    echo $before . $breadcrumb . $after;
}
