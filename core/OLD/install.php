<?php

/**
 * CMS :: Installator
 */


define ( 'CMS_INSTALLING' , true );


define( 'ABSPATH', dirname( dirname( __FILE__ ) ) . '/' );


//echo $_SERVER['SERVER_SOFTWARE'];
//echo $_SERVER['HTTP_USER_AGENT'];

//echo strpos($_SERVER['HTTP_USER_AGENT'], 'Safari');

$is_apache = strpos($_SERVER['SERVER_SOFTWARE'], 'Apache');
if ( $is_apache == 0 ){
    echo 'nous sommes sur apache';
}


// Si on lance l'installation
require_once( 'xmldb.php' );

// On vérifie que le fichier config existe et que le cms n'est pas déjà installé
if ( file_exists ( '../config.php') ){
    // Votre cms est installe et on propose un reparation de la table en reconstruisant le fichier config et la table options
}


function ecriredsconfig(){
$handle = fopen( $directory . 'wp-config.php', 'w' );
foreach ( $config_file as $line ) {
    fwrite( $handle, $line );
}
fclose( $handle );

// We set the good rights to the wp-config file
chmod( $directory . 'wp-config.php', 0666 );
}




$dir ='../';

// Get array with the names of all modules compiled and loaded
$php_modules = get_loaded_extensions();

// Get server port
if ($_SERVER["SERVER_PORT"] == "80") $port = ""; else $port = ':'.$_SERVER["SERVER_PORT"];

// Get site URL
$site_url = 'http://'.$_SERVER["SERVER_NAME"].$port.str_replace(array("index.php", "install.php"), "", $_SERVER['PHP_SELF']);

// Replace last slash in site_url
$site_url = rtrim($site_url, '/');

//echo $site_url;


$host  = $_SERVER['HTTP_HOST'];
$uri   = rtrim( dirname( $_SERVER['PHP_SELF'] ) , '/\\' );
//echo $host. $uri;




// Rewrite base
$rewrite_base = str_replace(array("index.php", "install.php"), "", $_SERVER['PHP_SELF']);



// If pressed <Install> button then try to install
if (isset($_POST['install_submit'])) {

    	$post_site_url = isset($_POST['site_url']) ? $_POST['site_url'] : '';
    	$post_site_timezone = isset($_POST['site_timezone']) ? $_POST['site_timezone'] : '';
    	$post_site_title = isset($_POST['site_title']) ? $_POST['site_title'] : '';
    	$post_site_description = isset($_POST['site_description']) ? $_POST['site_description'] : '';
    	$post_site_keywords = isset($_POST['site_keywords']) ? $_POST['site_keywords'] : '';
    	$post_email = isset($_POST['email']) ? $_POST['email'] : '';

    	file_put_contents('config.php', "<?php
    return array(
        'site_url' => '{$post_site_url}',
        'site_charset' => 'UTF-8',
        'site_timezone' => '{$post_site_timezone}',
        'site_theme' => 'default',
        'site_title' => '{$post_site_title}',
        'site_description' => '{$post_site_description}',
        'site_keywords' => '{$post_site_keywords}',
        'email' => '{$post_email}',
        'plugins' => array(
            'markdown',
            'sitemap',
        ),
    );
  		");

      	// Write .htaccess
        $htaccess = file_get_contents('.htaccess');
        $save_htaccess_content = str_replace("/%siteurlhere%/", $rewrite_base, $htaccess);

        $handle = fopen ('.htaccess', "w");
        fwrite($handle, $save_htaccess_content);
        fclose($handle);

        // Installation done :)
        header("location: index.php?install=done");

}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Morfy Installer</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="<?php echo $site_url; ?>/themes/default/assets/css/bootstrap.min.css" rel="stylesheet">
	<style>

	</style>
  </head>
  <body>
    <div class="container">

		<h1 class="text-center">MORFY</h1>

		<div class="step-2 hide">
		<form role="form" method="post">
		  <div class="form-group">
		    <label for="site_title">Site Name</label>
		    <input type="text" name="site_title" class="form-control" id="site_title" placeholder="Enter Site Name" required>
		  </div>
		  <div class="form-group">
		    <label for="site_description">Site Description</label>
		    <input type="text" name="site_description" class="form-control" id="site_description" placeholder="Enter Site Description">
		  </div>
		  <div class="form-group">
		    <label for="site_keywords">Site Keywords</label>
		    <input type="text" name="site_keywords" class="form-control" id="site_keywords" placeholder="Enter Site Keywords">
		  </div>
		  <div class="form-group">
		    <label for="site_url">Site Url</label>
		    <input type="text" name="site_url" class="form-control" id="site_url" placeholder="Enter Site Url" value="<?php echo $site_url; ?>" required>
		  </div>
		  <div class="form-group">
		    <label for="email">Email</label>
		    <input type="text" name="email" class="form-control" id="email" placeholder="Enter Email" required>
		  </div>
	      <div class="form-group">
            <label>Time zone</label>
            <select class="form-control" name="site_timezone">
                <option value="Kwajalein">(GMT-12:00) International Date Line West</option>
                <option value="Pacific/Samoa">(GMT-11:00) Midway Island, Samoa</option>
                <option value="Pacific/Honolulu">(GMT-10:00) Hawaii</option>
                <option value="America/Anchorage">(GMT-09:00) Alaska</option>
                <option value="America/Los_Angeles">(GMT-08:00) Pacific Time (US &amp; Canada)</option>
                <option value="America/Tijuana">(GMT-08:00) Tijuana, Baja California</option>
                <option value="America/Denver">(GMT-07:00) Mountain Time (US &amp; Canada)</option>
                <option value="America/Chihuahua">(GMT-07:00) Chihuahua, La Paz, Mazatlan</option>
                <option value="America/Phoenix">(GMT-07:00) Arizona</option>
                <option value="America/Regina">(GMT-06:00) Saskatchewan</option>
                <option value="America/Tegucigalpa">(GMT-06:00) Central America</option>
                <option value="America/Chicago">(GMT-06:00) Central Time (US &amp; Canada)</option>
                <option value="America/Mexico_City">(GMT-06:00) Guadalajara, Mexico City, Monterrey</option>
                <option value="America/New_York">(GMT-05:00) Eastern Time (US &amp; Canada)</option>
                <option value="America/Bogota">(GMT-05:00) Bogota, Lima, Quito, Rio Branco</option>
                <option value="America/Indiana/Indianapolis">(GMT-05:00) Indiana (East)</option>
                <option value="America/Caracas">(GMT-04:30) Caracas</option>
                <option value="America/Halifax">(GMT-04:00) Atlantic Time (Canada)</option>
                <option value="America/Manaus">(GMT-04:00) Manaus</option>
                <option value="America/Santiago">(GMT-04:00) Santiago</option>
                <option value="America/La_Paz">(GMT-04:00) La Paz</option>
                <option value="America/St_Johns">(GMT-03:30) Newfoundland</option>
                <option value="America/Argentina/Buenos_Aires">(GMT-03:00) Buenos Aires</option>
                <option value="America/Sao_Paulo">(GMT-03:00) Brasilia</option>
                <option value="America/Godthab">(GMT-03:00) Greenland</option>
                <option value="America/Montevideo">(GMT-03:00) Montevideo</option>
                <option value="America/Argentina/Buenos_Aires">(GMT-03:00) Georgetown</option>
                <option value="Atlantic/South_Georgia">(GMT-02:00) Mid-Atlantic</option>
                <option value="Atlantic/Azores">(GMT-01:00) Azores</option>
                <option value="Atlantic/Cape_Verde">(GMT-01:00) Cape Verde Is.</option>
                <option value="Europe/London">(GMT) Greenwich Mean Time : Dublin, Edinburgh, Lisbon, London</option>
                <option value="Atlantic/Reykjavik">(GMT) Monrovia, Reykjavik</option>
                <option value="Africa/Casablanca">(GMT) Casablanca</option>
                <option value="Europe/Belgrade">(GMT+01:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague</option>
                <option value="Europe/Sarajevo">(GMT+01:00) Sarajevo, Skopje, Warsaw, Zagreb</option>
                <option value="Europe/Brussels">(GMT+01:00) Brussels, Copenhagen, Madrid, Paris</option>
                <option value="Africa/Algiers">(GMT+01:00) West Central Africa</option>
                <option value="Europe/Amsterdam">(GMT+01:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna</option>
                <option value="Africa/Cairo">(GMT+02:00) Cairo</option>
                <option value="Europe/Helsinki">(GMT+02:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius</option>
                <option value="Europe/Athens">(GMT+02:00) Athens, Bucharest, Istanbul</option>
                <option value="Asia/Jerusalem">(GMT+02:00) Jerusalem</option>
                <option value="Asia/Amman">(GMT+02:00) Amman</option>
                <option value="Asia/Beirut">(GMT+02:00) Beirut</option>
                <option value="Africa/Windhoek">(GMT+02:00) Windhoek</option>
                <option value="Africa/Harare">(GMT+02:00) Harare, Pretoria</option>
                <option value="Asia/Kuwait">(GMT+03:00) Kuwait, Riyadh</option>
                <option value="Asia/Baghdad">(GMT+03:00) Baghdad</option>
                <option value="Europe/Minsk">(GMT+03:00) Minsk</option>
                <option value="Africa/Nairobi">(GMT+03:00) Nairobi</option>
                <option value="Asia/Tbilisi">(GMT+03:00) Tbilisi</option>
                <option value="Asia/Tehran">(GMT+03:30) Tehran</option>
                <option value="Asia/Muscat">(GMT+04:00) Abu Dhabi, Muscat</option>
                <option value="Asia/Baku">(GMT+04:00) Baku</option>
                <option value="Europe/Moscow">(GMT+04:00) Moscow, St. Petersburg, Volgograd</option>
                <option value="Asia/Yerevan">(GMT+04:00) Yerevan</option>
                <option value="Asia/Karachi">(GMT+05:00) Islamabad, Karachi</option>
                <option value="Asia/Tashkent">(GMT+05:00) Tashkent</option>
                <option value="Asia/Kolkata">(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi</option>
                <option value="Asia/Colombo">(GMT+05:30) Sri Jayawardenepura</option>
                <option value="Asia/Katmandu">(GMT+05:45) Kathmandu</option>
                <option value="Asia/Dhaka">(GMT+06:00) Astana, Dhaka</option>
                <option value="Asia/Yekaterinburg">(GMT+06:00) Ekaterinburg</option>
                <option value="Asia/Rangoon">(GMT+06:30) Yangon (Rangoon)</option>
                <option value="Asia/Novosibirsk">(GMT+07:00) Almaty, Novosibirsk</option>
                <option value="Asia/Bangkok">(GMT+07:00) Bangkok, Hanoi, Jakarta</option>
                <option value="Asia/Beijing">(GMT+08:00) Beijing, Chongqing, Hong Kong, Urumqi</option>
                <option value="Asia/Krasnoyarsk">(GMT+08:00) Krasnoyarsk</option>
                <option value="Asia/Ulaanbaatar">(GMT+08:00) Irkutsk, Ulaan Bataar</option>
                <option value="Asia/Kuala_Lumpur">(GMT+08:00) Kuala Lumpur, Singapore</option>
                <option value="Asia/Taipei">(GMT+08:00) Taipei</option>
                <option value="Australia/Perth">(GMT+08:00) Perth</option>
                <option value="Asia/Seoul">(GMT+09:00) Seoul</option>
                <option value="Asia/Tokyo">(GMT+09:00) Osaka, Sapporo, Tokyo</option>
                <option value="Australia/Darwin">(GMT+09:30) Darwin</option>
                <option value="Australia/Adelaide">(GMT+09:30) Adelaide</option>
                <option value="Australia/Sydney">(GMT+10:00) Canberra, Melbourne, Sydney</option>
                <option value="Australia/Brisbane">(GMT+10:00) Brisbane</option>
                <option value="Australia/Hobart">(GMT+10:00) Hobart</option>
                <option value="Asia/Yakutsk">(GMT+10:00) Yakutsk</option>
                <option value="Pacific/Guam">(GMT+10:00) Guam, Port Moresby</option>
                <option value="Asia/Vladivostok">(GMT+11:00) Vladivostok</option>
                <option value="Pacific/Fiji">(GMT+12:00) Fiji, Kamchatka, Marshall Is.</option>
                <option value="Asia/Magadan">(GMT+12:00) Magadan, Solomon Is., New Caledonia</option>
                <option value="Pacific/Auckland">(GMT+12:00) Auckland, Wellington</option>
                <option value="Pacific/Tongatapu">(GMT+13:00) Nukualofa</option>
            </select>
	       </div>
		  <input type="submit" name="install_submit" class="btn btn-primary form-control" value="Install">
		</form>
		</div>
    </div>
  </body>
</html>
