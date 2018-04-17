<?php defined('ABSPATH') or die('No direct script access.');

/**
 * @package cms mini POPS
 * @subpackage firewall
 * @version 1
 */


/***********************************************/
/*          PROTECTION USER AGENTS 		       */
/***********************************************/

add_action( 'muplugins_loaded', 'mp_block_bad_user_agents', 0 );

/**
 * Filter pour bloquer les mauvais user agent
 *
 */
function mp_block_bad_user_agents() {

	$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? trim( $_SERVER['HTTP_USER_AGENT'] ) : '';

	if ( strip_all_tags( $user_agent ) !== $user_agent ) {
		mp_die( 'message=bad user agent&http_response_code=403' );
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
		mp_die( 'message=bad user agent&http_response_code=403' );
	}

}



/***********************************************/
/*          PROTECTION BAD URL   		       */
/***********************************************/

add_action( 'muplugins_loaded', 'mp_block_bad_url_contents', 0 );

/**
 * Filter les mauvaises url
 *
 */
function mp_block_bad_url_contents() {

	if( strlen( $_SERVER['REQUEST_URI'] ) > 255 ){
		mp_die('message=Request-URI Too Large&http_response_code=414');
	}

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
		mp_die('message=bad url content&http_response_code=400');
	}
}


/***********************************************/
/*          PROTECTION REQUEST  		       */
/***********************************************/

// requête autorisé.
$methods = array( 'GET' => true, 'POST' => true, 'HEAD' => true );

if ( !defined('API_REST') ) {
	// Les méthodes sont autorisées seulement si Api rest utilisé
	$methods = array_merge( $methods, array( 'PUT' => true, 'PATCH' => true, 'DELETE' => true ) );
}

if ( ! isset( $methods[ $_SERVER['REQUEST_METHOD'] ] ) ) {
	mp_die('http_response_code=405&message=request error');
}

/***********************************************/
/*          PROTECTION BAD SQLI SCAN           */
/***********************************************/

add_action( 'mp_footer', 'mp_block_sqli_scanners' );
/**
 * Ajoute un balise cachée afin que si un robot scan le site le contenu change à chaque fois
 *
 * @since 1.0
 */
function mp_block_sqli_scanners() {
	$md5 = md5( microtime( true ) );
	$repeat = str_repeat( chr( rand( 33, 126 ) ), (int) rand( 1, 32 ) );
	echo '<span style="display:none !important">' . $md5 . $repeat . '</span>';
}