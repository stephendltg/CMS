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
add_filter('the_page_excerpt', function($value){ 

    if( strlen($value) === 0 )
        return mp_easy_minify( excerpt( get_the_page('content'), 140, 'words' ), false );
    return $value; 
} );


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
/*                         filter  Backup                 /
/*********************************************************/

// Hook d'appel ( back up tous les jours )
add_action('callback', 'do_backup_website');

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

            $zip->addDirectory(ABSPATH); 
            $zip->close(); 
        }
    } 

    // On supprime les backup qui datent de plus d'une semaine 
    foreach ( glob( $backup_dir . '/*.zip' ) as $file ) { 

        if( time() - filemtime( $file ) > $backup_max_life )
            unlink($file); 
    } 

}


/*********************************************************/
/*        Filter pour optimiser chargement html          */
/*********************************************************/

/* On charge le cache s'il existe, sinon on lance un hook pour créer le cache */
if( CACHE && !DEBUG ){

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
        add_action('TEMPLATE_REDIRECT', function(){ ob_start('mp_cache_pages'); } );
    }

}

/**
 * Créer le cache d'une page   
 * @return
 */
function mp_cache_pages( $html ){

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

/*********************************************************/
/*        Filter pour compass                            */
/*********************************************************/

add_filter('pre_style', 'mp_compass', 10, 2);

/**
 * Compilation des fichiers sass  
 * @return
 */
function mp_compass( $url, $dependencies ){

    if( 'scss' == substr(strrchr($url,'.'), 1) ){

        // On redessine le chemin
        $path = str_replace(MP_TEMPLATE_URL, MP_TEMPLATE_DIR, $url);

        // Si le fichier n'existe pas on renvoie null
        if( !file_exists($path) )  return;

        // Nom du fichier
        $file_name  = pathinfo($path)['filename'];

        // Path scss file
        $scss_dir = dirname($path) . '/';

        // Dir et url css
        if( isset($dependencies['css-dir']) && isset($dependencies['css-url']) ){
            $css_dir  = rtrim( $dependencies['css-dir'], '/') . '/';
            $css_url  = rtrim( $dependencies['css-url'], '/') . '/';
        } else {
            $css_dir = $scss_dir;
            $css_url = substr($url, 0, - strlen($file_name.'.scss') );
        }

        // On renvoie le fichier s'il existe et que le fichier d'appel sass a été modifié
        if( file_exists($css_dir.$file_name.'.css') && filemtime($css_dir.$file_name.'.css') > filemtime($path) )
            return $css_url . $file_name . '.css';

        // On charge la librairie
        require_once ( ABSPATH . INC . '/vendors/scss.inc.php' );

        $scss = new \Leafo\ScssPhp\Compiler();

        // On import le repertoire à la librairie
        $scss->setImportPaths($scss_dir);

        // Mode de compression fichier
        if( isset($dependencies['css-mode']) )
            $mode = strtolower($dependencies['css-mode']);
        else
            $mode = 'nested';
        // Application mode de compression
        switch ($mode) {
            case 'expanded':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Expanded');
                break;
            case 'compressed':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Compressed');
                break;
            case 'compact':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Compact');
                break;
            case 'crunched':
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Crunched');
                break;
            default:
                $scss->setFormatter('Leafo\ScssPhp\Formatter\Nested');
                break;
        }
        // Compilation sass
        $string_css = $scss->compile('@import "'.$file_name.'.scss";');
        // On créer le répertoire
        if( ! is_dir($css_dir) )   @mkdir($css_dir);
        // On enregistre le fichier
        file_put_content( $css_dir . $file_name . '.css', $string_css );
        // On retourne l'url du fichier css compilé
        $url =  $css_url . $file_name . '.css';
    }
    
    return $url;
}

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

    if ( !IMAGIFY ) return;

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

// On ajoute la fonction d'appel de construction du fichier robots
add_action('do_robots' , 'mp_doing_robots');

// On ajoute la fonction d'appel de construction du fichier flux rss
add_action('do_feed' , 'mp_doing_feed');

// On ajoute la fonction d'appel de construction du fichier sitempa.xml
add_action('do_sitemap' , 'mp_doing_sitemap');

// On ajoute la fonction d'appel de construction de l'image favicon
add_action('do_favicon' , 'mp_doing_favicon');


/**
 * Générer un fichier robots.txt
 * @return
 */
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


/**
 * Générer un fichier sitemap 
 * @return
 */
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



/**
 * Générer un flux xml
 * @return
 */
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