<?php
//COMMON

// Set default timezone
date_default_timezone_set('Europe/Paris');
//Set sqlite database path
define('DATABASE_PATH',__DIR__.DIRECTORY_SEPARATOR.'database.db');
//Get all GET/POST var
$_ = array_map('htmlspecialchars',array_merge($_POST,$_GET));

//open database
$db = new PDO('sqlite:'.DATABASE_PATH);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);




//INSTALL
if(fileSize(DATABASE_PATH)==0){

	$db->exec("CREATE TABLE IF NOT EXISTS domain (
						id INTEGER PRIMARY KEY, 
						label TEXT, 
						url TEXT)");

	$db->exec('CREATE TABLE IF NOT EXISTS ping (
						id INTEGER PRIMARY KEY, 
						"code" TEXT,
						"transaction-time" TEXT,
						"server-date" TEXT,
						"content-length" TEXT,
						"content-type" TEXT,
						"content-encoding" TEXT,
						"datetime" TEXT,
						"headers" TEXT,
						"domain" INT)');

	$db->exec("CREATE TABLE IF NOT EXISTS watching (
						id INTEGER PRIMARY KEY, 
						mail TEXT,
						domain INT)");
					
}


//ACTIONS

if(isset($_['action'])){
	switch($_['action']){

		case 'add_contact':
			header("Content-Type: application/json");
			$response = array();
			try{
				$query = $db->prepare("INSERT INTO watching (mail, domain) VALUES (?, ?)");
				$query->execute(array('test@test.com',$_['domain']));
				$response['id'] = $db->lastInsertId();
				$response['mail'] = 'test@test.com';
				$response['domain'] = $_['domain'];
			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		break;

		case 'update_contact':
			header("Content-Type: application/json");
			$response = array();
			try{
				$query = $db->prepare("UPDATE watching SET mail=? WHERE id=?");
				$query->execute(array($_['mail'],$_['id']));
			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		break;

		case 'delete_contact':
			header("Content-Type: application/json");
			$response = array();
			try{
				$query = $db->prepare("DELETE FROM watching WHERE id = ?");
				$query->execute(array($_['id']));
			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		break;


		case 'load_domain':
			header("Content-Type: application/json");
			$response = array();
			try{

				$response['rows'] = array();
				$domains = $db->query('SELECT * FROM domain d ORDER BY label')->fetchAll();
				foreach ($domains as $domain) :
					$currentdomain = $domain; 
					$contacts = $db->prepare('SELECT id,mail FROM watching w WHERE domain = ?');
					$contacts->execute(array($domain['id']));

					$currentdomain['class'] = '';
					$currentdomain['code'] = 'Non vérifié';
					$currentdomain['mime'] = '-';
					$currentdomain['encoding'] = '-';
					$currentdomain['date'] = '-';

					$ping = $db->prepare('SELECT * FROM ping WHERE domain=? ORDER BY id DESC LIMIT 1');
					$ping->execute(array($domain['id']));
					$ping = $ping->fetch();
					
					if($ping!=false):
						if($ping['code']!=200) $currentdomain['class'] = 'bubble-red';
						if(trim($ping['content-type'])!='text/html' || trim($ping['content-encoding'])!='utf-8') $currentdomain['class'] ='bubble-orange';
						$currentdomain['mime'] = $ping['content-type'];
						$currentdomain['encoding'] = $ping['content-encoding'];
						$currentdomain['code'] = http_code($ping['code']);
						$currentdomain['date'] = date('d/m/Y à H:i',$ping['datetime']);
					endif;
					
					
					foreach($contacts->fetchAll() as $contact):
						$currentdomain['contacts'][]  = array('id'=>$contact['id'],'mail'=>$contact['mail']);
					endforeach;
					$response['rows'][] = $currentdomain;
				endforeach;

			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		
		break;

		case 'add_domain':
			header("Content-Type: application/json");
			$response = array();
			try{
				$query = $db->prepare("INSERT INTO domain (label, url) VALUES (?, ?)");
				$query->execute(array('Perdu.com','http://perdu.com'));
				$response['id'] = $db->lastInsertId();
			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		break;

		case 'update_domain':
			header("Content-Type: application/json");
			$response = array();
			try{
				$query = $db->prepare("UPDATE domain SET label=?, url=? WHERE id=?");
				$query->execute(array($_['label'],$_['url'],$_['id']));
			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		break;

		case 'delete_domain':
			header("Content-Type: application/json");
			$response = array();
			try{
				$query = $db->prepare("DELETE FROM domain WHERE id = ?");
				$query->execute(array($_['id']));
			}catch(Exception $e){
				$response['error'] = $e->getMessage();
			}
			echo json_encode($response);
		break;

		case 'cron':
		
			$result = $db->query('SELECT * FROM domain');
			foreach ($result as $domain) :
				$ch = curl_init($domain['url']);

				curl_setopt_array($ch, array(
					CURLOPT_HEADER => 1,
					CURLOPT_NOBODY  => true,
					CURLOPT_RETURNTRANSFER => 1,
					CURLOPT_USERAGENT => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.1) Gecko/20061204 Firefox/2.0.0.1",
					CURLOPT_FOLLOWLOCATION => 1,
					CURLOPT_SSL_VERIFYPEER => 0,
					CURLOPT_CONNECTTIMEOUT => 0,
					CURLOPT_TIMEOUT => 60
				));


				
				$output = curl_exec($ch); 

				$report['domain'] = $domain['id'];
				$report['code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$report['transaction-time'] = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
				$report['server-date'] = curl_getinfo($ch, CURLINFO_FILETIME);
				$report['content-length'] = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
				preg_match('/Content-Type: ([^\n;]*)/i', $output, $matches);
				$report['content-type'] = isset($matches[1]) ? $matches[1] : '';
				preg_match('/charset=([^\n;]*)/i', $output, $matches);
				$report['content-encoding'] = isset($matches[1]) ? $matches[1] : '';
				$report['datetime'] = time();
				$report['headers'] = $output;

				$lastPing = $db->prepare('SELECT * FROM ping WHERE domain = ? ORDER BY id DESC LIMIT 1');
				$lastPing->execute(array($report['domain']));
				$lastPingInfos = $lastPing->fetch();
				
				$query = $db->prepare('INSERT INTO ping ("domain","code","transaction-time","server-date","content-length","content-type","content-encoding","datetime","headers") VALUES (?,?,?,?,?,?,?,?,?)');
				$query->execute(array_values($report));
			
				if($report['code'] == $lastPingInfos['code']){
					$query = $db->prepare('SELECT * FROM watching w WHERE domain = ?');
					$contacts = $query->execute(array($report['domain']));
					foreach($query->fetchAll() as $contact):
						mail_report($domain,$report,$contact['mail']);
					endforeach;
				}

				curl_close($ch);
			endforeach;
		break;
	}
	exit();
}


//DASHBOARD
?>
<!doctype html>
<html class="no-js" lang="">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
	    <title>EYZ</title>
	    <meta name="description" content="">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
	    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400" rel="stylesheet">
	    <style>
	    	html,body{
	    		font-family: 'Roboto', sans-serif;
	    		background-color: #f1f1f1;
	    		color:#333333;
	    		padding:0;
	    		margin:0;
	    	}
	    	a{
	    		color:#116aca;
	    	}
	    	h3{
	    		margin:0px 0px 15px 0;
	    	}
	    	ul,li{
	    		list-style-type: none;
	    		margin:0;
	    		padding:0;
	    	}
	    	hr{
	    		border:0;
	    		border-bottom:1px solid #cecece;
	    	}



	    	#domains > li{
	    		padding:15px;
	    		background-color: #ffffff;
	    		display: inline-block;
	    		position:relative;
	    		vertical-align: top;
	    		margin:15px;
	    		border-radius: 3px;
	    		box-shadow: 0 1px 2px rgba(0,0,0,0.1);
	    		width: 20%;
	    		max-width: 250px;
	    		min-width: 200px;
	    		min-height: 250px;
	    		transition:all 0.2s linear;
	    	}
	    	.contacts {
	    		margin:5px 0;
	    	}
	    	.contacts li{
				display: inline-block;
			    padding: 0 5px;
			    margin-bottom: 0;
			    font-size: 14px;
			    text-align: center;
			    vertical-align: middle;
			    cursor: pointer;
			    border: 1px solid transparent;
    		    background-color: #333333;
			    border-color: #000000;
		        border-radius: 4px;
		        color: #fff;
		        margin-right: 2px;
	    	}
	    	.bubble{
	    		display: block;
			    padding: 5px;
			    margin-bottom: 10px;
			    font-size: 14px;
			    font-weight: 400;
			    line-height: 1.42857143;
			    text-align: center;
			    white-space: nowrap;
			    vertical-align: middle;
			    cursor: pointer;
			    border: 1px solid transparent;
    		    background-color: #37bc9b;
			    border-color: #37bc9b;
		        border-radius: 4px;
		        color: #fff;
			}

			.bubble-red{
				 background-color: #e74c3c;
				 border-color: #e74c3c;
			}
	    	.bubble-orange{
				 background-color: #e67e22;
				 border-color: #e67e22;
			}

			#addDomain{
				opacity: 0.5;
				text-align: center;
				color:#cecece;
				font-size: 200px;
				font-weight: 300;
				cursor:pointer;
				transition: opacity 0.2s ease-in-out;
			}
			#addDomain:hover{
				opacity: 1;
			}

			#domains > li > i.fa-times{
				cursor: pointer;
				opacity: 0.2;
				transition: opacity 0.2s ease-in-out;
				position:absolute;
				top:10px;
				color:#444444;
				right:10px;
			}
			#domains > li > i.fa-times:hover{
				opacity: 0.7;
			}
			.label{
				text-align: center;
			}
			.label,.url{
				transition:all 0.2s ease-in-out;
				border-bottom:2px dotted #ffffff;
				text-decoration: none;
			}
			.label:hover,.url:hover{
				border-color:#f1c40f;
			}
			

			@media only screen and (max-width:600px) {
			   	#domains > li{
		    		max-width: 100%;
		    		width:100%;
		    		min-width: 100%;
		    		margin:0 0 5px 0;
		    		box-sizing: border-box;
		    		display: block;
		    	}

			}

	    </style>
	</head>
<body>
<ul id="domains">

	<li id="addDomain" onclick="addDomain();"><i class="fa fa-plus"></i></li>


	</ul>
	<script type="text/javascript">
		$(document).ready(function(){
			loadDomain();
			
		});
		

		function loadDomain(callback){
			$.getJSON('',{action:'load_domain'},function(r){
				if(r.error) return alert('Erreur:'+r.error);
				$('#domains li[data-id]').remove();
				for(var key in r.rows){
					var domain = r.rows[key];
					var li = '<li data-id="'+domain.id+'"> \
						<i onclick="deleteDomain(this)" class="fa fa-times"></i> \
						<h3 class="label" contenteditable="true">'+domain.label+'</h3> \
						<div class="bubble '+domain.class+'">'+domain.code+'</div> \
						<a contenteditable="true" class="url" href="'+domain.url+'">'+domain.url+'</a><br/> \
						<small>Dernier ping le <strong>'+domain.date+'</strong></small><br/> \
						<small>Type de contenu <strong>'+domain.mime+'</strong></small><br/> \
						<small>Encodage <strong>'+domain.encoding+'</strong></small><br/> \
						<hr/> \
						<small><strong>Contacts</strong></small> \
						<ul class="contacts">';
						for(var key2 in domain.contacts){
							var contact = domain.contacts[key2];
							li +='<li data-id="'+contact.id+'"><span class="contact" contenteditable="true">'+contact.mail+'</span> <i onclick="deleteContact(this);" class="fa fa-times"></i></li>';
						}
						li +='<li onclick="addContact(this);"><i class="fa fa-plus"></i></li>';
						li +='</ul></li>';
						$('#domains').prepend(li);
				}

				$('#domains .label').keydown(function(e){
					if(e.keyCode != 9) return;
					setTimeout(function(){
						$(this).next('.url').focus();
						document.execCommand('selectAll',false,null);
					},100);
					
				});

				$('#domains .label').blur(function(){
					updateDomain(this);
				});
				$('#domains .url').blur(function(){
					$(this).attr('href',$(this).text());
					updateDomain(this);
				});
				$('#domains').on('blur','.contact',function(){
					updateContact(this);
				});

				if(callback) callback();
			
			});
		}

		function addContact(element){
			var domainBloc = $(element).closest('#domains > li');
			$.getJSON('',{action:'add_contact',domain:domainBloc.attr('data-id')},function(r){
				if(r.error) return alert('Erreur:'+r.error);
				var li = $('<li data-id="'+r.id+'"><span class="contact" contenteditable="true">'+r.mail+'</span> <i onclick="deleteContact(this);" class="fa fa-times"></i></li>');
				$(element).before(li);
				$('.contact',li).focus();
				document.execCommand('selectAll',false,null);

			});
		}
		function updateContact(element){
			var contactBloc = $(element).closest('.contacts > li');
			$.getJSON('',{action:'update_contact',mail:$(element).text(),id:contactBloc.attr('data-id')},function(r){
				if(r.error) return alert('Erreur:'+r.error);
			});
		}
		function deleteContact(element){
			if(!confirm('Êtes vous sûr de vouloir supprimer cet item ?')) return;
			var contactBloc = $(element).closest('.contacts > li');
			$.getJSON('',{action:'delete_contact',label:$(element).text(),id:contactBloc.attr('data-id')},function(r){
				if(r.error) return alert('Erreur:'+r.error);
				contactBloc.fadeOut();
			});
		}

		function updateDomain(element){
			var bloc = $(element).closest('#domains > li');
			$.getJSON('',{action:'update_domain',label:bloc.find('.label').text(),url:bloc.find('.url').text(),id:bloc.attr('data-id')},function(r){
				if(r.error) return alert('Erreur:'+r.error);
			});
		}


		function addDomain(){
			$.getJSON('',{action:'add_domain'},function(r){
				if(r.error) return alert('Erreur:'+r.error);
				loadDomain(function(){
					var li = $('#domains > li[data-id="'+r.id+'"]');
					$('.label',li).focus();
					document.execCommand('selectAll',false,null);
				});
			});
		}
		function deleteDomain(element){
			var bloc = $(element).closest('#domains > li');
			if(!confirm('Êtes vous sûr de vouloir supprimer cet item ?')) return;
			$.getJSON('',{action:'delete_domain',id:bloc.attr('data-id')},function(r){
				if(r.error) return alert('Erreur:'+r.error);
				bloc.fadeOut();
			});
		}
	</script>
    </body>
</html>
<?php

//FUNCTIONS
function mail_report($domain,$report,$mail){
	echo '<pre>';
	global $httpCode;
	
	$state = $report['code'] == 200 ? 'Démarré':'Arreté'; 
	$stateColor = $report['code'] == 200 ? '#37BC9B' : '#D05141';
	
	$headers = "From: eyz@sys1.fr\r\n";
	$headers .= "Reply-To: developpement@nowhere.fr\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
	
	$title = "Le site ".$domain['label']."(".$domain['url'].") est ".$state." à ".date('H:i:s',$report['datetime']);
	
	$message = '<html><body>';
	$message = "Le site ".$domain['label']."(".$domain['url'].") est ".$state." à".date('H:i:s',$report['datetime'])."<br/>";
	$message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
	$message .= "<tr style='background: #eee;'><td><strong>DOMAINE:</strong> </td><td><a href='".$domain['url']."'>".$domain['url']." (".$domain['label'].")</a></td></tr>";
	$message .= "<tr><td><strong>CODE</strong> </td><td style='background:$stateColor;'>" . $report['code'].' '.http_code($report['code']). "</td></tr>";
	$message .= "<tr><td><strong>TYPE CONTENU</strong> </td><td>" . $report['content-type']. "</td></tr>";
	$message .= "<tr><td><strong>DATE</strong> </td><td>" . date('d/m/Y H:i:s',$report['datetime']). "</td></tr>";
	$message .= "<tr><td><strong>DETAILS</strong> </td><td>" . strip_tags($report['headers']). "</td></tr>";
	$message .= "</table>";
	$message .= "</body></html>";
	mail($mail,$title,$message,$headers);
	echo $mail.PHP_EOL.$title.PHP_EOL.$message;
}


function http_code($code){
	
	$httpCode = array(
	0=>"NOT FOUND: Serveur introuvable.",
	200=>"OK: Requête traitée avec succès.",
	201=>"Created: Requête traitée avec succès et création d’un document.",
	202=>"Accepted: Requête traitée, mais sans garantie de résultat.",
	203=>"Non-Authoritative Information: Information retournée, mais générée par une source non certifiée.",
	204=>"No Content: Requête traitée avec succès mais pas d’information à renvoyer.",
	205=>"Reset Content: Requête traitée avec succès, la page courante peut être effacée.",
	206=>"Partial Content: Une partie seulement de la ressource a été transmise.",
	207=>"Multi-Status: WebDAV : Réponse multiple.",
	210=>"Content Different: WebDAV : La copie de la ressource côté client diffère de celle du serveur (contenu ou propriétés).",
	226=>"IM Used: RFC 32293 : Le serveur a accompli la requête pour la ressource, et la réponse est une représentation du résultat d'une ou plusieurs manipulations d'instances appliquées à l'instance actuelle.",
	300=>"Multiple Choices: L’URI demandée se rapporte à plusieurs ressources.",
	301=>"Moved Permanently: Document déplacé de façon permanente.",
	302=>"Moved Temporarily: Document déplacé de façon temporaire.",
	303=>"See Other: La réponse à cette requête est ailleurs.",
	304=>"Not Modified: Document non modifié depuis la dernière requête.",
	305=>"Use Proxy: La requête doit être ré-adressée au proxy.",
	306=>"(aucun): Code utilisé par une ancienne version de la RFC 26164, à présent réservé.",
	307=>"Temporary Redirect: La requête doit être redirigée temporairement vers l’URI spécifiée.",
	308=>"Permanent Redirect: La requête doit être redirigée définitivement vers l’URI spécifiée.",
	310=>"Too many Redirects: La requête doit être redirigée de trop nombreuses fois, ou est victime d’une boucle de redirection.",
	400=>"Bad Request: La syntaxe de la requête est erronée.",
	401=>"Unauthorized: Une authentification est nécessaire pour accéder à la ressource.",
	402=>"Payment Required: Paiement requis pour accéder à la ressource.",
	403=>"Forbidden: Le serveur a compris la requête, mais refuse de l'exécuter. Contrairement à l'erreur 401, s'authentifier ne fera aucune différence. Sur les serveurs où l'authentification est requise, cela signifie généralement que l'authentification a été acceptée mais que les droits d'accès ne permettent pas au client d'accéder à la ressource.",
	404=>"Not Found: Ressource non trouvée.",
	405=>"Method Not Allowed: Méthode de requête non autorisée.",
	406=>"Not Acceptable: La ressource demandée n'est pas disponible dans un format qui respecterait les en-têtes 'Accept' de la requête.",
	407=>"Proxy Authentication Required: Accès à la ressource autorisé par identification avec le proxy.",
	408=>"Request Time-out: Temps d’attente d’une requête du client écoulé.",
	409=>"Conflict: La requête ne peut être traitée en l’état actuel.",
	410=>"Gone: La ressource n'est plus disponible et aucune adresse de redirection n’est connue.",
	411=>"Length Required: La longueur de la requête n’a pas été précisée.",
	412=>"Precondition Failed: Préconditions envoyées par la requête non vérifiées.",
	413=>"Request Entity Too Large: Traitement abandonné dû à une requête trop importante.",
	414=>"Request-URI Too Long: URI trop longue.",
	415=>"Unsupported Media Type: Format de requête non supporté pour une méthode et une ressource données.",
	416=>"Requested range unsatisfiable: Champs d’en-tête de requête « range » incorrect.",
	417=>"Expectation failed: Comportement attendu et défini dans l’en-tête de la requête insatisfaisante.",
	418=>"I’m a teapot: « Je suis une théière ». Ce code est défini dans la RFC 23245 datée du premier avril 1998, Hyper Text Coffee Pot Control Protocol.",
	421=>"Bad mapping / Misdirected Request: La requête a été envoyée à un serveur qui n'est pas capable de produire une réponse (par exemple, car une connexion a été réutilisée).",
	422=>"Unprocessable entity: WebDAV : L’entité fournie avec la requête est incompréhensible ou incomplète.",
	423=>"Locked: WebDAV : L’opération ne peut avoir lieu car la ressource est verrouillée.",
	424=>"Method failure: WebDAV : Une méthode de la transaction a échoué.",
	425=>"Unordered Collection: WebDAV RFC 36486. Ce code est défini dans le brouillon WebDAV Advanced Collections Protocol, mais est absent de Web Distributed Authoring and Versioning (WebDAV) Ordered Collections Protocol.",
	426=>"Upgrade Required: RFC 28177 Le client devrait changer de protocole, par exemple au profit de TLS/1.0.",
	428=>"Precondition Required: RFC 65858 La requête doit être conditionnelle.",
	429=>"Too Many Requests: RFC 65859 Le client a émis trop de requêtes dans un délai donné.",
	431=>"Request Header Fields Too Large: RFC 658510 Les entêtes HTTP émises dépassent la taille maximale admise par le serveur.",
	449=>"Retry With: Code défini par Microsoft. La requête devrait être renvoyée après avoir effectué une action.",
	450=>"Blocked by Windows Parental Controls: Code défini par Microsoft. Cette erreur est produite lorsque les outils de contrôle parental de Windows sont activés et bloquent l’accès à la page.",
	451=>"Unavailable For Legal Reasons: Ce code d'erreur indique que la ressource demandée est inaccessible pour des raisons d'ordre légal11,12.",
	456=>"Unrecoverable Error: WebDAV : Erreur irrécupérable.",
	444=>"No Response: Indique que le serveur n'a retourné aucune information vers le client et a fermé la connexion.",
	495=>"SSL Certificate Error: Une extension de l'erreur 400 Bad Request, utilisée lorsque le client a fourni un certificat invalide.",
	496=>"SSL Certificate Required: Une extension de l'erreur 400 Bad Request, utilisée lorsqu'un certificat client requis n'est pas fourni.",
	497=>"HTTP Request Sent to HTTPS Port: Une extension de l'erreur 400 Bad Request, utilisée lorsque le client envoie une requête HTTP vers le port 443 normalement destiné aux requêtes HTTPS.",
	499=>"Client Closed Request: Le client a fermé la connexion avant de recevoir la réponse. Cette erreur se produit quand le traitement est trop long",
	500=>"Internal Server Error: Erreur interne du serveur.",
	501=>"Not Implemented: Fonctionnalité réclamée non supportée par le serveur.",
	502=>"Bad Gateway ou Proxy Error: Mauvaise réponse envoyée à un serveur intermédiaire par un autre serveur.",
	503=>"Service Unavailable: Service temporairement indisponible ou en maintenance.",
	504=>"Gateway Time-out: Temps d’attente d’une réponse d’un serveur à un serveur intermédiaire écoulé.",
	505=>"HTTP Version not supported: Version HTTP non gérée par le serveur.",
	506=>"Variant Also Negotiates: RFC 229514 : Erreur de négociation. Transparent content negociation.",
	507=>"Insufficient storage: WebDAV : Espace insuffisant pour modifier les propriétés ou construire la collection.",
	508=>"Loop detected: WebDAV : Boucle dans une mise en relation de ressources (RFC 584215).",
	509=>"Bandwidth Limit Exceeded: Utilisé par de nombreux serveurs pour indiquer un dépassement de quota.",
	510=>"Not extended: RFC 277416 : la requête ne respecte pas la politique d'accès aux ressources HTTP étendues.",
	511=>"Network authentication required: RFC 658517 : Le client doit s'authentifier pour accéder au réseau. Utilisé par les portails captifs pour rediriger les clients vers la page d'authentification. côté serveur13.",
	520=>"Unknown Error: L'erreur 520 est utilisé en tant que réponse générique lorsque le serveur d'origine retourne un résultat imprévu.",
	521=>"Web Server Is Down: Le serveur a refusé la connexion depuis Cloudflare.",
	522=>"Connection Timed Out: Cloudflare n'a pas pu négocier un TCP handshake avec le serveur d'origine.",
	523=>"Origin Is Unreachable: Cloudflare n'a pas réussi à joindre le serveur d'origine. Cela peut se produire en cas d'échec de résolution de nom de serveur DNS.",
	524=>"A Timeout Occurred: Cloudflare a établi une connexion TCP avec le serveur d'origine mais n'a pas reçu de réponse HTTP avant l'expiration du délai de connexion.",
	525=>"SSL Handshake Failed: Cloudflare n'a pas pu négocier un SSL/TLS handshake avec le serveur d'origine.",
	526=>"Invalid SSL Certificate: Cloudflare n'a pas pu valider le certificat SSL présenté par le serveur d'origine.",
	527=>"Railgun Error: L'erreur 527 indique que la requête a dépassé le délai de connexion ou a échoué après que la connexion WAN ait été établie."
	);
	return (isset($httpCode[$code])?$httpCode[$code]:'Erreur indéfinie');
}

?>