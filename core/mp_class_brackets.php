<?php defined('ABSPATH') or die('No direct script access.');

/**
 *
 *
 * @package cms mini POPS
 * @subpackage brackets
 * @version 1
 */

/***********************************************/
/*                  brackets                   */
/***********************************************/

/*
    var :           {{ma_variable}}
    commentaire:    {{! mon  commentaire }}
    boucle if :     {{#test}} j'aime la soupe {{/test}}
    boucle for :    {{#test}} j'aime la soupe à la {{.}} {{/test}} itération automatique
    test si variable n'existe pas : {{^test}} j'aime la soupe {{/test}}
    partial:        {{>ma_variable}}
*/


/**
* 
*/
class brackets {
    
    # ~

    const VERSION = '1.0.0';

    # ~

    /*
    * ex: $this->set_brackets( array('title'=>'mon titre') )
    * ex: $this->add_brackets('title', 'mon titre')
    * ex: $this->set_template('index.html')   nom du template
    * ex: $this->set_template_directory('/')  répertoire ou se situe les templates
    * ex: $this->add_partials('header', 'header.html')   // selon le répertoire ou se situe les templates
    */

    /**#@+
    * @access private
    * @var
    */
    private $template, $template_directory;
    private $partials = array(), $brackets = array();
    private $private  = array('partials');

    
    /**
    * constructor
    */
    function __construct() {
        
        /* On charge les valeurs des variables par défaut */
        $this->template_directory = dirname(__FILE__) . '/template/';
    }

    /**
    * getter et setter
    */
    function __call($function,$args) {

        $v = strtolower(substr($function,4));
        
        if (!strncasecmp($function,'get_',4) && !in_array($v,$this->private) ) 
            return $this->$v;

        if (!strncasecmp($function,'set_',4) && !in_array($v,$this->private) ) 
            $this->$v = $this->_apply_filter($v, $args[0]);

        if (!strncasecmp($function,'add_',4) && !in_array($v,$this->private) ) {

            if(!is_array($args[0]))
                $args[0] = array( $args[0] => $args[1] );

            $this->$v = array_merge( $this->$v , $args[0] );
        }
    }

    /**
    * filter 
    */
    private function _apply_filter( $v, $args ){

        switch ($v) {

            case 'brackets':
                return parse_args($args);

            case 'template_directory':
                if( is_dir($args) )     return $args;
                break;

            case 'template':
                return @file_get_contents( current( glob($this->template_directory.$args) ) );
                break;
            
            default:
                return $args;
                break;
        }

    }

    /**
    * partials 
    */
    public function add_partials( $name, $template ){

        $name     = (string) $name;
        $template = (string) $template;

        $this->partials[$name] = @file_get_contents( current( glob($this->template_directory.$template) ) );
    }


    /**
    * ob_get_func: attraper la sortie d'une fonction.
    * ob_get_func( 'var_dump', 'bonjour' )
    */
    public function ob_get_func( $function_name ){

        $function_name = (string) $function_name;
        $params        = array_slice(func_get_args(),1);

        if( !is_callable($function_name) ) return;

        ob_start();
        call_user_func_array( $function_name, $params);
        return ob_get_clean();
    }

    /**
    * render
    */
    public function render( $string = false, $brackets = array() , $partials = array() ){

        $string   = $string ?: $this->template;
        $args     = parse_args( $brackets, $this->brackets );
        $partials = array_filter( parse_args( $partials, $this->partials ) );
        $brackets = array();

        // On prépare la table des boucles ainsi que celle des variables
        foreach ($args as $key => $value) {

             if ( is_array( $value ) ){

                // On nettoie pour que seul les tableaux non multi dimenssionnel soit utilisé et filtre les valeurs ( null, '', false )
                $value = array_filter( array_map(function($value){ return !is_array($value) ? $value : null; }, $value ) );

                // On construit la table des arguments
                foreach ($value as $k => $v){
                    $brackets['/[{]{2}'. trim( json_encode($key. '.' .$k), '"') .'[}]{2}/i'] = $v;
                    $args[$key.'.'.$k] = $v;
                }

                // On créer un tableau à scruter
                $args[$key] = $value;

            } else {
                $brackets['/[{]{2}'. trim(json_encode($key),'"') .'[}]{2}/i'] = $value;
            }
        }

        // on filtre les valeurs ( null, '', false ) des arguments
        $brackets = array_filter($brackets);
        $args     = array_filter($args);

        // On parse les patriales
        foreach ($partials as $k => $v) {

            $k = preg_replace( '/[^a-z0-9_-]/', '', strtolower($k) );

            if( is_string($v) && strlen($k)>0 )
                $string = preg_replace( '/[{]{2}>'.trim(json_encode($k),'"').'[}]{2}/i', $v, $string );
        }


        // On scrute les boucles foreach
        foreach ( $args as $key => $value) {

            $key = trim( json_encode($key), '"');

            // On nettoie les boucles not si variables existes
            $brackets['/[\s]*[{]{2}[\^]'.$key.'[}]{2}(.*?)[{]{2}[\/]'.$key.'[}]{2}/si'] = '';

            preg_match_all( '/[{]{2}[#]'.$key.'[}]{2}(.*?)[{]{2}[\/]'.$key.'[}]{2}/si', $string, $matches, PREG_SET_ORDER );

            foreach ($matches as $match) {

                $result = '';

                if( is_array($args[$key]) ){

                    foreach ($args[$key] as $k => $v) {
                        $temp = str_replace( array('{{.}}', '.}}', '.values}}', '.keys}}'), array($v, '.'.$v.'}}', '.'.$v.'}}' , '.'.$k.'}}') , ltrim($match[1]), $count);
                        $result .= ($count == 0) ? '' : $temp;
                    }

                } else {

                    $result = $match[1];
                } 

                // On remplace le contenu par le resultat du parsage de variable
                $string = str_replace($match[0], trim($result), $string);
            }
        }

        // On nettoie les boucles foreach et if residuelle (non utilisé par le template)
        preg_match_all( '/[{]{2}[#](.*?)[}]{2}/', $string, $matches, PREG_SET_ORDER );
        foreach ($matches as $match)
            $brackets['/[\s]*[{]{2}[#]'.$match[1].'[}]{2}(.*?)[{]{2}[\/]'.$match[1].'[}]{2}/si'] = '';


        // On filtre les traductions
        preg_match_all( '/[{]{2}\@(.*?)\@[}]{2}/i', $string, $matches );

        // On traduit le texte ($matches[1]) selon le domaine ($matches[2])
        $matches[1] = array_map( function($v){ return esc_html__( trim($v) ); } , $matches[1] );
        $string     = str_replace( $matches[0], $matches[1], $string );

        // On nettoie les commentaires
        $brackets['/[\s]*[{]{2}!([^{]*)[}]{2}/'] = '';

        // Ajour Regex pour supprimer tous les brackets sans arguments
        $brackets['/[{]{2}[\w. \/^]*[}]{2}/'] = '';

        // On parse les variables
        $string = preg_replace(array_keys($brackets), $brackets, $string);

        return trim($string);
    }

}