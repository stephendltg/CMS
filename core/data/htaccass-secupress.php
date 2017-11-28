# BEGIN SecuPress readme_discloses
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /wordpress/
    RewriteRule ^(.*/)?(readme|changelog)\.(txt|md|html)$ - [R=404,L,NC]
</IfModule>
# END SecuPress

# BEGIN SecuPress bad_url_access
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /wordpress/
    RewriteCond %{REQUEST_URI} !wp-includes/js/tinymce/wp-tinymce\.php$
    RewriteRule ^(php\.ini|wp-config\.php|wp-includes/.+\.php|wp-admin/(admin-functions|install|menu-header|setup-config|([^/]+/)?menu|upgrade-functions|includes/.+)\.php)$ [R=404,L,NC]
</IfModule>
# END SecuPress

# BEGIN SecuPress wp_version
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /wordpress/
    RewriteRule ^readme\.html$ - [R=404,L,NC]
</IfModule>
# END SecuPress

# BEGIN SecuPress no_x_powered_by
<IfModule mod_headers.c>
    Header unset X-Powered-By
</IfModule>
# END SecuPress

# BEGIN SecuPress php_disclosure
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{QUERY_STRING} \=PHP[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12} [NC]
    RewriteRule .* - [F]
</IfModule>
# END SecuPress

# BEGIN SecuPress directory_listing
<IfModule mod_autoindex.c>
    Options -Indexes
</IfModule>
# END SecuPress

# BEGIN SecuPress directory_index
<IfModule mod_dir.c>
    DirectoryIndex index.php index.html index.htm index.cgi index.pl index.xhtml
</IfModule>
# END SecuPress

# BEGIN WordPress
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /wordpress/
RewriteRule ^index\.php$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /wordpress/index.php [L]
</IfModule>

# END WordPress
