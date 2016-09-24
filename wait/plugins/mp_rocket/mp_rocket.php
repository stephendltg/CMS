<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage optimizer
 * @version 1
 */

// On optimise le rendu html
if ( apply_filter( 'do_optimize', true ) && get_option('optimize->files->html') ) {

    // Add filter optimisation HTML
    add_action('TEMPLATE_REDIRECT', function(){ ob_start('mp_minify_html'); } , PHP_INT_MAX );

}

// On optimise le chargement des images
if ( apply_filter( 'do_optimize', true ) && get_option('optimize->lazyload->images') ) {

    // Add filter lazyload_script
    add_action('mp_head', 'mp_lazyload', PHP_INT_MAX);

    // On ajouter un filter pour the_content et modifier les images et rendre lazy load actif
    add_action('TEMPLATE_REDIRECT', function(){ ob_start('mp_lazyload_images'); } , 1 );

}

// On optimise les feuilles de styles css
if ( apply_filter( 'do_optimize', true ) && get_option('optimize->files->css') ) {

    // Add filter pour enqueue inline style
    add_filter('mp_inline_styles', 'mp_easy_minify');

    // Add filter pour preparer la concetanation des fichiers css
    add_filter('mp_enqueue_style_link', 'mp_prepare_concatenate', 10, 2 );

    // Add filter pour la concetanation des fichiers css
    add_filter('mp_enqueue_styles', 'mp_concatenate_css', 10, 2 );

}

// On optimise les script js
if ( apply_filter( 'do_optimize', true ) && get_option('optimize->files->js') ) {

    // Add filter pour preparer la concetanation des fichiers js
    add_filter('mp_enqueue_script_link', 'mp_prepare_concatenate', 10, 2 );

    // Add filter pour la concetanation des fichiers js
    add_filter('mp_enqueue_scripts', 'mp_concatenate_js', 10, 2 );

    //On minifie le fichier de combination
    add_filter('mp_before_concatenate', 'mp_easy_minify');

}


/***********************************************/
/*                  lazy_load                  */
/***********************************************/

function mp_lazyload(){
    echo '<script type="text/javascript">(function(a,e){function f(){var d=0;if(e.body&&e.body.offsetWidth){d=e.body.offsetHeight}if(e.compatMode=="CSS1Compat"&&e.documentElement&&e.documentElement.offsetWidth){d=e.documentElement.offsetHeight}if(a.innerWidth&&a.innerHeight){d=a.innerHeight}return d}function b(g){var d=ot=0;if(g.offsetParent){do{d+=g.offsetLeft;ot+=g.offsetTop}while(g=g.offsetParent)}return{left:d,top:ot}}function c(){var l=e.querySelectorAll("[data-lazy-original]");var j=a.pageYOffset||e.documentElement.scrollTop||e.body.scrollTop;var d=f();for(var k=0;k<l.length;k++){var h=l[k];var g=b(h).top;if(g<(d+j)){h.src=h.getAttribute("data-lazy-original");h.removeAttribute("data-lazy-original")}}}if(a.addEventListener){a.addEventListener("DOMContentLoaded",c,false);a.addEventListener("scroll",c,false)}else{a.attachEvent("onload",c);a.attachEvent("onscroll",c)}})(window,document);</script>';
}

function mp_lazyload_images( $html ) {

    $lazyload_replace_callback = function($matches) {
        return sprintf( '<img%1$s src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==" data-lazy-original=%2$s%3$s><noscript><img%1$s src=%2$s%3$s></noscript>', $matches[1], $matches[2], $matches[3] );
    };

    return preg_replace_callback( '#<img([^>]*) src=("(?:[^"]+)"|\'(?:[^\']+)\'|(?:[^ >]+))([^>]*)>#', $lazyload_replace_callback, $html );
}


/***********************************************/
/*                  js/css Minify              */
/***********************************************/
// Minifie js et css simplement
function mp_easy_minify( $str, $comments = true ){

    $str = apply_filter('pre_mp_easy_minify', $str);

    // On enlève les commentaires
    if($comments)
        $str = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str );

    /* remove tabs, spaces, newlines, etc. */
    $str = str_replace(array("\r\n", "\r", "\n", "\t"), '', $str);
    while ( stristr($str, '  ') )
        $str = str_replace('  ', ' ', $str);

    return apply_filter('mp_easy_minify', $str);
}


/***********************************************/
/*                  html Minify                */
/***********************************************/

function mp_minify_html($html){

    $html = apply_filter('pre_mp_minify_html', $html);

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
            // On supprimer les commentaires seulement s'il ne sont pas dans un textaera
            if($raw_tag != 'textarea')
                $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);

        }
        else {

            // On minifie le contenu seulement s'il n'appartient pas à pre ou textaera
            if( $tag == 'pre' || $tag == 'textarea' )
                $raw_tag = $tag;
            else if( $tag == '/pre' || $tag == '/textarea' )
                $raw_tag = false;
            else {

                if ( $raw_tag )
                    $strip = false;
                else {
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

    return apply_filter('mp_minify_html', $html);
}


/***********************************************/
/*        Concate file                         */
/***********************************************/

function mp_prepare_concatenate( $link, $data ){

    global $concatenate;

    extract($data);

    // On force $media pour les script
    if(!isset($media)) $media ='all';

    // On créer la table à combiner selon l'emplacement
    if( $cache && is_size($before, 0) )
        $concatenate[$handled] = array('path' => $path, 'source' => $source, 'filetime' => $filetime, 'media' => $media );

    return $link;
}



/***********************************************/
/*        Concate css                          */
/***********************************************/
function mp_concatenate_css($enqueue, $footer, $type = 'css'){

    global $concatenate;

    // Si pas de fichier script ou style : on stop la concatenate
    if( empty($concatenate) ) return;

    // On nomme un repertoire pour les fichiers combination soit dans le footer soit dans le header
    $footer_hash = substr( base64_encode(CONTENT_DIR).(string)$footer, -12, 10 );

    // On cherche si un fichier de combination a été créé
    $files = glob( CONTENT_DIR.'/assets/'.$footer_hash.'/*.'.$type );

    // Si un fichier combination créé on affecte le fichier
    if( is_size($files,1) )
        $file_concatenate = $files[0];

    // Si plusieurs fichiers de combination existe, on les supprime tous afin de ne pas surcharger le disque de fichiers inutile
    if( is_sup($files, 1) )
        foreach ($files as $file) @unlink($file);

    // On definit le schema selon le type de combination css/js
    if(is_same($type,'css'))
        $scheme   = '<link rel="stylesheet" type="text/css" href="%1$s">'."\n";
    else
        $scheme   = '<link rel="javascript" type="text/javascript" href="%1$s">'."\n";

    // Les handles combinés qui servirons à la création du nom du fichier de combination
    $handleds = '';

    // Si le fichier combiné existe
    if( isset($file_concatenate) ){

        $lasttime_concatenate = filemtime($file_concatenate);
        $raw_file = false;
        $valid_handleds = '';

        foreach ($concatenate as $handled => $data){

            // On check si un fichier a été modifié
            if( $data['filetime'] > $lasttime_concatenate )
                $raw_file = true;

            // On vérifie si la liste des handleds ont été modifiés
            $valid_handleds .= $handled ;
        }

        // On hash les handles valid qui serve de nom au fichier de combination
        $valid_handleds = substr( md5($valid_handleds), -12, 10 );

        if(!$raw_file && is_same( $valid_handleds.'.css', basename($file_concatenate) ) ){

            // On dequeue les handleds combinés
            foreach ($concatenate as $handled => $data)
                unset($enqueue[$handled]);

            // On purge la table concatenate pour éviter doublon
            $concatenate = array();

            // On récupère l'url du fichier de combination
            $file_concatenate_url = rel2abs(str_replace(ABSPATH ,'' ,$file_concatenate) );

            // On enqueue le fichier de combination
            $enqueue[] = sprintf( $scheme, $file_concatenate_url );
            return $enqueue;
        }

    }

    // On supprime le fichier de combination s'il existe afin de la mettre à jour
    @unlink($file_concatenate);

    // Contenu du fichier de combination
    $file_content = '';

    // Si le fichier combiné n'existe pas on le créé
    foreach ($concatenate as $handled => $data){

        // On liste les fichiers handleds pour le nom du fichier de combination
        $handleds  .= $handled;

        // On traite le contenu du fichier de combination
        $content    = file_get_contents( $data['path'] );

        // On remplace les url relative en absolu
        $content    = preg_replace_callback(
                '#url\s*\(\s*[\'"]?([^\'"\)]+)[\'"]\s*\)#',
                function($matches) use ($data) { return 'url("'. rel2abs( $matches [1], $data['source'] ) . '")'; },
                $content
               );

        // On traite les medias css particuliers
        $before        = is_different($data['media'],'all') ? '@media '.$data['media'].'{'."\n" : '';
        $after         = !empty($before) ? "\n".'}' : '';
        $content       = $before.$content.$after;
        $file_content .= $content;
    }

    // Nom du fichier de combination
    $handleds = substr( md5($handleds), -12, 10 );

    // On créé le fichier de combination
    @mkdir( CONTENT_DIR.'/assets/'.$footer_hash, 0755, true );

    $file_content = apply_filter('mp_before_concatenate', $file_content);

    // Si le fichier est bien créer on l'enqueue
    if( file_put_contents( CONTENT_DIR.'/assets/'.$footer_hash.'/'.$handleds.'.css', $file_content ) ){

        unset($file);

        // On dequeue les handleds combinés
        foreach ($concatenate as $handled => $data)
            unset($enqueue[$handled]);

        // On récupère l'url du fichier de combination
        $file_concatenate_url = rel2abs(str_replace(ABSPATH ,'' ,CONTENT_DIR.'/assets/'.$footer_hash.'/'.$handleds.'.'.$type) );

        // On enqueue le fichier de combination
        $enqueue[] = sprintf( $scheme, $file_concatenate_url );
    }

    // On purge la table concatenate pour éviter les doublons
    $concatenate = array();

    return $enqueue;
}

/***********************************************/
/*        Concate js                           */
/***********************************************/

function mp_concatenate_js($enqueue, $footer){
    mp_concatenate_css($enqueue, 'js');
}
