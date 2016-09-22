<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction pages
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

function mp_load_default_style(){

    // On charge le fichier style.css du thème actif
    if ( glob( MP_TEMPLATE_DIR . '/style.css' ) )
        mp_enqueue_style('my-style', 'style.css');
}


/***********************************************/
/*        Filter pour extrait d'une page vide    */
/***********************************************/
add_filter('the_page_excerpt', function($value){ 

    if( strlen($value) === 0 )
        return mp_easy_minify( excerpt( get_the_page('content'), 140, 'words' ), false );
    return $value; 
} );


/***********************************************/
/*        Filter pour logo                     */
/***********************************************/

add_filter('the_blog_logo', function($logos){

    if( empty($logos) ) return;

    $attr = '';
    $logo = basename($logos[0]);

    if( 'logo.svg' === $logo || 'logo@.svg' === $logo )
        $attr = isset($logos[1]) ? 'onerror="this.removeAttribute(\'onerror\'); this.src=\''.$logos[1].'\'"' : '';

    $scheme = apply_filters('mp_logo_scheme', '<a href="%s" title="%s"><img class="logo" src="%s" alt="logo %s" %s></a>' );
    
    return sprintf( $scheme, get_the_blog('home'), get_the_blog('title'), $logos[0], get_the_blog('title'), $attr );

});


/***********************************************/
/*        Filter for the meta                  */
/***********************************************/


// On ajoute les filtres par defaut
add_action('TEMPLATE_REDIRECT','mp_load_meta_filter');

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

// On ajoute la fonction d'appel de construction du fichier robots
add_action('do_robots' , 'mp_doing_robots');

// On ajoute la fonction d'appel de construction du fichier flux rss
add_action('do_feed' , 'mp_doing_feed');

// On ajoute la fonction d'appel de construction du fichier sitempa.xml
add_action('do_sitemap' , 'mp_doing_sitemap');

// On ajoute la fonction d'appel de construction de l'image favicon
add_action('do_favicon' , 'mp_doing_favicon');


// On génére un fichier robots
function mp_doing_robots(){

    // On déclarer le bon header
    header( 'Content-Type: text/plain; charset='.CHARSET );
    header("X-Robots-Tag: noindex", true);

    $robot = sanitize_words( get_the_blog('robots') );
    $robot = str_replace(' ', ',' , $robot);

    $robots     = "# www.robotstxt.org/\n\n";

    // on desindex le site si noindex declaré
    if( is_in($robot, array('noindex', 'noindex,nofollow',) ) )
        $robots .= "User-agent: *\nDisallow: /\n";

    else{

        $robots    .= "User-agent: *\n";
        $robots    .= "Disallow: /*?\n";
        // On désindexe tous les URL ayant des paramètres (duplication de contenu) sauf les fichier css et js ( numero de version après le ? de l'url)
        $robots    .= "Allow: /*css?*\n";
        $robots    .= "Allow: /*js?*\n";
        // On bloque les URL de ping et de trackback
        $robots    .= "Disallow: */trackback\n";
        // On bloque tous les flux RSS sauf celui principal (enlevez /* pour bloquer TOUS les flux)
        $robots    .= "Disallow: /*/feed\n";
        // On élimine ce répertoire sensible présent sur certains serveurs 
        $robots    .= "Disallow: /cgi-bin\n";
        // On désindexe tous les fichiers qui n'ont pas lieu de l'être
        $robots    .= "Disallow: /*.php$\n";
        $robots    .= "Disallow: /*.inc$\n";
        $robots    .= "Disallow: /*.gz\n";
        $robots    .= "Disallow: /*.cgi\n";
        // ne pas indexer des pages, mais de faire en sorte que les images qu’elles contiennent soient quand même ajoutées dans le moteur de recherche google
        $robots    .= "# Google Image\n";
        $robots    .= "User-agent: Googlebot-Image\n";
        $robots    .= "Disallow:\n";
        $robots    .= "User-agent: Mediapartners-Google\n";
        $robots    .= "Disallow:\n";
        // Sitemap: Google : href="http://www.google.fr/webmasters/ | Yahoo & Bing: href="http://www.bing.com/toolbox/webmaster
        $robots    .= 'Sitemap: '.MP_HOME.'/sitemap.xml';
    }

    echo apply_filters( 'the_robots', $robots);
    exit();
}

// On génére un sitemap
function mp_doing_sitemap(){

    // On déclare le bon header
    header( 'Content-Type: text/xml; charset='.CHARSET );
    header("X-Robots-Tag: noindex", true);

    $sitemap    = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $sitemap   .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    $sitemap   .= "\t<url>\n\t\t<loc>".MP_HOME."</loc>\n\t\t<priority>0.9</priority>\n\t</url>\n";
    $pages      = apply_filters('sitemap_pages', get_all_page() );
    foreach( $pages as $url )
        $sitemap .= "\t<url>\n\t\t<loc>".get_permalink($url)."</loc>\n\t</url>\n";
    $sitemap   .= '</urlset>';

    echo apply_filters( 'the_sitemap', $sitemap );
    exit();
}

// On génére le flux rss
function mp_doing_feed(){

    // on déclarerle bon header
    header( 'Content-Type: text/xml; charset='.CHARSET );

    // Boucle pour flux rss
    the_loop('max=5', 'my_feed'); 

    // Déclaration du document
    echo '<?xml version="1.0" encoding="UTF-8" ?>';
?>
    
    <rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/"
    xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/"
    >
    <channel>
        <title><?php the_blog('title') ?></title>
        <atom:link href="<?php echo get_permalink('rss','feed') ?>" rel="self" type="application/rss+xml" />
        <link><?php the_blog('home') ?></link>
        <description><?php the_blog('description') ?></description>
        <lastBuildDate><?php echo _date('D, d M y H:i:s O') ?></lastBuildDate>
        <language><?php the_blog('lang') ?></language>
        <sy:updatePeriod>hourly</sy:updatePeriod>
        <sy:updateFrequency>1</sy:updateFrequency>
<?php while( have_pages('my_feed') ):?>
        <item>
            <title><?php the_page('title') ?></title>
            <link><?php the_page('url') ?></link>
            <pubDate><?php the_date('D, d M y H:i:s O') ?></pubDate>
            <dc:creator><?php the_page('author','<![CDATA[', ']]>') ?></dc:creator>
<?php 
if( strlen(get_the_page('tag') ) === 0 ):
else :
    foreach ( explode(',',get_the_page('tag') ) as $category ):
?>
            <category><![CDATA[<?php echo $category ?>]]></category>
<?php endforeach; endif;?>
            <guid isPermaLink="false"><?php the_page('url') ?></guid>
            <description><![CDATA[<?php the_page('thumbnail')?><p><?php the_page('excerpt')?></p>]]></description>
        </item>
<?php endwhile; ?>
    </channel>
</rss>

<?php

    exit();
}