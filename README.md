# CMS
CMS est un d'abord un exercice pour l'apprentissage de php. Il n'est pas conseillé de l'utilisé en production.

## Système requis
OS: Unix, Linux, Windows, Mac OS   
PHP: PHP 5.3.0 ou mieux avec librairies [Multibyte String module](http://php.net/mbstring)   
Webserver: Apache with [Mod Rewrite](http://httpd.apache.org/docs/current/mod/mod_rewrite.html) or Ngnix with [Rewrite Module](http://wiki.nginx.org/HttpRewriteModule)   

## Installation


## Recommandations:

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


Copyright (C) 2015 Stephen Deletang / s.deletang [s.deletang@laposte.net]
