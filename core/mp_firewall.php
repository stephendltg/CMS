<?php defined('ABSPATH') or die('No direct script access.');

/**
 * @package cms mini POPS
 * @subpackage firewall
 * @version 1
 */


function mp_die( $arg = array() ){

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
