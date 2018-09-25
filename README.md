CMS
===
MiniPops est un moteur pour CMS sans base de donnée SQL. 
Le moteur repose sur les fichiers yaml ( un format de représentation de données ).


AUTHOR
======

stephen deletang


SYSTEME REQUIS
--------------

OS: Unix, Linux, Windows, Mac OS   
PHP: PHP 5.3.0 ou mieux avec librairies [Multibyte String module](http://php.net/mbstring)   
Webserver: Apache with [Mod Rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html)   

*************



INSTALLATION
------------

Décompresser l'archive et transférer les fichiers vers le serveur.
Attention aux droits sur les fichiers (644) et répertoires (755).

*************



ARCHITECTURE:
------------

	minipops
	----------|
			  |--- index.php
			  |--- README.md
			  |--- humans.txt
			  |--- browserconfig.xml
			  |--- crossdomain.xml
			  |--- .htaccess
			  |--- core ( ce répertoire contient le coeur de minipops)
			  |--- mp-content-[xxxxxxx]
			  			|
			  			|---- plugins
			  			|---- themes
			  			|---- pages
			  			 		|
			  			 		|---- site.yml ( configuration du site )
			  			 		|---- logo.jpg, logo.png, logo.svg
			  			 		|---- ma-page
			  			 		        |
			  			 		        |---- ma-page.md
			  			 		        |---- mes images

nota: le repertoire mp-content-[xxxxx] sera créer par minipops, de même que le fichier site.yml

*************



CREER UNE PAGE
--------------

Une page est composée d'un répertoire et d'un fichier au format "md" au même nom.

exemple d'une page:

	
	title: Ma première page

	----

	tag: moto, moteur

	----

	content:

	voici mon premier contenu

	----

Minipops offre plusieurs clé pour votre contenu: 
'title','description', 'tag', 'robots', 'author', 'template', 'content', 'thumbnail', 'excerpt', 'pubdate'.

*************


LES POPS
--------

Dans la clé "content" d'une page, il est possible d'écrire du texte en markdown.
Minipops ajout des pops( shortcode ).
Tous les pops, ont des attributs commun: "class" et "text".
ex: ( image: my-image.jpeg | class: image | text: mon image )


| pops                      		|  Commentaires                      	    |
|-----------------------------------|-------------------------------------------|
| ( audio: my.mp3 )         		| Lecteur audio                       	    |
| (email: ss@sss.com | rel: me )	| Adresse email                        	    |
| (file: my.docx ) 			   		| Telecharge fichier            		    |
| (image: test.jpg | size: large)	| Image (size: large, medium, small, crop)  |
| (gallery: test.png, hou.jpg)      | Gallerie d'image						    |
| (link: http://www.re.fr | rel: me)| Liens 			       		            |
| (map: nantes)                     | Affichage carte                           |
| (tel: 0256101213)				    | Téléphone 		                        |
| (twitter: s@dltg | rel:me )       | Twitter                		       		|
| (youtube: url_youtube )      		| Video youtube                   			|

*************


THEME
-----

	default
	----------|
	 		  |--- index.php
			  |--- function.php
			  |--- template
			        |
			       	|---- 404.php
			        |---- page.php
			       	|---- tag.php
			        |---- home.php
			  |--- snippets
			  		|
			  		|---- header.php
			  		|---- header.yml ( posssibilité d'y mettre des traductions ou toute forme d'argument )
			  		|---- footer.php
			  |--- lang
			        |---- en_fr.yml ( fichier de traduction du thème )
			  |--- assets
			  		|
			  		|---- js
			  		|---- css
			  		|---- sass
			  		 	|
			  		 	|---- site.scss
*************


FONCTIONS THEMES
----------------

Fonction pour blog:

| fonction                  |  Commentaires                      		|
|---------------------------|-------------------------------------------|
| the_blog('title')         | Titre du site                       		|
| the_blog('subtitle')      | Sous-titre                         		|
| the_blog('description')   | Déscription du site             		    |
| the_blog('keywords')      | Mots clés                                 |
| the_blog('author')        | Auteur du site 						    |
| the_blog('author_email')  | email de l'auteur        		            |
| the_blog('robot')         | indexation du site                        |
| the_blog('copyright')     | Texte copyright                           |
| the_blog('home')          | URL du site                        		|
| the_blog('charset')       | Charset: utf-8                     		|
| the_blog('language')      | Langue du site                 		    |
| the_blog('version')       | Version minipops                          |
| the_blog('logo')          | logo du site (image à côté de site.yml)   |
| the_blog('template_url')  | url du theme          		            |
| the_blog('rss')           | Url flux rss 			                    |
| the_blog('copyright')     | Texte coypright                           |


Fonction pour page:

| fonction                  |  Commentaires                      		|
|---------------------------|-------------------------------------------|
| the_page('title')         | Titre de la page                    		|
| the_page('description')   | Description de la page              		|
| the_page('excerpt')       | Extrait de la page            		    |
| the_page('tag')           | Mots clés                                 |
| the_page('author')        | Auteur de la page						    |
| the_page('robot')         | indexation de la page                     |
| the_page('content')       | Contenu de la page                        |
| the_page('thumbnail')     | Vignette de la page                  		|
| the_page('template')      | Template de la page à utiliser       		|
| the_page('slug')          | Slug de la page                		    |
| the_page('edit_date')     | Date d'édition de la page                 |
| the_page('pubdate')       | Date de publication de la page  		    |
| the_thumbnail()           | Affichage image vignette 		            |
| the_date()       	        | Date de publication formaté de la page    |
| the_time()   		        | heure de publication formaté de la page   |


Divers:

| fonction                  	|  Commentaires                      		|
|---------------------------	|-------------------------------------------|
| the_breadcrumb()          	| fil arianne  	                    		|
| the_menu('primary_menu')  	| Menu ( configurer dans site.yml)     		|
| search_tag()              	| A utiliser sur template tag: lance la boucle et affiche le mot recherché |
| snippet()                 	| Va charger un snippet definit             |
| get_the_args('xxx')       	| Lit argument du fichier yml du snippet	|
| the_args('xxx')          	 	| Affiche argument du fichier yml du snippet|
| mp_head()         			|              		   						|
| mp_footer()               	|                 							|
| body_class()           		| Classe pour balise body       		    |
| the_loop()           		    | boucle minipops ( chercher dans les pages)|
| have_pages()              	|        		    						|
| have_not_pages()          	| 							    		    |
| _date()                   	| idem date() avec mise à l'heure           |
| __( 'mon texte')          	| Traduction texte			    		    |
| _e( 'mon texte')          	| Traduction texte			    		    |
| esc_attr__( 'mon texte')  	| Traduction texte			    		    |
| esc_attr_e( 'mon texte')  	| Traduction texte			    		    |
| esc_html_( 'mon texte')   	| Traduction texte			    		    |
| esc_html_e( 'mon texte')  	| Traduction texte			    		    |
| _n( 'article','articles',2 )  | Traduction texte  		    		    |


Fonctions pour style et script:

| fonction                  |  Commentaires                      		|
|---------------------------|-------------------------------------------|
| mp_register_style()       | 			 	                    		|
| add_inline_style()        | 								     		|
| mp_deregister_style()     |										    |
| mp_enqueue_style()	    | 										    |
| mp_enqueue_styles()       | 									        |
| mp_register_script()      | 			 	                    		|
| add_inline_script()       |  								     		|
| mp_deregister_script()    |										    |
| mp_enqueue_script()	    | 										    |
| mp_enqueue_scripts()      | 									        |


*************


LES CONSTANTES
--------------

Plusieurs constantes peuvent être définit dans le fichier mp-config.php.
Le fichier mp-config.php doit être placé à côté du fichier index.php ou un cran au dessus.


| Constante                 |  Commentaires                      |
|---------------------------|------------------------------------|
| MP_CONTENT_DIR            | Répertoire du contenu du site      |
| MP_CONTENT_URL            | URL d'accès au répertoire          |
| MP_HOME                   | URL du site                        |
| IMAGIFY                   | Activer: optimisation des images   |
| FORCE_RELOCATE            | Forçage pour réallouer les urls    |
| CACHE                     | Activer: cache statique            |
| DEBUG                     | Mode debuggage                     |
| DEBUG_DISPLAY             | Afficahge erreur à l'écran         |
| DEBUG_LOG                 | Ecriture fichier error.log         |
| MEMORY_LIMIT              | taille de la mémoire limite ex: 64 |


*************


LES HOOKS
---------

Minipops permet d'appliquer des filtres ou d'ajouter des actions.


| fonction                  |  Commentaires                      |
|---------------------------|------------------------------------|
| add_action()              | Ajout d'une action     		     |
| do_action()               | Executer actions		             |
| add_filter()    		    | Ajout d'un filter                  |
| apply_filters()           | Appliquer des filtres              |


*************


LES TACHES
----------

Minipops permet de gérer certaines taches à des moments précis ou récursive.


| Fonction                  |  Commentaires                      |
|---------------------------|------------------------------------|
| do_event()                | Evenements					     |
| get_schedules()           | Plage horaires		             |
| get_scheduled()           | Lecture d'un evenement             |
| reschedule_event()        | Replannifier un evenement          |
| unschedule_event()        | De-pllanifier un evenement         |


*************


RECOMMENDATIONS:
---------------

- L'affichage : la commande echo est 10% plus rapide que print

- Les chaines de caractères : externalisez les variables des chaines : echo 'nom: '.$nom."\n"; est 20% plus rapide que echo "nom: $nom\n"; ou utilisez sprintf qui est 10 fois plus rapide que print pour afficher des chaines qui contiennent des variables

-Les boucles et les tests : for est 17 fois plus rapide si le test de fin s'appuie sur une variable plutôt qu'une fonction, 24 fois plus rapide qu'un while, 53 fois plus rapide qu'un foreach, if... elseif... est 11 fois plus rapide qu'un switch... case...

- Libération des ressources : faites un unset des variables inutiles et fermez les connexions vers la base de données quant elles sont inutiles, évitez la programmation objet en PHP qui est gourmande en mémoire et 4 fois plus lente qu'une programmation procédurale.

- Choix des méthodes : n'utilisez pas de méthode magic préfixée par deux underscores, par exemple __unset, mais préférer les fonctions de hauts niveaux.

- L'inclusion de code : require() est 4 fois plus rapide que require_once(). Limitez l'usage de ce dernier dans le cadre de fonction fréquemment appelée incluant de grosses bibliothèques. L'utilisation d'un chemin absolu dans les inclusions de fichiers est 2 fois plus rapide qu'un chemin relatif

- Les erreurs : l'opérateur de suppression des erreurs @ double le temps d'exécution des méthodes. Il est préférable de gérer les erreurs manuellement, d'augmenter la sévérité de l'interpréteur PHP via la directive error_reporting(E_STRICT)

- Une page HTML statique est 10 fois plus rapide à restituer qu'une page PHP. Cachez les résultats en évitant si possible les framework complexes comme Smarty, utilisez XDebug pour optimiser votre code, utilisez un précompilateur PHP comme XCache

- Les variables : l'utilisation des constantes est 7 fois plus rapide que les variables, les opérations sur les variables locales sont 3 fois plus rapide que celle sur les attributs d'un objet

- La sécurité : protégez vos applications des attaques par injection SQL en encodant les chaines à destination de la base de données avec la méthode mysql_real_escape_string(), des attaques par Cross Site Scripting (XSS) en encodant les chaines à destination de conteneurs HTML par la méthode htmlspecialchars() et manipulez les données sensibles (compte, mot de passe...) exclusivement cryptées à l'aide de la méthode md5()


Copyright (C) 2015 Stephen Deletang / s.deletang


*************************

Balises HTML

Les principales balises sont:

    - itemscope: élément général
    - itemtype: type d'élément
    - itemid: identifiant de l'élément
    - itemprop: propriétés de l'élément
    - itemref: référence vers un autre élément (itemscope)

 
Types

Les principaux types d'éléments (itemtype) sont:

    - CreativeWork (Article, Book, MediaObject, Painting, Movie, WebPage...)
    - Event
    - Organization
    - Person
    - Place (CivicStructure, LocalBusiness, Restaurant...)
    - Product
    - Service

Vous pouvez consulter la liste complète ici.

 
Exemple d'usage pour une page web

Les pages web sont définies par le type WebPage. Il existe des types spécifiques pour certaines pages:

    - AboutPage: à propos
    - CheckoutPage: commande (commerciale)
    - CollectionPage: collection
    - ImageGallery: galerie photo
    - VideoGallery: galerie vidéo
    - ContactPage: contact
    - ItemPage: objet/article
    - MedicalWebPage: médicale
    - ProfilePage: profil
    - QAPage: questions/réponses
    - SearchResultsPage: résultats de recherche



Voici un exemple pour une page web classique contenant un article:

<body itemscope itemtype="http://schema.org/WebPage">

# NAVIGATION

<div id="menu" itemscope itemtype="http://schema.org/SiteNavigationElement" >
  <a itemprop="url" href="http://www.littlej.fr/services"  >
    <div itemprop="name">Services</div>
  </a>
  <a itemprop="url" href="http://www.littlej.fr/contact"  >
    <div itemprop="name">Contact</div>
  </a>
</div>


# CONTENU DE LA PAGE

<div class="wrapper" itemprop="mainContentOfPage">

  # ARTICLE 
  <div itemscope itemtype="http://schema.org/Article">
    <p>Titre: <span itemprop="name">Mon super article</span></p>
    <p>Date de publication: <span itemprop="datePublished" content="20-01-2014">20 Janvier 2014<span></p>
    <p>Description: <span itemprop="about">Une petite description</span>
    <p itemprop="articleBody">Voici le contenu de mon article</p>

    # AUTEUR
    <div itemprop="author" itemscope itemtype="http://schema.org/Person">
      <span itemprop="name">Little J</span>
      <img src="john-doe.jpg" itemprop="image" />
      <span itemprop="jobTitle">Conseil informatique</span>
      <a href="mailto:jane-doe@xyz.edu" itemprop="email">contactez@moi.fr</a>
      <a href="http://www.littlej.fr" itemprop="url">www.littlej.fr</a>
    </div>

  </div> 

</div>


<body>

*******************


<!DOCTYPE html>
<html lang="en-US">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
	<meta name="robots" content="noodp, noydir" />

	<link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
	<link rel="canonical" href="http://mysite.com/" />
	<link rel="stylesheet" href="http://mysite.com/style.css" type="text/css" />
	<link rel="alternate" href="http://feeds.feedburner.com/feed" type="application/rss+xml" title="Direct Feed"/>
	<link rel="shortcut icon" href="http://mysite.com/images/favicon.ico" type="image/x-icon" />
	<link rel="apple-touch-icon-precomposed" href="http://mysite.com/images/apple-retina.png" sizes="114x114" />
	<link rel="apple-touch-icon-precomposed" href="http://mysite.com/images/apple-ipad.png" sizes="72x72" />
	<link rel="apple-touch-icon" href="http://mysite.com/images/apple-touch.png" />
	<link rel="next" href="page2.php" />

	<title>MySite.com</title>
	<meta name="description" content="mysite html5 framework with scheme.org" />
	<meta name="keywords" content="html5, schema.org" />

	<!--[if lt IE 9]>
		<script src="//cdnjs.cloudflare.com/ajax/libs/html5shiv/3.6.2/html5shiv.js"></script>
	<![endif]-->

	<!-- Google Analytics Code -->
		<script type="text/javascript">
			var _gaq = _gaq || [];
			var pluginUrl = '//www.google-analytics.com/plugins/ga/inpage_linkid.js';
		 	_gaq.push(['_setAccount', 'UA-XXXXXXXX-XX']);
			_gaq.push(['_trackPageview']);
			_gaq.push(['_require', 'inpage_linkid', pluginUrl]);
			(function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			})();
		</script> 
	<!-- Google ANalytics Code --> 
</head>
<body class="home" itemscope itemtype="http://schema.org/WebPage">
	<div id="wrap" class="site-container">
		
		<header class="site-header" role="banner" itemscope itemtype="http://schema.org/WPHeader">
			<div class="wrap">
				<div class="title-area">
					<h1 class="site-title" itemprop="headline">Site Name</h1>
					<h2 class="site-description" itemprop="description">Site Description</h2>
				</div>
			</div>
		</header><!-- .site-header -->

		<nav class="site-navigation nav-primary" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
			<div class="wrap">
				<ul class="site-nav-menu">
					<li class="menu-item menu-item-1 menu-home" itemprop="url"><a href="http://mysite.com/" title="Home" itemprop="name">Home</a></li>
					<li class="menu-item menu-item-2 menu-about" itemprop="url"><a href="http://mysite.com//about.php" title="About" itemprop="name">About</a></li>
				</ul>
			</div>
		</nav><!--.site-navigation -->

		<div class="site-inner">
			<main class="site-content" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

				<article class="post entry" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">
					<header class="entry-header">
						<h1 class="entry-title" itemprop="headline"><a href="http://mysite.com/post" title="Blog Post" rel="bookmark">Blog Post</a></h1> 
						<p class="entry-meta"><time class="entry-time" itemprop="datePublished" datetime="2012-01-07T07:07:21+00:00">January 7, 2012</time> by 
							<span class="entry-author" itemprop="author" itemscope itemptype="http://schema.org/Person">
								<a href="http://mysite.com/author" class="entry-author-link" itemprop="url" rel="author">
									<span class="entry-author-name" itemprop="name">Author Name</span>
								</a>
							</span>
						</p>
					</header>
					<div class="entry-content" itemprop="text">
						<p>This is a blog post.</p>
					</div>
					<footer class="entry-footer">
						<p class="entry-meta">
							<span class="entry-categories">Filed Under: <a href="http://mysite.com/the-category" title="View all posts in Category" rel="category">The Category</a></span>
							<span class="entry-tags">Tagged With: <a href="http://mysite.com/?tag=tag-1" rel="tag">tag 1</a>, <a href="http://mysite.com/?tag=tag-2" rel="tag">tag 2</a></span>
						</p>
					</footer>
				</article><!-- .post -->
				
				<div class="entry-comments" id="comments">
					<h3>Comments</h3>
					<ol class="comment-list">
						<li class="comment even thread-even depth-1" id="comment-123">
							<article itemprop="comment" itemscope itemtype="http://schema.org/UserComments">
								<header class="comment-header">
									<p class="comment-author" itemprop="creator" itemscope itemtype="http://schema.org/Person">
										<span itemprop="name"><a href="http://authorswebsite.com" rel="external nofollow" itemprop="url">Comment Author</a></span>
									</p>
									<p class="comment-meta">
										<time itemprop="commentTime" datetime="2013-09-15T13:22:51+00:00">September 15th, 2013 at 13:22</time>
									</p>
								</header>
								<div class="comment-content" itemprop="commentText">
									<p>some comment text etc etc...</p>
								</div>
							</article>
						</li>
					</ol>
				</div><!-- .entry-coments -->

				<ul class="pagination" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
					<li class="active" itemprop="url"><a class="more" href="index.php" itemprop="name">1</a></li>
					<li itemprop="url"><a rel="next" class="more" href="page2.php" itemprop="name">2</a></li>
					<li itemprop="url"><a  class="more" href="page3.php" itemprop="name">3</a></li>
					<li itemprop="url"><a class="more next" href="page2.php" itemprop="name">Next &gt;</a></li>
				</ul>

			</main><!-- .site-content -->

			<aside class="site-sidebar sidebar-primary widget-area" role="complementary" itemscope itemtype="http://schema.org/WPSideBar">
				<div class="widget widget_text">
					<div class="widget-wrap">
						<h4 class="widgettitle">Primary Sidebar Widget</h4>
						<div class="textwidget">
							<p>This is the Primary Sidebar Widget</p>
						</div>
					</div>
				</div>
			</aside><!-- .site-sidebar -->

		</div><!-- .site-inner -->

		<footer class="site-footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">
			<div class="wrap">
				<p class="back-to-top"><a title="Back to top" href="#wrap">Back to top</a></p>
				<p class="creds">&copy; Copyright 2012-2013 MySite.com | <a title="RSS Feed" href="http://feeds.feedburner.com/feed">RSS</a> | <a title="Subscribe via Email" href="http://feedburner.google.com/fb/a/mailverify?uri=feed" target="_blank">Subscribe via Email</a></p>
			</div>
		</footer><!-- .site-footer -->

	</div><!-- .site-container -->

	<!-- Footer JavaScripts -->
		<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script>
	<!-- Footer JavaScripts -->
</body>
</html>
