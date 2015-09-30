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

function snippet( $snippet ){
    $snippets = glob( TEMPLATEPATH . '/snippets/' . $snippet .'.php' );

    if( !empty($snippets) )
        include( TEMPLATEPATH . '/snippets/' . $snippet .'.php' );
    return;
}


/***********************************************/
/*        Fonctions for the head               */
/***********************************************/

function html_attributes(){
    echo apply_filter('html_attributes','lang="'.get_option('site-lang').'"');
    return;
}

function charset(){
    echo apply_filter('charset', strtolower(CHARSET) );
    return;
}

function title(){
    echo apply_filter('title', get_option('site-title') );
    return;
}

function copyright(){
    $copyright = pops( get_option('site-copyright') );
    echo apply_filter('copyright', $copyright );
    return;
}

function blog( $field ){
    $field = (string) $field;
    if( is_in( $field , array('title','subtitle') ) )
        echo get_option('site-'.$field);
    if( is_same($field, 'url') )
        echo get_permalink();
    return;
}


/***********************************************/
/*        Fonctions link meta                  */
/***********************************************/

function description(){
    $description = apply_filter('description', get_option('site-description') );
    echo '<meta name="description" content="'.$description.'">'."\n";
    return;
}

function keywords(){
    $keywords = apply_filter('keywords', get_option('site-keywords') );
    echo '<meta name="keywords" content="'.$keywords.'">'."\n";
    return;
}

function author(){
    $author = apply_filter('author', get_option('site-author') );
    echo '<meta name="author" content="'.$author.'">'."\n";
    return;
}

function robots(){
    $robots = apply_filter('robots', '' );
    echo '<meta name="robots" content="'.$robots.'">'."\n";
    return;
}

function feed_link(){
    //<a type="application/rss+xml" href="http://www.xul.fr/rss.xml">Flux RSS de cette page</a>
    $title = get_option('site-title');
    $title = apply_filter('feed_link_title',$title);
    echo '<link rel="alternate" type="application/rss+xml" href="'.get_permalink('rss','feed').'" title="'.$title.'">'."\n";
    echo '<link rel="alternate" type="application/atom+xml" href="'.get_permalink('atom','feed').'" title="'.$title.'">'."\n";
    return;
}

function sitemap_link(){
    $title = get_option('site-title');
    $title = apply_filter('sitemap_link_title',$title);
    echo  '<link rel="sitemap" type="application/xml"  href="'.get_permalink('sitemap').'" title="'.$title.'" />'."\n";
    return;
}

function canonical_link(){
    global $query;
    if( !get_permalink($query) ) return false;
    echo '<link rel="canonical" href="'.get_permalink($query).'" />'."\n";
    return;
}


/***********************************************/
/*        hook mpops_head                      */
/***********************************************/

add_action('mpops_head','description', 1);
add_action('mpops_head','keywords', 1);
add_action('mpops_head','author', 1);
add_action('mpops_head','feed_link', 1);
add_action('mpops_head','sitemap_link', 2);
add_action('mpops_head','canonical_link', 3);



/***********************************************/
/*        Fonctions mpops_header               */
/***********************************************/

function mpops_head(){
    return do_action('mpops_head');
}


/***********************************************/
/*        Fonctions mpops_footer               */
/***********************************************/

function mpops_footer(){
    return do_action('mpops_footer');
}


/***********************************************/
/*        Fonctions get_template               */
/***********************************************/


function get_error_template() {

    global $page;

    $page['title'] = 'Error 404';
    $page['description'] = 'Error 404: page not found !';
    $page['keywords'] = '';

    if ( is_page('error') ){
        add_filter('page_fields_custom', function() { return array('title','description','keywords','author','robots'); } );
        $page = file_get_page($query);
    }

    return get_template('404');
}


function get_home_template() {

    global $page;

    if( is_page('home') ){
        add_filter('page_fields_custom', function() { return array('title','author'); } );
        $page = file_get_page('home');
    }
    $page['url']    = esc_url_raw( HOME );
    $page['description'] = get_option('site-description');
    $page['keywords'] = get_option('site-keywords');

    return get_template('home');
}

function get_page_template() {

    global $page, $query;

    $page = file_get_page($query);

    if( !empty($page['Template']) )
        return get_template( $page['Template'] );
    else
        return get_template( 'page' );
}

function get_template( $template_name ) {
    $template = glob( TEMPLATEPATH . '/' . $template_name .'.php' );
    if( !empty($template) )
        return TEMPLATEPATH . '/' . $template_name .'.php';
    else return '';
}


/***********************************************/
/*        Fonctions get                        */
/***********************************************/

function get( $string ){

    global $page;

    switch ($string) {

        case 'site-lang':
            $get = get_option('site-lang');
            break;
        case 'site-charset':
            $get = CHARSET;
            break;
        case 'site-title':
            $get = get_option('site-title');
            break;
        case 'site-subtitle':
            $get = get_option('site-subtitle');
            break;
        case 'site-author':
            $get = get_option('site-author');
            break;
        case 'site-description':
            $get = get_option('site-description');
            break;
        case 'site-copyright':
            $get = pops( get_option('site-copyright') , HOME );
            break;

        case 'charset':
            $get = CHARSET;
            break;

        default:
            if( !empty($page[$string]) )
                $get = $page[$string];
            break;
    }

    if(!empty($get) )
        echo $get;
    return;
}
