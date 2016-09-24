<?php defined('ABSPATH') or die('No direct script access.');
/**
 *
 *
 * @package cms mini POPS
 * @subpackage API REST
 * @version 1
 */


/***********************************************/
/*                 API-REST CLIENT             */
/***********************************************/

/* Accès api-rest */
function mp_remote( $url, $token, $method ='GET', $options = array() ){


    $url = esc_url_raw($url);

    if( empty($url) || is_notin( $method, array('GET', 'POST', 'PUT', 'PATCH', 'DELETE') ) || is_same(strlen($token), 0 ) )
        return false;

    $context = array( 'http' => array('ignore_errors' => true, 'method' => 'GET' , 'header' => array('authorization: '.$token) ) );

    if( is_in( $method, array('POST', 'PUT', 'PATCH', 'DELETE') ) )
        $context['http']['method'] = 'POST';

    if( is_in( $method, array('PUT', 'PATCH') ) ){
        if( !empty($options) && is_array($options) ) $context['http']['content'] = $options;
        else return false;
    }

    return $context;
    $context  = stream_context_create( $context );
    if( !file_get_contents( $url, false, $context ) ) return false;
    return json_decode( $response );
}


/*

Afin de normaliser les noms de domaines, dans un souci d’affordance, nous préconisons d’utiliser uniquement trois sous-domaines pour la production :

API – https://api.{fakecompany}.com
OAuth2 – https://oauth2.{fakecompany}.com/v1/
Portal developer – https://developers.{fakecompany}.com/v1

Nous préconisons de sécuriser votre API via le protocole OAuth2
GET https://www.googleapis.com/drive/v2/files?access_token=1/fFBGRNJru1FQd44AzqT3Zg

SUCCESS
201 Created Indique qu’une ressource a été créé. C’est la réponse typique aux requête PUT et POST, en incluant une en-tête HTTP “Location” vers l’url de la ressource.
202 Accepted    Indique que la requête a été acceptée pour être traitée ultérieurement. C’est la réponse typique à un appel asynchrone (pour une meilleure UX ou de meilleures performances, …).
204 No Content  Indique que la requête a été traitée avec succès, mais qu’il n’y a pas de réponse à retourner. Souvent retourné en réponse à un DELETE.
206 Partial Content

CLIENT ERROR
400 Bad Request Généralement utilisé pour les erreurs d’appels, si aucun autre status ne correspond. On peut distinguer deux types d’erreurs.
Request behaviour error, example
401 Unauthorized    Je ne vous connais pas, dites moi qui vous êtes et je vérifierai vos habilitations.
403 Forbidden   Vous êtes correctement authentifié, mais vous n’êtes pas suffisamment habilité.
404 Not Found   La ressource que vous demandez n’existe pas.
405 Method not allowed  Soit l’appel d’une méthode n’a pas de sens sur cette ressource, soit l’utilisateur n’est pas habilité à réaliser cette appel.
406 Not Acceptable  Rien ne match au Header Accept-* de la requête. Par exemple, vous demandez une ressources XML or la ressources n’est disponnible qu’en Json.

SERVER ERROR
500 Server error    L’appel de la ressource est valide, mais un problème d’exécution est rencontré. Le client ne peut pas réellement faire quoi que ce soit à ce propos. Nous proposons de retourner systématiquement un Status 500.

/*
POST    /sites/$site/menus/new              Create a new navigation menu.
POST    /sites/$site/menus/$menu_id         Update a navigation menu.
GET     /sites/$site/menus/$menu_id         Get a single navigation menu.
GET     /sites/$site/menus                  Get a list of all navigation menus.
POST    /sites/$site/menus/$menu_id/delete  Delete a navigation menu
*/

/***********************************************/
/*       Principe nonce              */
/***********************************************/

/*

//On créer le hook sur client puis lors de la validation d'un formulaire on vérifie que le cookies ezst valide avec le nonce

http://example.com/wp-admin/post.php?post=123&action=trash&_wpnonce=b192fc4204


// variables et fonction utile nonce:

$uid = (int) $user->ID;
$token = wp_get_session_token();
$i = wp_nonce_tick();

function wp_hash($data, $scheme = 'auth') {
    $salt = wp_salt($scheme);
    return hash_hmac('md5', $data, $salt);
}

//créer un nonce: wp_create_nonce($action = -1)
substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );


// vérifier un nonce: wp_verify_nonce( $nonce, $action = -1 )
// Nonce generated 0-12 hours ago
$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'nonce'), -12, 10 );
if ( hash_equals( $expected, $nonce ) ) {
    return 1;
}

// Nonce generated 12-24 hours ago
$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
if ( hash_equals( $expected, $nonce ) ) {
    return 2;
}

*/

/***********************************************/
/*       exemple requet ajax              */
/***********************************************/
/*
exemple requet eajax

$.ajax( {
    url: WP_API_Settings.root + 'wp/v2/posts/1',
    method: 'POST',
    beforeSend: function ( xhr ) {
        xhr.setRequestHeader( 'X-WP-Nonce', WP_API_Settings.nonce );
    },
    data:{
        'title' : 'Hello Moon'
    }
} ).done( function ( response ) {
    console.log( response );
} );
*/

/***********************************************/
/*       exemple requet http              */
/***********************************************/
/*
$headers = array (
    'Authorization' => 'Basic ' . base64_encode( 'admin' . ':' . '12345' ),
);
$url = rest_url( 'wp/v2/posts/1' );

$body = array(
    'title' => 'Hello Gaia'
);

$response = wp_remote_post( $url, array (
    'method'  => 'POST',
    'headers' => $headers,
    'body'    =>  $data
) );
*/

/***********************************************/
/*       Methode GET              */
/***********************************************/

/*

$options  = array (
  'http' =>
  array (
    'ignore_errors' => true,
    'header' =>
    array (
      0 => 'authorization: Bearer YOUR_API_TOKEN',
    ),
  ),
);

$context  = stream_context_create( $options );
$response = file_get_contents(
    'https://public-api.wordpress.com/rest/v1/sites/30434183/user/23',
    false,
    $context
);
$response = json_decode( $response );
*/

/***********************************************/
/*        Methode POST     */
/***********************************************/
/*
$options  = array (
  'http' =>
  array (
    'ignore_errors' => true,
    'method' => 'POST',
    'header' =>
    array (
      0 => 'authorization: Bearer YOUR_API_TOKEN',
    ),
  ),
);

$context  = stream_context_create( $options );
$response = file_get_contents(
    'https://public-api.wordpress.com/rest/v1/sites/82974409/users/1/delete',
    false,
    $context
);
$response = json_decode( $response );
*/

/***********************************************/
/*        Methode upload image     */
/***********************************************/
/*
$options  = array (
  'http' =>
  array (
    'ignore_errors' => true,
    'method' => 'POST',
    'header' =>
    array (
      0 => 'authorization: Bearer YOUR_API_TOKEN',
      1 => 'Content-Type: application/x-www-form-urlencoded',
    ),
    'content' =>
     http_build_query(  array (
        'media_urls' => 'https://s.w.org/about/images/logos/codeispoetry-rgb.png',
        //'media' => '@/path/to/file.jpg' ',
      )),
  ),
);

$context  = stream_context_create( $options );
$response = file_get_contents(
    'https://public-api.wordpress.com/rest/v1.1/sites/82974409/media/new',
    false,
    $context
);

$response = json_decode( $response );

*/

/***********************************************/
/*        Methode new post                       */
/***********************************************/
/*
$options  = array (
  'http' =>
  array (
    'ignore_errors' => true,
    'method' => 'POST',
    'header' =>
    array (
      0 => 'authorization: Bearer YOUR_API_TOKEN',
      1 => 'Content-Type: application/x-www-form-urlencoded',
    ),
    'content' =>
     http_build_query(  array (
        'title' => 'Hello World',
        'content' => 'Hello. I am a test post. I was created by the API',
        'tags' => 'tests',
        'categories' => 'API',
      )),
  ),
);

$context  = stream_context_create( $options );
$response = file_get_contents(
    'https://public-api.wordpress.com/rest/v1.1/sites/82974409/posts/new/',
    false,
    $context
);
$response = json_decode( $response );

*/

/***********************************************/
/*       return                       */
/***********************************************/

/*

{
    "ID": 7,
    "site_ID": 3584907,
    "author": {
        "ID": 5,
        "login": "matt",
        "email": false,
        "name": "Matt",
        "nice_name": "matt",
        "URL": "http:\/\/matt.wordpress.com",
        "avatar_URL": "https:\/\/1.gravatar.com\/avatar\/767fc9c115a1b989744c755db47feb60?s=96&d=retro",
        "profile_URL": "http:\/\/en.gravatar.com\/matt",
        "site_ID": 4
    },
    "date": "2005-09-26T21:43:58+00:00",
    "modified": "2005-09-26T21:43:58+00:00",
    "title": "So should we be, like, blogging and stuff too?",
    "URL": "http:\/\/en.blog.wordpress.com\/2005\/09\/26\/blogging-and-stuff\/",
    "short_URL": "http:\/\/wp.me\/pf2B5-7",
    "content": "<p>It is absolutely is criminal we don’t have an official blog for WordPress.com yet. It was all just a ploy to get you to read my, Donncha, Andy, and Ryan’s blogs. But now that the secret is out, we should start blogging officially, and this is as good a place as any. We’re adding new features nearly every day, so at least we’ll have something to write about. <\/p>\n",
    "excerpt": "<p>It is absolutely is criminal we don’t have an official blog for WordPress.com yet. It was all just a ploy to get you to read my, Donncha, Andy, and Ryan’s blogs. But now that the secret is out, we should start blogging officially, and this is as good a place as any. We’re adding new [&hellip;]<\/p>\n",
    "slug": "blogging-and-stuff",
    "guid": "http:\/\/wordpress.com\/2005\/09\/26\/blogging-and-stuff\/",
    "status": "publish",
    "sticky": false,
    "password": "",
    "parent": false,
    "type": "post",
    "discussion": {
        "comments_open": false,
        "comment_status": "open",
        "pings_open": false,
        "ping_status": "open",
        "comment_count": 8
    },
    "likes_enabled": true,
    "sharing_enabled": true,
    "like_count": 5,
    "i_like": 0,
    "is_reblogged": 0,
    "is_following": 0,
    "global_ID": "3e1080a3f47c8e54dee7ae98e94b1796",
    "featured_image": "",
    "post_thumbnail": null,
    "format": "standard",
    "geo": false,
    "menu_order": 0,
    "page_template": "",
    "publicize_URLs": [],
    "tags": {
        "Introduction": {
            "ID": 885,
            "name": "Introduction",
            "slug": "introduction",
            "description": "",
            "post_count": 2,
            "meta": {
                "links": {
                    "self": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907\/tags\/slug:introduction",
                    "help": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907\/tags\/slug:introduction\/help",
                    "site": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907"
                }
            }
        }
    },
    "categories": {},
    "attachments": {},
    "attachment_count": 0,
    "metadata": false,
    "meta": {
        "links": {
            "self": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907\/posts\/7",
            "help": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907\/posts\/7\/help",
            "site": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907",
            "replies": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907\/posts\/7\/replies\/",
            "likes": "https:\/\/public-api.wordpress.com\/rest\/v1.1\/sites\/3584907\/posts\/7\/likes\/"
        }
    },
    "capabilities": {
        "publish_post": false,
        "delete_post": false,
        "edit_post": false
    }
}

*/
