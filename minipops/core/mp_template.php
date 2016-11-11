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
 * Gestion de lecture d'un argument snippet.
 * @return string || array
 */
function get_the_args( $field, $type = null ) {

    $field = (string) $field;

    $__args = mp_cache_data('__args');

    // Si pas d'arguments return
    if( $__args === null || !is_array($__args) || empty($__args) ) return;

    // On créer le noeuds de recherche
    $array_keys = explode('->', $field);

    // On récupère la variable selon le noeud
    $ref = &$__args;

    foreach ($array_keys as $k)
        if(!isset($ref[$k]) ) return; else $ref = &$ref[$k];

    $arg = esc_html($ref);

    // On filtre le résultat si la fonction de validation existe
    $type = 'is_'.$type;

    if( function_exists($type) )
        if( $type($arg)) return $arg; else return;

    return $arg;
}



/**
 * Gestion affichage d'un argument snippet
 * @return echo
 */
function the_args( $field, $before = '', $after = '' ) {

    $field  = (string) $field;
    $before = (string) $before;
    $after  = (string) $after;

    $value = apply_filters( 'the_args_'.$field, get_the_args( $field ) );

    if ( is_array($value) || strlen($value) == 0 )  
        return;

    echo $before . $value . $after;
}


/**
 * Chargement d'un snippet
 * @return echo
 */
function snippet( $snippet ){

    $snippet = (string) $snippet;

    $snippets = glob( MP_TEMPLATE_DIR . '/snippets/' . $snippet .'.php' );

    if( !empty($snippets) ){
        $__args = yaml_parse_file( MP_TEMPLATE_DIR . '/snippets/' . $snippet .'.yml', 0, null, true );
        mp_cache_data('__args', $__args);
        include( MP_TEMPLATE_DIR . '/snippets/' . $snippet .'.php' );
        mp_cache_data('__args', null); // On décharge les arguments du snippet
    }
    return;
}


/***********************************************/
/*        Fonctions get_template               */
/***********************************************/

function get_page_template() {

    // On modifie le template
    $template = get_the_page('template') ? get_template( 'templates/'. get_the_page('template') ) : '';
    return $template ?: get_template( 'templates/page' );
}


function get_template( $template_name ) {

    $template = glob( MP_TEMPLATE_DIR . '/' . $template_name .'.php' );

    if( !empty($template) )
        return MP_TEMPLATE_DIR . '/' . $template_name .'.php';

    else return '';
}


/***********************************************/
/*        Fonctions for the blog               */
/***********************************************/

function the_blog( $field,  $before = '', $after = '' ) {

    $field  = (string) $field;
    $before = (string) $before;
    $after  = (string) $after;

    $value = apply_filters( 'the_blog_'.$field, get_the_blog($field) );
    if ( strlen($value) == 0 )  return;
    echo $before . $value . $after;
}

function the_lang( $before = '', $after = '' ) {

    $before = (string) $before;
    $after = (string) $after;

    $value = apply_filters('the_lang', get_the_lang() );
    if ( strlen($value) == 0 )  return;
    echo $before . $value . $after;
}


/***********************************************/
/*        Fonctions link meta                  */
/***********************************************/

function mp_meta_charset(){
    echo '<meta charset="'.strtolower(CHARSET).'">'. PHP_EOL;
}

function mp_meta_title(){
    $title = apply_filters('meta_title', get_the_blog('title') );
    $title = excerpt( $title, 65);
    if ( strlen($title) == 0 )  return;
    echo '<title>'.$title.'</title>'."\n";
}

function mp_meta_description(){
    $description = excerpt( apply_filters('meta_description', get_the_blog('description') ) );
    if ( strlen($description) == 0 )  return;
    echo '<meta name="description" content="'.$description.'">'."\n";
}

function mp_meta_keywords(){
    $keywords = apply_filters('meta_keywords', get_the_blog('keywords') );
    if ( strlen($keywords) == 0 )  return;
    echo '<meta name="keywords" content="'.$keywords.'">'."\n";
}

function mp_meta_author(){
    $author = apply_filters('meta_author', get_the_blog('author') );
    if ( strlen($author) == 0 )  return;
    echo '<meta name="author" content="'.$author.'">'."\n";
}

function mp_meta_robots(){
    $robots = apply_filters('meta_robots', get_the_blog('robots') );
    $robots_authorized = apply_filters('meta_robots_authorized', array(
                                            'noindex',
                                            'nofollow',
                                            'noindex,nofollow',
                                            'noindex,follow',
                                            'index,nofollow',
                                            'noarchive',
                                            'nosnippet',
                                            'noodp',
                                            'noydir',
                                            'noodp,noydir',
                                            'noarchive,nosnippet',
                                            'noarchive,noodp,noydir',
                                            'noarchive,noodp',
                                            'noarchive,noydir' ) );

    if( is_in( $robots, $robots_authorized ) ) echo '<meta name="robots" content="'.$robots.'">'."\n";
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

    $meta_favicon = '';

    // <!-- Use Iconifyer to generate all the favicons and touch icons you need: http://iconifier.net -->
    $favicon = apply_filters( 'mp_meta_favicon_path', 'favicon.ico' );
    if ( strlen($favicon) != 0 )
        $meta_favicon .= '<link rel="shortcut icon" href="'.$favicon.'" type="image/x-icon">'."\n";

    // <!-- Apple icon: no error 404 for safari and ios -->
    $apple_icon_touch = apply_filters( 'mp_meta_apple_touch_icon_path', 'apple-touch-icon.png' );
    if ( strlen($apple_icon_touch) != 0 )
        $meta_favicon .= '<link rel="apple-touch-icon" href="'.$apple_icon_touch.'" type= "text/plain">'."\n";

    // <!-- Because the humans is important! -->
    $humans = apply_filters( 'mp_meta_humans_path', 'humans.txt' );
    if ( strlen($humans) != 0 )
        $meta_favicon .= '<link rel="author" href="'.$humans.'">'."\n";

    echo $meta_favicon;

    do_action('mp_meta_favicon');
}

function mp_ie_rendering(){

    $ie_rendering = '<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge"><![endif]-->'."\n";
    echo apply_filters('mp_meta_ie_rendering', $ie_rendering );
}


function mp_meta_viewport(){

    /*  Mobile Viewport
    http://j.mp/mobileviewport & http://davidbcalhoun.com/2010/viewport-metatag
    device-width : Occupy full width of the screen in its current orientation
    initial-scale = 1.0 retains dimensions instead of zooming out if page height > device height
    maximum-scale = 1.0 retains dimensions instead of zooming in if page width < device width (wrong for most sites)
    */
    $viewport = '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable = yes">'."\n";
    echo apply_filters('mp_meta_viewport', $viewport);
}

function mp_meta_google_site_verification(){

    // Don't forget to set your site up: http://google.com/webmasters
    $google_check = apply_filters('mp_meta_google_check', '');
    if ( strlen($google_check) == 0 )  return;
    echo '<meta name="google-site-verification" content="'.$google_check.'" />'."\n";
}

function mp_meta_opengraph(){

    if( is_404() )
        return;

    $description = excerpt( apply_filters('meta_description', get_the_blog('description') ) );
    if ( strlen($description) == 0 )  return;

    $title = excerpt( apply_filters('meta_title', get_the_blog('title') ) , 65);
    if ( strlen($title) == 0 )  return;

    global $query;

    // Twitter: see https://dev.twitter.com/docs/cards/types/summary-card for details
    $opengraph  = '<meta name="twitter:card" content="summary">'."\n";
    $opengraph .= '<meta name="twitter:site" content="'.get_the_blog('title') .'">'."\n";
    $opengraph .= '<meta name="twitter:title" content="'.$title.'">'."\n";
    $opengraph .= '<meta name="twitter:description" content="'.$description.'">'."\n";
    $opengraph .= '<meta name="twitter:url" content="'.get_permalink($query).'">'."\n";

    // Facebook (and some others) use the Open Graph protocol: see http://ogp.me/ for details
    $opengraph .= '<meta property="og:title" content="'.$title.'" />'."\n";
    $opengraph .= '<meta property="og:description" content="'.$description.'" />'."\n";
    $opengraph .= '<meta property="og:url" content="'.get_permalink($query).'" />'."\n";
    $opengraph .= '<meta property="og:locale" content="'.get_the_lang().'" />'."\n";
    $opengraph .= '<meta property="og:type" content="website" />'."\n";
    $opengraph .= '<meta property="og:site_name" content="'.get_the_blog('title') .'" />'."\n";

    /* prepare image */
    $image = get_the_page('thumbnail');
    $image = get_the_image("size=medium&file=$image", 'uri');

    if( $image )
        $opengraph .= '<meta property="og:image" content="'.$image.'" />'."\n";

    echo $opengraph;
    
}


/***********************************************/
/*        hook mpops_head                      */
/***********************************************/

add_action('mp_head', 'mp_meta_charset', 1);
add_action('mp_head', 'mp_ie_rendering', 2);
add_action('mp_head', 'mp_meta_title', 3);
add_action('mp_head', 'mp_meta_description', 4);
add_action('mp_head', 'mp_meta_keywords', 5);
add_action('mp_head', 'mp_meta_author', 6);
add_action('mp_head', 'mp_meta_robots', 7);
add_action('mp_head', 'mp_meta_viewport', 8);
add_action('mp_head', 'mp_meta_favicon', 9 );
add_action('mp_head', 'mp_meta_google_site_verification', 10);
add_action('mp_head', 'mp_meta_feed_link', 11);
add_action('mp_head', 'mp_meta_sitemap_link', 12);
add_action('mp_head', 'mp_meta_canonical_link', 13);
add_action('mp_head', 'mp_meta_opengraph', 14);
add_action('mp_head', 'mp_enqueue_styles', 15 );
add_action('mp_head', 'mp_enqueue_scripts', 16 );

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
/*              element Class                  */
/***********************************************/

function body_class( $class = '' ){

    $classes = array();
    
    if( is_page() )
        $classes[] = 'paged';

    if( is_404() )
        $classes[] = 'error404';

    if( is_home() )
        $classes[] = 'home';

    if ( ! empty( $class ) ) {
        if ( !is_array( $class ) )
            $class = preg_split( '#\s+#', $class );
        $classes = array_merge( $classes, $class );
    }

    $classes = array_map('sanitize_html_class', $classes);

    echo 'class="' . join( ' ', $classes ) . '"';
}


/***********************************************/
/*        Fonctions affichage page             */
/***********************************************/

function the_page( $field,  $before = '', $after = '' ) {

    $field = (string) $field;
    $before = (string) $before;
    $after = (string) $after;

    $value = apply_filters( 'the_page_'.$field, get_the_page( $field ) );
    if ( strlen($value) == 0 )  return;

    echo $before . $value . $after;
}

function the_thumbnail( $before = '', $after = '' ) {

    $before = (string) $before;
    $after  = (string) $after;

    $value  = get_the_page('thumbnail');
    $large  = apply_filters( 'the_thumbnail_large', get_the_image('size=large&file='.$value, 'uri') );

    if ( strlen($large) === 0 )  return;

    $small  = apply_filters( 'the_thumbnail_small',  get_the_image('size=small&file='.$value, 'uri') );
    $medium = apply_filters( 'the_thumbnail_medium', get_the_image('size=medium&file='.$value, 'uri') );

    $scheme = apply_filters('the_thumbnail','<img srcset="%s 1x, %s 2x" src="%s">', $value);

    echo $before . sprintf($scheme, $small, $large, $medium ) . $after;
}


/***********************************************/
/*        Fonctions affichage tag              */
/***********************************************/

function search_tag( $before = '', $after = '' ){

    $before = (string) $before;
    $after = (string) $after;

    if( !$tag = is_tag('tag') )
        return;

    the_loop( 'tag='.$tag , 'tag');

    echo $before . $tag . $after;
}


/***********************************************/
/*        Fonctions date                       */
/***********************************************/

function the_date( $format = '',  $before = '', $after = '', $echo = true ) {

    $before = (string) $before;
    $after = (string) $after;

    $value = apply_filters( 'the_date', get_the_date( $format ) );

    if ( strlen($value) == 0 )  return;
    if($echo)
        echo $before . $value . $after;
    else
        return $before . $value . $after;
}

function the_time( $format = '',  $before = '', $after = '', $echo = true ) {

    $before = (string) $before;
    $after = (string) $after;

    $value = apply_filters( 'the_time', get_the_time( $format ) );

    if ( strlen($value) == 0 )  return;
    
    if($echo)
        echo $before . $value . $after;
    else
        return $before . $value . $after;
}


/***********************************************/
/*        Fonctions menu                       */
/***********************************************/

function get_the_menu( $menu_nav = '' ){

    $menu_items = get_option('customize->'. $menu_nav);

    $menu = array();

    if( is_array($menu_items) ){

        foreach ($menu_items as $item => $title){
            if( is_integer($item) )   $item = $title; $title = basename($title);
            if( is_page($item) )      $menu[$item] = esc_html($title);
        }
        $menu = array_flip($menu);
    }
    else
        $menu = get_childs_page();

    return $menu;
}

function the_menu( $menu_nav = '',  $before = '<ul class="menu">', $after = '</ul>', $menu_item = '<li class="menu-item%3$s" itemprop="url"><a href="%1$s" itemprop="name">%2$s</a></li>' ){

    $before = (string) $before;
    $after  = (string) $after;

    $menu  = get_the_menu( $menu_nav );

    if ( is_size($menu, 0) )  return;

    array_walk( $menu, function(&$slug, $title) use ($menu_item){
        
        $title  = is_string($title) ? $title: basename($slug);
        $active = is_same($slug, $GLOBALS['query'] ) ? ' active' : '';
        $menu_item = apply_filters('mp_the_menu_scheme', $menu_item, $slug );
        $slug   = sprintf( $menu_item, get_permalink($slug), $title, $active );

    } );
    echo $before.implode($menu).$after;
}


/***********************************************/
/*        Fonctions fils ariane                */
/***********************************************/

function the_breadcrumb( $separator = ' &rarr;&nbsp;' , $before = '', $after = '' ) {

    $separator = (string) $separator;
    $before    = (string) $before;
    $after     = (string) $after;

    $breadcrumb       = '';
    $breadcrumb_begin = '<nav role="navigation" aria-label="'. __('You are here') .' : " id="breadcrumb" class="breadcrumb">';
    $breadcrumb_begin = apply_filters('breadcrumb_begin', $breadcrumb_begin);
    $breadcrumb_end   = apply_filters('breadcrumb_end', '</nav>');
    $breadcrumb_schema_separator = sprintf( apply_filters('breadcrumb_schema_separator' , '<span aria-hidden="true">%s</span>' ) , $separator );
    $breadcrumb_schema = '<span itemscope itemtype="http://data-vocabulary.org/Breadcrumb"><a itemprop="url" title="%1$s" href="%2$s"><span itemprop="title">%1$s</span></a></span>';
    $breadcrumb_schema = apply_filters('breadcrumb_schema' , $breadcrumb_schema );

    if( is_page() ){

        $queries = get_the_page('slug');

        do {

            $slug = substr( $queries , 0 , strpos($queries,'/') );

            if( $slug === '' )
                $breadcrumb = sprintf( $breadcrumb_schema ,  get_the_blog('title')  , MP_HOME ) . $breadcrumb_schema_separator . $breadcrumb;
            else
                $breadcrumb = ( is_page($slug) ) ? sprintf( $breadcrumb_schema , basename($slug) , get_permalink($slug) ) . $breadcrumb_schema_separator : '';
            
            $queries = str_replace( $slug.'/' , '', $queries );

        } while ( strlen($slug) > 0 );

        $breadcrumb .= get_the_page('title');
    }

    if( is_home() )
        $breadcrumb = get_the_blog('title');

    if( is_404() )
        $breadcrumb = sprintf( $breadcrumb_schema , get_the_blog('title') , MP_HOME ) . $breadcrumb_schema_separator . '404';

    echo $breadcrumb_begin . $before . $breadcrumb . $after . $breadcrumb_end;
}
