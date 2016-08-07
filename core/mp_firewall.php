<?php defined('ABSPATH') or die('No direct script access.');

/**
 * Fonction Firewall
 *
 *
 * @package cms mini POPS
 * @subpackage Firewall
 * @version 1
 */


/*
 $update_url = 'https://raw.github.com/ai/autoprefixer-rails/master/vendor/autoprefixer.js';
        $local_path = __DIR__ . '/vendor/autoprefixer.js';
        $new = file_get_contents($update_url);
        $old = file_get_contents($local_path);
       
        if (md5($new) == md5($old)) return false;
       
        file_put_contents($local_path, $new);
        return true;
*/

/*

6G:[QUERY STRINGS]

Cette partie vérifie que l'URL demandée par le client n'a pas été faite pour profiter de failles sur votre serveur web ou votre code PHP. Si c'est le cas, il va interdire l'accès à la page grace à (RewriteRule .* - [F]), ou le [F] signifie que l'accès n'est pas autorisé.

6G: [REQUEST METHOD]

Cette partie teste les méthodes HTTP envoyées. Les navigateurs ne prenant en charge que GET et POST, toutes les autres se retrouvent bloquées avec la même méthode que le bloc précédent.

6G:[REFERRERS]

Ce bloc est là pour bloquer le trafique provenant de certains referers (c'est-à-dire les referents, les sites d'où proviennent les visiteurs). Si vos êtes soumis a dû référer spam, c'est à cet endroit que vous pourrez lister les adresses de spammeur.

6G:[REQUEST STRINGS]

Ce bloc est là pour bloquer les appels les plus fréquents fait pas des Bots essayant de déterminer le type de site que vous possédez.

6G:[USER AGENTS]

Cette ligne bloque les bots dont le nom est dans la grande liste que vous pouvez voir.

Notez la présence de archive.org en tout premier. J'ai personnellement autorise ce bot, car je trouve que la présence d'une copie de son site sur la waybackmachine n'est pas un mal, au contraire.

6G:[BAD IPS]

Enfin, ce bloc actuellement vide vous permet de bloquer des ips spécifiques. Si vous subissez les assauts d'un bot ou une tentative de DDOS depuis une IP, c'est à cet endroit qu'il faudra l'inserer !

*/

/***********************************************/
/*          Functions Firewall                 */
/***********************************************/

function mp_firewall_rules(){

    $firewall = get_option('security->firewall->active', true);

    if ($firewall === 'enable' || $firewall === 'disable')
        return;

    if( !$is_apache || !$is_mod_rewrite )
        $firewall = false;

    // On modifie le fichier htaccess si le mode rewrite n'est pas active et que nous sommes sur serveur apache
    if ( $firewall === true ) {

        $firewall = 'enable';

        $rules = 'etc...';

    } else {

        $firewall = 'disable';
        $rules = "# BEGIN miniPops\n# END miniPops";
    }

    if ( file_exists( ABSPATH . '.htaccess' ) ) {
        $rule = file_get_contents( ABSPATH . '.htaccess' );
        $marker_begin =  strpos( $rule , '# BEGIN miniPops') ;
        $marker_end =  strpos( $rule , '# END miniPops') + strlen('# END miniPops') ;
        $rules = substr_replace( $rule , $rules , $marker_begin , $marker_end );
    }

    if ( !file_put_contents( ABSPATH . '.htaccess', $rules ) ) 
        $firewall = 'disable';

    update_option('security->firewall->active', $firewall);

}

if( get_option('security->firewall->active') === true ){

    $bad_ips = get_option('security->firewall->bad_ips');
    $bad_user_agents = get_option('security->firewall->bad_user_agents');
    $bad_referrers = get_option('security->firewall->bad_referrers';)


$rules = <<<EOT
# BEGIN SECURITY

# protect the htaccess file
<files .htaccess>
    order allow,deny
    deny from all
</files>

# Disable directory listing
Options -Indexes

# XSS Protection & iFrame Protection & Mime Security
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header always append X-Frame-Options SAMEORIGIN /* DENY, SAMEORIGIN */
    Header set X-Content-Type-Options nosniff
</IfModule>

# 6G FIREWALL/BLACKLIST
# @ https://perishablepress.com/6g/

# 6G:[QUERY STRINGS]
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{QUERY_STRING} (eval\() [NC,OR]
    RewriteCond %{QUERY_STRING} (127\.0\.0\.1) [NC,OR]
    RewriteCond %{QUERY_STRING} ([a-z0-9]{2000}) [NC,OR]
    RewriteCond %{QUERY_STRING} (javascript:)(.*)(;) [NC,OR]
    RewriteCond %{QUERY_STRING} (base64_encode)(.*)(\() [NC,OR]
    RewriteCond %{QUERY_STRING} (GLOBALS|REQUEST)(=|\[|%) [NC,OR]
    RewriteCond %{QUERY_STRING} (<|%3C)(.*)script(.*)(>|%3) [NC,OR]
    RewriteCond %{QUERY_STRING} (\\|\.\.\.|\.\./|~|`|<|>|\|) [NC,OR]
    RewriteCond %{QUERY_STRING} (boot\.ini|etc/passwd|self/environ) [NC,OR]
    RewriteCond %{QUERY_STRING} (thumbs?(_editor|open)?|tim(thumb)?)\.php [NC,OR]
    RewriteCond %{QUERY_STRING} (\'|\")(.*)(drop|insert|md5|select|union) [NC]
    RewriteRule .* - [F]
</IfModule>

# 6G:[REQUEST METHOD]
<IfModule mod_rewrite.c>
    RewriteCond %{REQUEST_METHOD} ^(connect|debug|delete|move|put|trace|track) [NC]
    RewriteRule .* - [F]
</IfModule>

# 6G:[REFERRERS]
<IfModule mod_rewrite.c>
    RewriteCond %{HTTP_REFERER} ([a-z0-9]{2000}) [NC,OR]
    RewriteCond %{HTTP_REFERER} (semalt.com|todaperfeita) [NC]
    RewriteRule .* - [F]
</IfModule>

# 6G:[REQUEST STRINGS]
<IfModule mod_alias.c>
    RedirectMatch 403 (?i)([a-z0-9]{2000})
    RedirectMatch 403 (?i)(https?|ftp|php):/
    RedirectMatch 403 (?i)(base64_encode)(.*)(\()
    RedirectMatch 403 (?i)(=\\\'|=\\%27|/\\\'/?)\.
    RedirectMatch 403 (?i)/(\$(\&)?|\*|\"|\.|,|&|&amp;?)/?$
    RedirectMatch 403 (?i)(\{0\}|\(/\(|\.\.\.|\+\+\+|\\\"\\\")
    RedirectMatch 403 (?i)(~|`|<|>|:|;|,|%|\\|\s|\{|\}|\[|\]|\|)
    RedirectMatch 403 (?i)/(=|\$&|_mm|cgi-|etc/passwd|muieblack)
    RedirectMatch 403 (?i)(&pws=0|_vti_|\(null\)|\{\$itemURL\}|echo(.*)kae|etc/passwd|eval\(|self/environ)
    RedirectMatch 403 (?i)\.(aspx?|bash|bak?|cfg|cgi|dll|exe|git|hg|ini|jsp|log|mdb|out|sql|svn|swp|tar|rar|rdf)$
    RedirectMatch 403 (?i)/(^$|(mp-)?config|mobiquo|phpinfo|shell|sqlpatch|thumb|thumb_editor|thumbopen|timthumb|webshell)\.php
</IfModule>

# 6G:[USER AGENTS]
<IfModule mod_setenvif.c>
    SetEnvIfNoCase User-Agent ([a-z0-9]{2000}) bad_bot
    SetEnvIfNoCase User-Agent (archive.org|binlar|casper|checkpriv|choppy|clshttp|cmsworld|diavol|dotbot|extract|feedfinder|flicky|g00g1e|harvest|heritrix|httrack|kmccrew|loader|miner|nikto|nutch|planetwork|postrank|purebot|pycurl|python|seekerspider|siclab|skygrid|sqlmap|sucker|turnit|vikspider|winhttp|xxxyy|youda|zmeu|zune) bad_bot
    <limit GET POST PUT>
        Order Allow,Deny
        Allow from All
        Deny from env=bad_bot
    </limit>
</IfModule>

# 6G:[BAD IPS]
<Limit GET HEAD OPTIONS POST PUT>
    Order Allow,Deny
    Allow from All
    # uncomment/edit/repeat next line to block IPs
    # Deny from 123.456.789
</Limit>

# END SECURITY
EOT;

_echo($rules,1);

}

//_echo($rules,1);
