<?php defined('ABSPATH') or die('No direct script access.');

/**
 *
 *
 * @package cms mini POPS
 * @subpackage filters - filters par défaut
 * @version 1
 */

/***********************************************/
/*        Filter for the shutdown              */
/***********************************************/

add_action('shutdown', function() {
    $levels = ob_get_level();
    for ($i=0; $i<$levels; $i++)
        @ob_end_flush();
}, 1 );


/***********************************************/
/*        Filter for the style                 */
/***********************************************/

add_action('enqueue_styles','mp_load_default_style');

/**
 * Chargement de la feuille de style par défaut
 * @return
 */
function mp_load_default_style(){

    // On charge le fichier style.css du thème actif
    if ( glob( MP_TEMPLATE_DIR . '/style.css' ) )
        mp_enqueue_style('my-style', 'style.css');
}


/***********************************************/
/*        Filter pour extrait d'une page vide  */
/***********************************************/

/**
 * Création d'un extrait si ce dernier n'existe pas dans la page
 * @return
 */
add_filter('default_page_excerpt', 'mp_default_the_page_excerpt', 10, 3);
function mp_default_the_page_excerpt($value, $field, $slug){ 
    return mp_easy_minify( excerpt( get_the_page('content', $slug), 140, 'words' ), false );
}

/***********************************************/
/*        Filter pour liens tag                */
/***********************************************/

/**
 * Transforme les listes de mot clés en liens cliquable
 * @return
 */
add_filter('the_page_tag', function($value){ 

    if( strlen($value) === 0 )
        return;

    $value = explode(',', $value);
    $value = array_map( function($value){ return '<a href="'.get_permalink($value, 'tag').'">'.$value.'</a>' ;}
            , $value);

    $tag_delimiter = apply_filters('the_tag_delimiter', ', ');

    return join($tag_delimiter,$value);
} );


/***********************************************/
/*        Filter pour content                  */
/***********************************************/

/**
 * Parser pour champ content d'une page
 * @return
 */
add_filter('get_the_page_content', function($value, $slug){

    if( true === get_option('customize.pages.content.shortcode', false) )
        $value = mp_pops($value, $slug);

    if( true === get_option('customize.pages.content.markdown', false) )
        $value = parse_markdown( $value);
    else
        $value = parse_text( $value);
    
    return $value;

}, 10, 2 );

/***********************************************/
/*        Filter pour logo                     */
/***********************************************/

/**
 * Créer un logo cliquable 
 * @return
 */
add_filter('the_blog_logo', function($logos){

    if( empty($logos) ) return;

    $attr = '';
    $logo = basename($logos[0]);

    if( 'logo.svg' === $logo || 'logo@.svg' === $logo )
        $attr = isset($logos[1]) ? 'onerror="this.removeAttribute(\'onerror\'); this.src=\''.$logos[1].'\'"' : '';

    $scheme = apply_filters('mp_logo_scheme', '<a href="%s" title="%s"><img class="logo" src="%s" alt="logo %s" %s></a>' );
    
    return sprintf( $scheme, get_the_blog('home'), get_the_blog('title'), $logos[0], get_the_blog('title'), $attr );

});



/*********************************************************/
/*                         fonction  Backup              */
/*********************************************************/

/**
 * Sauvegarde repertoire du site
 * @return
 */
function do_backup_website() {

    $backup_file     = 'website-' . date( 'd-m-Y-G-i' );  // nom de l'archive de backup 
    $backup_dir      = $_SERVER['DOCUMENT_ROOT'].'/backup-website-' . substr( md5( __FILE__ ), 0, 8 ); // nom du dossier où sera stocké tous les backup 
    $htaccess_file   = $backup_dir . '/.htaccess'; // chemin vers le fichier .htaccess du dossier de backup 
    $backup_max_life = 259200; // temps maximum de vie d'un backups 

    // On créé le dossier backup-bdd si il n'existe pas 
    if( !is_dir( $backup_dir ) )
        mkdir( $backup_dir, 0755 ); 

    // On ajoute un fichier .htaccess pour la sécurité 
    if( !file_exists( $htaccess_file ) )
        file_marker_contents($htaccess_file, "Order Allow, Deny\nDeny from all");

    // On zip les fichiers du site
    if( class_exists( 'ZipArchive' ) ) {

        // On crée une fonction dans la classe qui permettra de parcourir les dossiers du site 
        class ZipRecursif extends ZipArchive {

            public function addDirectory( $dir ) { 

                foreach( glob( $dir . '/*' ) as $file )
                    is_dir( $file ) ? $this->addDirectory( $file ) : $this->addFile( $file );
            } 
        } 

        $zip = new ZipRecursif; 

        // On check si on peut se servir de l'archive 
        if( $zip->open( $backup_dir . '/' . $backup_file . '.zip' , ZipArchive::CREATE ) === true ){

            $zip->addDirectory(MP_CONTENT_DIR);
            $zip->close(); 
        }
    } 

    // On supprime les backup qui datent de plus d'une semaine 
    foreach ( glob( $backup_dir . '/*.zip' ) as $file ) { 

        if( time() - filemtime( $file ) > $backup_max_life )
            unlink($file); 
    } 

}


/*****************************************************************************/
/*        Filter pour optimiser chargement html et lancer sauvegarde         */
/*****************************************************************************/

/* On charge le cache s'il existe, sinon on lance un hook pour créer le cache */
if( !DEBUG ){

    // Hook d'appel ( back up tous les jours )
    add_action('callback', 'do_backup_website');

    // Cache statique
    if( $_SERVER['REQUEST_METHOD'] == 'GET'
        && empty($_GET)
        && isset($_SERVER['HTTP_USER_AGENT'])
        && !preg_match( '/(minipops_auth)/', var_export( $_COOKIE , true ) )
        ){

        $cache_dir = $_SERVER['DOCUMENT_ROOT'].'/cache-website-' . substr( md5( __FILE__ ), 0, 8 ) .'/';
        $cache_file = $cache_dir . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/index.html';

        // On supprimer le cache toutes les 24 heures
        if( file_exists($cache_file) && (filemtime($cache_file) + DAY_IN_SECONDS) > microtime(true) ){
            readfile( $cache_file );
            die();
        }

        // Hook qui va créer le cache
        add_action('TEMPLATE_REDIRECT', 'mp_load_mp_cache_pages' );
    }

}


/**
 * Chargement de la fonction de création du cache 
 * @return
 */
function mp_load_mp_cache_pages(){

    if( true === get_option('site.setting.static-cache', false ) )
        ob_start('mp_cache_pages');
}


/**
 * Créer le cache d'une page   
 * @return
 */
function mp_cache_pages( $html ){

    if( apply_filters('mp_cache', false) ) return $html;

    if( is_404() ) return $html;

    $cache_file = $_SERVER['DOCUMENT_ROOT'].'/cache-website-' . substr( md5( __FILE__ ), 0, 8 ) .'/';
    @mkdir( $cache_file . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 0755, true );
    file_put_content( $cache_file . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/index.html', mp_minify_html($html) );
    return $html;
}

/**
 * Efface l'ensemble du cache
 * @return
 */
function mp_clear_cache_all_pages(){

    $cache_file = $_SERVER['DOCUMENT_ROOT'].'/cache-website-' . substr( md5( __FILE__ ), 0, 8 ) .'/';
    if( is_dir($cache_file) )
        rrmdir($cache_file);
}


/**
 * On ajoute une action si une modification des règles de réécriture url
 * @return
 */
add_action('mp_before_write_rules', 'mp_clear_cache_all_pages' );


/*********************************************************/
/*        Filter pour optimiser chargement image         */
/*********************************************************/

// On optimise les images une fois minipops chargé
add_action('loaded', 'mp_lazy_load');

/**
 * Charge le script lasyload et lance l'action de lasyload   
 * @return
 */
function mp_lazy_load(){

    // On ajoute le script lazyload
    add_inline_script('lazyload', '(function(a,e){function f(){var d=0;if(e.body&&e.body.offsetWidth){d=e.body.offsetHeight}if(e.compatMode=="CSS1Compat"&&e.documentElement&&e.documentElement.offsetWidth){d=e.documentElement.offsetHeight}if(a.innerWidth&&a.innerHeight){d=a.innerHeight}return d}function b(g){var d=ot=0;if(g.offsetParent){do{d+=g.offsetLeft;ot+=g.offsetTop}while(g=g.offsetParent)}return{left:d,top:ot}}function c(){var l=e.querySelectorAll("[data-lazy-original]");var j=a.pageYOffset||e.documentElement.scrollTop||e.body.scrollTop;var d=f();for(var k=0;k<l.length;k++){var h=l[k];var g=b(h).top;if(g<(d+j)){h.src=h.getAttribute("data-lazy-original");h.removeAttribute("data-lazy-original")}}}if(a.addEventListener){a.addEventListener("DOMContentLoaded",c,false);a.addEventListener("scroll",c,false)}else{a.attachEvent("onload",c);a.attachEvent("onscroll",c)}})(window,document);' );

    // On ajouter un filter pour the_content et modifier les images et rendre lazy load actif
    add_action('TEMPLATE_REDIRECT', function(){ ob_start('mp_lazyload_images'); } , PHP_INT_MAX );

}

/**
 * Modifie les images par un gif base 64 ( optimise le chargement du site )
 * @param (html) Contenu html
 * @return
 */
function mp_lazyload_images( $html ) {

    $lazyload_replace_callback = function($matches) {
        return sprintf( '<img%1$s src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-lazy-original=%2$s%3$s><noscript><img%1$s src=%2$s%3$s></noscript>', $matches[1], $matches[2], $matches[3] );
    };

    return preg_replace_callback( '#<img([^>]*) src=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))([^>]*)>#', $lazyload_replace_callback, $html );
}


/***********************************************/
/*        Filter for the meta                  */
/***********************************************/

// On ajoute les filtres par defaut
add_action('TEMPLATE_REDIRECT','mp_load_meta_filter');

/**
 * Lance les filtre pour modifier les meta donnée de la page html  
 * @return
 */
function mp_load_meta_filter(){
    
    /***********************************************/
    /*        meta for the 404                     */
    /***********************************************/

    if( is_404() ){

        add_filter('meta_title', function($title){ return '404 | '. $title; } );
        add_filter('meta_description', function(){ return null; } );
        add_filter('meta_keywords', function(){ return null; } );
        add_filter('meta_author', function(){ return null; } );
        add_filter('meta_canonical_link', function(){ return null; } );
    }
    

    /***********************************************/
    /*        meta for the page                    */
    /***********************************************/

    if( is_page() ){

        // On modifie la meta title
        add_filter('meta_title', function($title){ return get_the_page('title').' | '. $title; } );

        // On modifie la meta description
        if ( strlen( get_the_page('description') ) == 0 )
            add_filter('meta_description', function(){ return get_the_page('content'); } );
        else
            add_filter('meta_description', function(){ return get_the_page('description'); } );

        // On modifie la meta keywords
        if ( strlen( get_the_page('tag') ) == 0 )
            add_filter('meta_keywords', function(){ return null; } );
        else
            add_filter('meta_keywords', function(){ return get_the_page('tag'); } );

        // On modifie la meta robots
        if ( strlen( get_the_page('robots') ) > 0 )
            add_filter('meta_robots', function(){ return get_the_page('robots'); } );

        // On modifie la meta author
        if ( strlen( get_the_page('author') ) > 0 )
            add_filter('meta_author', function(){ return get_the_page('author'); } );

    }

}



/***********************************************/
/*        Filter for the robot                 */
/***********************************************/

// On corrige le header
add_filter('mp_http_header', 'mp_set_http_header');

// On ajoute la fonction d'appel de construction du fichier robots
add_action('do_robots' , 'mp_doing_robots');

// On ajoute la fonction d'appel de construction du fichier flux rss
add_action('do_feed' , 'mp_doing_feed');

// On ajoute la fonction d'appel de construction du fichier sitempa.xml
add_action('do_sitemap' , 'mp_doing_sitemap');

// On ajoute la fonction d'appel de construction du fichier humans
add_action('do_humans' , 'mp_doing_humans');


/**
 * Gestion du header
 * @return
 */
function mp_set_http_header(){

    $header = array();

    if( is_sitemap() )
        $header = array( 
            'response' => 200, 
            'header' => array(
                'Content-Type: text/xml; charset=' . CHARSET,
                "X-Robots-Tag: noindex",
                'Pragma: public',
                'Cache-Control: maxage=' . DAY_IN_SECONDS,
                'Expires: ' . gmdate('D, d M Y H:i:s', time() + DAY_IN_SECONDS) . ' GMT'
        ) );


    if( is_robots() )
        $header = array( 
            'response' => 200, 
            'header' => array(
                'Content-Type: text/plain; charset='.CHARSET,
                "X-Robots-Tag: noindex",
                'Pragma: public',
                'Cache-Control: maxage=' . DAY_IN_SECONDS,
                'Expires: ' . gmdate('D, d M Y H:i:s', time() + DAY_IN_SECONDS) . ' GMT'
        ) );


    if( is_feed() )
        $header = array( 
            'response' => 200, 
            'header' => array(
                'Cache-Control: must-revalidate, pre-check=0, post-check=0, max-age=0',
                'Content-Type: application/rss+xml; charset=utf-8'
        ) );


    if( is_humans() )
        $header = array( 
            'response' => 200, 
            'header' => array( 
                'Content-Type: text/plain; charset=' . CHARSET,
                'Pragma: public',
                'Cache-Control: maxage='. DAY_IN_SECONDS,
                'Expires: ' . gmdate('D, d M Y H:i:s', time() + DAY_IN_SECONDS) . ' GMT'
        ) );


    return $header;
}


/**
 * Générer un fichier robots.txt
 * @return
 */
function mp_doing_robots(){

    $robot = sanitize_words( get_the_blog('robots') );
    $robot = str_replace(' ', ',' , $robot);

    $args = array(
        'noindex'     => is_in($robot, array('noindex', 'noindex,nofollow') ),
        'sitemap_url' => get_permalink('sitemap')
        );

    $args     = apply_filters('robots_args', $args);
    $template = apply_filters('robots_template', ABSPATH . INC .'/data/robots.txt' );
    $template = file_get_content( realpath($template) );
    $robots   = apply_filters( 'the_robots', mp_brackets( $template, $args ) );

    if( strlen($robots) == 0 )
        redirect( get_the_blog('home') );

    echo $robots;
}


/**
 * Générer un fichier sitemap 
 * @return
 */
function mp_doing_sitemap(){

    $pages = apply_filters('sitemap_pages', get_all_page() );

    $args = array(
        'home'  => guess_url(),
        'pages' => map_deep( $pages, function($value){ return get_the_page('url',$value);} )
        );


    $args     = apply_filters('sitemap_args', $args);
    $template = apply_filters('sitemap_template', ABSPATH . INC .'/data/sitemap.xml' );
    $template = file_get_content( realpath($template) );
    $sitemap  = apply_filters( 'the_sitemap', mp_brackets( $template, $args ) );

    if( strlen($sitemap) == 0 )
        redirect( get_the_blog('home') );

    echo $sitemap;
}



/**
 * Générer un flux xml
 * @return
 */
function mp_doing_feed(){

    // Boucle pour flux rss
    $pages = the_loop( apply_filters('feed_loop', 'max=5&order=desc') , 'my_feed');

    $title     = map_deep($pages, function($value){ return get_the_page('title',$value);} );
    $url       = map_deep($pages, function($value){ return get_the_page('url',$value);} );
    $author    = map_deep($pages, function($value){ return get_the_page('author',$value);} );
    $date      = map_deep($pages, function($value){ return get_the_date('D, d M y H:i:s O',$value);} );
    $excerpt   = map_deep($pages, function($value){ return get_the_page('excerpt',$value);} );
    $thumbnail = map_deep($pages, function($value){ return get_the_image('size=medium&file='.get_the_page('thumbnail',$value), 'uri'); } );

    $args = array(
        'blog'       => array( 'title' => get_the_blog('title'),
                               'home'  => get_the_blog('home'),
                               'description' => get_the_blog('description'),
                               'lang'  => get_the_blog('lang')
                               ),
        'now'        => _date('D, d M y H:i:s O'),
        'feed_url'   => get_permalink('rss','feed'),
        'pages'      => $pages,
        'title'      => array_combine($pages, $title),
        'url'        => array_combine($pages, $url),
        'author'     => array_combine($pages, $author),
        'date'       => array_combine($pages, $date),
        'excerpt'    => array_combine($pages, $excerpt),
        'thumbnail'  => array_combine($pages, $thumbnail),
        'logo'       => get_the_image('name=logo&orderby=type&max=1&order=desc', 'uri')
        );

    $args     = apply_filters('feed_args', $args);
    $template = apply_filters('feed_template', ABSPATH . INC .'/data/feed.xml' );
    $template = file_get_content( realpath($template) );
    $feed     = apply_filters( 'the_feed', mp_brackets( $template, $args ) );

    if( strlen($feed) == 0 )
        redirect( get_the_blog('home') );

    echo $feed;
}


/**
 * Générer un fichier humans.txt
 * @return
 */
function mp_doing_humans(){

    $template = apply_filters('sitemap_template', ABSPATH . INC .'/data/humans.txt' );
    $humans   = file_get_content( realpath($template) );

    if( strlen($humans) == 0 )
        redirect( get_the_blog('home') );

    echo $humans;
}