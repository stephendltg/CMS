<?php defined('ABSPATH') or die('No direct script access.');

/**
 * @package cms mini POPS
 * @subpackage firewall
 * @version 1
 */


function mp_dies( $arg = array() ){

	$args = parse_args( $args, array( 
        'message'  => 'Service Unavailable !',
        'subtitle' => 'Error',
        'type' 	   => 'Error',
        'http_response_code' => 404) 
    );

	// Error
	_doing_it_wrong('__FILE__', date('j-m-Y H:i:s') .' | '. $args['type'] . ' | IP: ' . get_ip_client() . ' | DNS: ' . gethostbyaddr( get_ip_client() ) . ' | Agent: '.PHP_FIREWALL_USER_AGENT . ' | URL: ' . PHP_FIREWALL_REQUEST_URI . ' | Referer: ' . PHP_FIREWALL_GET_REFERER );

	// Hook filter
	$function = apply_filters( 'wp_die_handler', 'cms_maintenance' );

	// On lance l'action
	call_user_func_array( $function, $args );
}



/***********************************************/
/*          Load vars global                   */
/***********************************************/

// IP Client
mp_cache_data('get_ip_client', get_ip_client() );
mp_cache_data('ip_client_array', explode('.', mp_cache_data('get_ip_client') ) ); 
mp_cache_data('ip_client', join('.', array_slice(mp_cache_data('ip_client_array'), 0,2) ) );

// gethostbyaddr
mp_cache_data('gethostbyaddr', @gethostbyaddr( mp_cache_data('get_ip_client') ) );



/***********************************************/
/*          PROTECTION CONTRE SERVEUR          */
/***********************************************/

if ( stristr( mp_cache_data('gethostbyaddr') ,'ovh') )
	die( 'message=Protection OVH Server active, this IP range is not allowed !&type=OVH Server list' );

if ( stristr( mp_cache_data('gethostbyaddr') ,'kimsufi') )
	die( 'message=Protection OVH Server active, this IP range is not allowed !&type=KIMSUFI Server list' );

if ( stristr( mp_cache_data('gethostbyaddr') ,'dedibox') )
	die( 'message=Protection OVH Server active, this IP range is not allowed !&type=DEDIBOX Server list' );

if ( stristr( mp_cache_data('gethostbyaddr') ,'digicube') )
	die( 'message=Protection DIGICUBE Server active, this IP range is not allowed !&type=DIGICUBE Server list' );

/* Protection ip serveur */
switch ( mp_cache_data('ip_client') ) {

	case '87.98':
	case '91.121':
	case '94.23':
	case '213.186':
	case '213.251':
		die( 'message=Protection OVH Server active, this IP range is not allowed !&type=OVH Server list' );
		break;

	case '88.191':
		die( 'message=Protection DEDIBOX Server active, this IP range is not allowed !&type=DEDIBOX Server list' );
		break;

	case '95.130':
		die( 'message=Protection DIGICUBE Server active, this IP range is not allowed !&type=DIGICUBE Server list' );
		break;
	default:
		break;
}




/***********************************************/
/*          PROTECTION IP SPAM 	               */
/***********************************************/
$ip_array = array('24', '186', '189', '190', '200', '201', '202', '209', '212', '213', '217', '222' );
if ( in_array( mp_cache_data('ip_client_array')[0], $ip_array ) )
	die( 'message=Protection died IPs active, this IP range is not allowed !&type=IPs Spam list' );


/***********************************************/
/*          PROTECTION IP DENY 	               */
/***********************************************/

$ip_array = array('0', '1', '2', '5', '10', '14', '23', '27', '31', '36', '37', '39', '42', '46', '49', '50', '100', '101', '102', '103', '104', '105', '106', '107', '114', '172', '176', '177', '179', '181', '185', '192', '223', '224' );
if ( in_array( mp_cache_data('ip_client_array')[0], $ip_array ) )
	die( 'message=Protection died IPs active, this IP range is not allowed !&type=IPs reserved list' );


/***********************************************/
/*          PROTECTION COOKIE, POST, GET       */
/***********************************************/

$ct_rules = Array('applet', 'base', 'bgsound', 'blink', 'embed', 'expression', 'frame', 'javascript', 'layer', 'link', 'meta', 'object', 'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload', 'script', 'style', 'title', 'vbscript', 'xml');

foreach($_COOKIE as $value) {

	$check = str_replace($ct_rules, '*', $value);
	if( $value != $check )			
		unset( $value );
}

foreach( $_POST as $value ) {

	$check = str_replace($ct_rules, '*', $value);
	if( $value != $check )
		unset( $value );
}

foreach( $_GET as $value ) {

	$check = str_replace($ct_rules, '*', $value);
	if( $value != $check )
	unset( $value );			
}


/***********************************************/
/*          PROTECTION URL 				       */
/***********************************************/



$ct_rules = array( 'absolute_path', 'ad_click', 'alert(', 'alert%20', ' and ', 'basepath', 'bash_history', '.bash_history', 'cgi-', 'chmod(', 'chmod%20', '%20chmod', 'chmod=', 'chown%20', 'chgrp%20', 'chown(', '/chown', 'chgrp(', 'chr(', 'chr=', 'chr%20', '%20chr', 'chunked', 'cookie=', 'cmd', 'cmd=', '%20cmd', 'cmd%20', '.conf', 'configdir', 'config.php', 'cp%20', '%20cp', 'cp(', 'diff%20', 'dat?', 'db_mysql.inc', 'document.location', 'document.cookie', 'drop%20', 'echr(', '%20echr', 'echr%20', 'echr=', '}else{', '.eml', 'esystem(', 'esystem%20', '.exe',  'exploit', 'file\://', 'fopen', 'fwrite', '~ftp', 'ftp:', 'ftp.exe', 'getenv', '%20getenv', 'getenv%20', 'getenv(', 'grep%20', '_global', 'global_', 'global[', 'http:', '_globals', 'globals_', 'globals[', 'grep(', 'g\+\+', 'halt%20', '.history', '?hl=', '.htpasswd', 'http_', 'http-equiv', 'http/1.', 'http_php', 'http_user_agent', 'http_host', '&icq', 'if{', 'if%20{', 'img src', 'img%20src', '.inc.php', '.inc', 'insert%20into', 'ISO-8859-1', 'ISO-', 'javascript\://', '.jsp', '.js', 'kill%20', 'kill(', 'killall', '%20like', 'like%20', 'locate%20', 'locate(', 'lsof%20', 'mdir%20', '%20mdir', 'mdir(', 'mcd%20', 'motd%20', 'mrd%20', 'rm%20', '%20mcd', '%20mrd', 'mcd(', 'mrd(', 'mcd=', 'mod_gzip_status', 'modules/', 'mrd=', 'mv%20', 'nc.exe', 'new_password', 'nigga(', '%20nigga', 'nigga%20', '~nobody', 'org.apache', '+outfile+', '%20outfile%20', '*/outfile/*',' outfile ','outfile', 'password=', 'passwd%20', '%20passwd', 'passwd(', 'phpadmin', 'perl%20', '/perl', 'phpbb_root_path','*/phpbb_root_path/*','p0hh', 'ping%20', '.pl', 'powerdown%20', 'rm(', '%20rm', 'rmdir%20', 'mv(', 'rmdir(', 'phpinfo()', '<?php', 'reboot%20', '/robot.txt' , '~root', 'root_path', 'rush=', '%20and%20', '%20xorg%20', '%20rush', 'rush%20', 'secure_site, ok', 'select%20', 'select from', 'select%20from', '_server', 'server_', 'server[', 'server-info', 'server-status', 'servlet', 'sql=', '<script', '<script>', '</script','script>','/script', 'switch{','switch%20{', '.system', 'system(', 'telnet%20', 'traceroute%20', '.txt', 'union%20', '%20union', 'union(', 'union=', 'vi(', 'vi%20', 'wget', 'wget%20', '%20wget', 'wget(', 'window.open', 'wwwacl', ' xor ', 'xp_enumdsn', 'xp_availablemedia', 'xp_filelist', 'xp_cmdshell', '$_request', '$_get', '$request', '$get',  '&aim', '/etc/password','/etc/shadow', '/etc/groups', '/etc/gshadow', '/bin/ps', 'uname\x20-a', '/usr/bin/id', '/bin/echo', '/bin/kill', '/bin/', '/chgrp', '/usr/bin', 'bin/python', 'bin/tclsh', 'bin/nasm', '/usr/x11r6/bin/xterm', '/bin/mail', '/etc/passwd', '/home/ftp', '/home/www', '/servlet/con', '?>', '.txt');


/***********************************************/
/*          PROTECTION USER AGENTS 		       */
/***********************************************/

add_action( 'muplugins_loaded', 'mp_block_bad_user_agents', 0 );

/**
 * Filter the user agent to block it or not
 *
 * @since 1.0
 * @since 1.1.4 The user-agents match is case sensitive.
 * @since 1.3.1 Remove empty user agent blocking
 */
function mp_block_bad_user_agents() {

	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? trim( $_SERVER['HTTP_USER_AGENT'] ) : '';

	if ( strip_all_tags( $user_agent ) !== $user_agent ) {
		die( 'UAHT' );
	}

	$bad_user_agents = 'ADSARobot, ah-ha, aktuelles, almaden, amzn_assoc, Anarchie, ASPSeek, ASSORT, ATHENS, Atomz, attach, autoemailspider, BackWeb, Bandit, BatchFTP, bdfetch, big.brother, BlackWidow, bmclient, Boston Project, Bot mailto:craftbot@yahoo.com, BravoBrian SpiderEngine MarcoPolo, Buddy, Bullseye, bumblebee, capture, CherryPicker, ChinaClaw, CICC, clipping, Collector, Copier, Crescent, Crescent Internet ToolPak, Custo, cyberalert, DA$, Deweb, diagem, Digger, Digimarc, DIIbot, DISCo, DISCoFinder, DISCo Pump, Download Demon, Downloader, Download Wonder, Drip, DSurf15a, DTS.Agent, EasyDL, eCatch, ecollector, efp@gmx.net, EirGrabber, EmailCollector, Email Extractor, EmailSiphon, EmailWolf, Express WebPictures, ExtractorPro, EyeNetIE, fastlwspider, FavOrg, Favorites Sweeper, FEZhead, FileHound, FlashGet WebWasher, FlickBot, fluffy, FrontPage, GalaxyBot, Gecko/2009032609 Firefox, Generic, Getleft, GetRight, GetSmart, GetWeb!, GetWebPage, gigabaz, Girafabot, Go!Zilla, Go-Ahead-Got-It, GornKer, gotit, Grabber, GrabNet, Grafula, Green Research, grub-client, Harvest, hhjhj@yahoo, hloader, HMView, HomePageSearch, httpdown, http generic, HTTrack, httrack, ia_archiver, IBM_Planetwide, imagefetch, Image Stripper, Image Sucker, IncyWincy, Indy*Library, Indy Library, informant, Ingelin, InterGET, InternetLinkagent, Internet Ninja, InternetSeer.com, Iria, Irvine, JBH*agent, JetCar, JOC, JOC Web Spider, JustView, KWebGet, Lachesis, larbin, LeechFTP, LexiBot, lftp, libwww, likse, Link*Sleuth, LINKS ARoMATIZED, LinkWalker, LWP, lwp-trivial, Mac Finder, Mag-Net, Magnet, Mass Downloader, MCspider, Memo, Microsoft.URL, MIDown tool, Mirror, Missigua Locator, Mister PiX, MMMtoCrawl/UrlDispatcherLLL, Mozilla*MSIECrawler, Mozilla.*Indy, Mozilla.*NEWT, MSFrontPage, MS FrontPage*, MSIECrawler, MSProxy, multithreaddb, nationaldirectory, Navroad, NearSite, NetAnts, NetCarta, NetMechanic, netprospector, NetResearchServer, NetSpider, Net Vampire, NetZIP, NetZip Downloader, NetZippy, NEWT, NICErsPRO, Ninja, NPBot, Octopus, Offline Explorer, Offline Navigator, OpaL, Openfind, OpenTextSiteCrawler, PackRat, PageGrabber, Papa Foto, pavuk, pcBrowser, PersonaPilot, PingALink, Pockey, psbot, PSurf, puf, Pump, PushSite, QRVA, RealDownload, Reaper, Recorder, ReGet, replacer, RepoMonkey, Robozilla, Rover, RPT-HTTPClient, Rsync, Scooter, SearchExpress, searchhippo, searchterms.it, Second Street Research, Seeker, Shai, Siphon, sitecheck, sitecheck.internetseer.com, SiteSnagger, SlySearch, SmartDownload, snagger, Snake, SpaceBison, Spegla, SpiderBot, sproose, SqWorm, Stripper, Sucker, SuperBot, SuperHTTP, Surfbot, SurfWalker, Szukacz, tAkeOut, tarspider, Teleport Pro, Templeton, TrueRobot, TV33_Mercator, UIowaCrawler, URLSpiderPro, URL_Spider_Pro, UtilMind, Vacuum, vagabondo, vayala, visibilitygap, VoidEYE, vspider, w3mir, web.by.mail, WebAuto, WebBandit, Webclipping, webcollage, webcollector, WebCopier, webcraft@bea, Web Data Extractor, webdevil, webdownloader, Web Downloader, Webdup, WebEMailExtrac, WebFetch, WebGo IS, WebHook, Web Image Collector, Webinator, WebLeacher, WEBMASTERS, WebMiner, WebMirror, webmole, WebReaper, WebSauger, Website, Website eXtractor, Website Quester, WebSnake, Webster, WebStripper, Web Sucker, websucker, webvac, webwalk, webweasel, WebWhacker, WebZIP, Wget, Whacker, whizbang, WhosTalking, Widow, WISEbot, WUMPUS, Wweb, WWWOFFLE, x-Tractor, Xenu, XGET, Zeus, Zeus.*Webster, ^Mozilla$, ^Xaldon WebSpider';


	if ( ! empty( $bad_user_agents ) ) {
		$bad_user_agents = preg_replace( '/\s*,\s*/', '|', addcslashes( $bad_user_agents, '/' ) );
		$bad_user_agents = trim( $bad_user_agents, '| ' );

		while ( false !== strpos( $bad_user_agents, '||' ) ) {
			$bad_user_agents = str_replace( '||', '|', $bad_user_agents );
		}
	}

	// Shellshock.
	$bad_user_agents .= ( $bad_user_agents ? '|' : '' ) . '\(.*?\)\s*\{.*?;\s*\}\s*;';

	if ( preg_match( '/' . $bad_user_agents . '/', $user_agent ) ) {
		die( 'UAHB' );
	}

}



/***********************************************/
/*          PROTECTION BAD URL   		       */
/***********************************************/

add_action( 'muplugins_loaded', 'mp_block_bad_url_contents', 0 );

/**
 * Filter the query string to block the request or not
 *
 * @since 1.0
 */
function mp_block_bad_url_contents() {

	if ( empty( $_SERVER['QUERY_STRING'] ) ) {
		return;
	}

	$bad_url_contents = '%%30%30, %00, ../, .ini, 127.0.0.1, AND%201=, AND+1=, AND 1=, base64_decode, base64_encode, etc/passwd, eval(, GLOBALS[, information_schema, input_file, javascript:, REQUEST[, UNION%20ALL%20SELECT, UNION%20SELECT, UNION+ALL+SELECT, UNION+SELECT, UNION ALL SELECT, UNION SELECT, mp-config.php';

	if ( ! empty( $bad_url_contents ) ) {
		$bad_url_contents = preg_replace( '/\s*,\s*/', '|', preg_quote( $bad_url_contents, '/' ) );
		$bad_url_contents = trim( $bad_url_contents, '| ' );

		while ( false !== strpos( $bad_url_contents, '||' ) ) {
			$bad_url_contents = str_replace( '||', '|', $bad_url_contents );
		}
	}

	if ( $bad_url_contents && preg_match( '/' . $bad_url_contents . '/i', $_SERVER['QUERY_STRING'] ) ) {
		die( 'BUC' /*, 503*/ );
	}
}



/***********************************************/
/*          PROTECTION REQUEST  		       */
/***********************************************/

// Allow these request methods.
$methods = array( 'GET' => true, 'POST' => true, 'HEAD' => true );

if ( !defined('API_REST') ) {
	// Sub-module not activated === REST API enabled === these methods are also allowed.
	$methods = array_merge( $methods, array( 'PUT' => true, 'PATCH' => true, 'DELETE' => true ) );
}

if ( ! isset( $methods[ $_SERVER['REQUEST_METHOD'] ] ) ) {
	die( 'RMHM' /*, 405*/ );
}



/***********************************************/
/*          PROTECTION BAD SQLI SCAN           */
/***********************************************/

add_action( 'mp_footer', 'mp_block_sqli_scanners' );
/**
 * Ajoute un balise cacher afin que si un robot scan le site le contenu change à chaque fois
 *
 * @since 1.0
 */
function mp_block_sqli_scanners() {
	$md5 = md5( microtime( true ) );
	$repeat = str_repeat( chr( rand( 33, 126 ) ), (int) rand( 1, 32 ) );
	echo '<span style="display:none !important">' . $md5 . $repeat . '</span>';
}