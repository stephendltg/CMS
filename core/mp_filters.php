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
        ob_end_flush();
}, 1 );



/***********************************************/
/*        Filter for the meta                  */
/***********************************************/


// On ajoute les filtres par defaut
add_action('TEMPLATE_REDIRECT','mp_load_meta_filter');

function mp_load_meta_filter(){

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
        if ( strlen( get_the_page('keywords') ) == 0 )
            add_filter('meta_keywords', function(){ return null; } );
        else
            add_filter('meta_keywords', function(){ return get_the_page('keywords'); } );

        // On modifie la meta robots
        if ( strlen( get_the_page('robots') ) > 0 )
            add_filter('meta_robots', function(){ return get_the_page('robots'); } );

        // On modifie la meta author
        if ( strlen( get_the_page('author') ) > 0 )
            add_filter('meta_author', function(){ return get_the_page('author'); } );

    }

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

    $robots = sanitize_words( get_the_blog('robots') );
    $robots = str_replace(' ', ',' , $robots);

    // on desindex le site si noindex declaré
    if( is_in($robots, array('noindex', 'noindex,nofollow',) ) )
        $robots = "User-agent: *\nDisallow: /\n";

    else{

        $robots     = "User-agent: *\n";
        $robots    .= "Disallow: /*?\n";
        $robots    .= "Disallow: /*/feed\n";
        $robots    .= "Disallow: /cgi-bin\n";
        $robots    .= "Disallow: /*.php$\n";
        $robots    .= "Disallow: /*.inc$\n";
        $robots    .= "Disallow: /*.gz\n";
        $robots    .= "Disallow: /*.cgi\n";
        $robots    .= "Allow: /*css?*\n";
        $robots    .= "Allow: /*js?*\n\n";
        $robots    .= "# Google Image\n";
        $robots    .= "User-agent: Googlebot-Image\n";
        $robots    .= "Disallow:\n";
        $robots    .= 'Sitemap: '.HOME.'/sitemap.xml';
    }

    echo apply_filter( 'the_robots', $robots);
    exit();
}

// On génére un sitemap
function mp_doing_sitemap(){

    $sitemap    = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $sitemap   .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
    $sitemap   .= "\t<url>\n\t\t<loc>".HOME."</loc>\n\t\t<priority>0.9</priority>\n\t</url>\n";
    $pages      = apply_filter('sitemap_pages', get_all_page() );
    foreach( $pages as $url )
        $sitemap .= "\t<url>\n\t\t<loc>".get_permalink($url)."</loc>\n\t</url>\n";
    $sitemap   .= '</urlset>';

    echo apply_filter( 'the_sitemap', $sitemap );
    exit();
}

// On génére le flux rss
function mp_doing_feed(){

    $feed    = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
    $feed   .= '<rss version="2.0"
    xmlns:content="http://purl.org/rss/1.0/modules/content/"
    xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/"
    xmlns:atom="http://www.w3.org/2005/Atom"
    xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"
    xmlns:slash="http://purl.org/rss/1.0/modules/slash/">'."\n\n";
    $feed   .= "\t<channel>\n";

    $feed   .= "\t</channel>\n";
    $feed   .= '</rss>';

    echo apply_filter( 'the_feed' , $feed );
    exit();

/*
    <title>BoiteAWeb.fr</title>
    <atom:link href="http://boiteaweb.fr/feed" rel="self" type="application/rss+xml" />
    <link>http://boiteaweb.fr</link>
    <description>Sécurité Web et Développement WordPress</description>
    <lastBuildDate>Fri, 20 Nov 2015 14:27:04 +0000</lastBuildDate>
    <language>fr-FR</language>
    <sy:updatePeriod>hourly</sy:updatePeriod>
    <sy:updateFrequency>1</sy:updateFrequency>
    <generator>http://wordpress.org/?v=4.3.1</generator>
    <item>
        <title>WordPress + Technique = WPTech ! : Version 2015</title>
        <link>http://boiteaweb.fr/wordpress-technique-wptech-version-2015-9881.html</link>
        <comments>http://boiteaweb.fr/wordpress-technique-wptech-version-2015-9881.html#comments</comments>
        <pubDate>Fri, 20 Nov 2015 14:27:04 +0000</pubDate>
        <dc:creator><![CDATA[Julio Potier]]></dc:creator>
                <category><![CDATA[WordCamps]]></category>

        <guid isPermaLink="false">http://boiteaweb.fr/?p=9881</guid>
        <description><![CDATA[Le WPTech à Nantes c'est 10 conférences autour de WordPress, du fun, une dcoding room et ... vous ?]]></description>
        <wfw:commentRss>http://boiteaweb.fr/wordpress-technique-wptech-version-2015-9881.html/feed</wfw:commentRss>
        <slash:comments>0</slash:comments>
        </item>
*/

}
