<?php

/**
 * YAML PARSER - DUMP
 *
 * Specifications : http://www.yaml.org/spec/1.2/spec.html
 *
 * (c) stephen deletang
 *
 * @package cms mini POPS
 * @subpackage yaml parser-dump
 * @version 1
 */


/***********************************************/
/*       Fonction d'appel yaml                 */
/***********************************************/

/**
* Parser un flux texte yaml
* @access private
* @param $string  contenu du flux
*/
if (!function_exists('yaml_parse')) {
    function yaml_parse( $string, $pos = 0, $ndocs = null ){
        $yaml_parse = new YAML();
        return $yaml_parse->parse($string, $pos, $ndocs);
    }
}


/**
* Parser un fichier yaml
* @access private
* @param $path  chemin du fichier
*/
if (!function_exists('yaml_parse_file')) {
    function yaml_parse_file( $path, $pos = 0, $ndocs = null ){
        $yaml_parse = new YAML();
        return $yaml_parse->parse_file( $path, $pos, $ndocs );
    }
}


/**
* Parser une table vers un flux yaml
* @access private
* @param $array  table de donnée
*/
if (!function_exists('yaml_emit')) {
    function yaml_emit( $data ){
        $yaml_parse = new YAML();
        return $yaml_parse->emit( $data );
    }
}


/**
* Parser une table vers fichier yaml
* @access private
* @param $array  table de donnée
*/
if (!function_exists('yaml_emit_file')) {
    function yaml_emit_file( $path, $data ){
        $yaml_parse = new YAML();
        return $yaml_parse->emit_file($path, $data);
    }
}



/***********************************************/
/*                  Class yaml                 */
/***********************************************/


class yaml {

    # ~

    const VERSION = '1.0.0';

    # ~


    /**#@+
    * @access private
    * @var array
    */
    private $TAB = '    ';
    private $encode_iterator;
    private $yaml_file_header = "# YAML - minipopS CMS.\n\n";


    # ~

    /***********************************************/
    /*              Functions privates             */
    /***********************************************/

    # ~

    /**
    * delete yaml commment
    * @access private
    * @param $string
    */
    private function _delete_comment( $string ){

        $comment = strstr($string, '#', true);
        return (false !== $comment) ? $comment : $string;
    }


    /**
    * Detect type encode
    * @param $string
    */
    private function detect_encoding( $string ) {

        if ( function_exists( 'mb_internal_encoding' ) ) {
          return strtolower ( mb_detect_encoding( $string , 'UTF-8, ISO-8859-1, windows-1251') );
        } else {
            foreach( array('utf-8', 'iso-8859-1', 'windows-1251') as $item )
                if( md5( iconv( $item , $item , $string ) ) == md5( $string ) ) return $item;
          return false;
        }
    }

    /**
    * Encode utf-8
    * @param $string
    */
    private function encode_utf8( $string ){

        $encoding = detect_encoding( $string );
        if( is_same( $encoding , 'utf-8') ) 
            return $string;
        return iconv( $encoding , 'utf-8' , $string );
    }

    /**
     * Escape the dangerous characteres
     */
    private function esc_attr( $value ) {

        $char = array('/%0[0-8bcef]/', '/%1[0-9a-f]/', '/[\x00-\x08]/', '/\x0b/', '/\x0c/', '/[\x0e-\x1f]/');
        do {
            $cleaned = $value;
            $value = preg_replace( $char , '' , $value );
        } while ( $cleaned != $value );

        return $value;
    }

    /**
    * encode the value to yaml format
    * @access public
    * @param $data          string      encode value
    * @param $intends       int         number intends
    * @param $offset        int         offset
    */
    private function _yaml_encode_value( $value, $i, $lenght ){

        $indents = "\n". str_repeat($this->TAB , $i) . str_repeat(" " , $lenght);

        if(     $value === true  )                  return "true";
        elseif( $value === false )                  return "false";
        elseif( $value === null  )                  return "~";
        elseif( strpos($value, "\n") !== false )
            return '| '. $indents . str_replace("\n", "$indents", $value) ."\n";
        elseif( strlen($value) > 70 )
            return '> '. $indents . str_replace( "\n", "$indents", wordwrap($value, 70, "\n") ) ."\n";

        return $value;
    }

    /**
    * encode a array to yaml format
    * @access private
    * @param $data   array      array data
    */
    function _yaml_encode( $data ){

        static $i = 0;

        foreach( $data as $k => $v ) {

            // sainitize key if a string
            $k = is_string($k) ? preg_replace( '/[^a-z0-9\/_-]/', '', strtolower($k) ) : $k;

            // If v a array and is null alse value is null
            if( is_array($v) && empty($v) )   $v = null;

            if (  is_array($v) ){
                if( is_integer($k) )
                    $this->encode_iterator .= str_repeat($this->TAB , $i) .'- '. json_encode($v) ."\n";
                else {
                    $this->encode_iterator .= str_repeat($this->TAB , $i) . $k .': '."\n";
                    $i++;
                    $this->_yaml_encode($v);
                    $i--;
                }
            } else {
                if( is_integer($k) )
                    $this->encode_iterator .= str_repeat($this->TAB , $i) .'- '. $this->_yaml_encode_value($v, $i, 2) ."\n";
                else
                    $this->encode_iterator .= str_repeat($this->TAB , $i) . $k .': '. $this->_yaml_encode_value($v, $i, strlen($k)+3) ."\n";
            }
        }

        return $this->encode_iterator;
    }





    /**
    * Insert in array
    * @access private
    * @param $arr array  tableau d'origine
    * @param $ins array  tableau à insérer
    */
    private function _array_insert( $arr ,$ins ) {

        if( is_array($arr) && is_array($ins) ){
            foreach( $ins as $k => $v ) {
                if ( isset($arr[$k]) && is_array($v) && is_array($arr[$k]) )
                    $arr[$k] = $this->_array_insert($arr[$k],$v);
                else $arr[$k] = $v;
            }
        }
        return($arr);
    }


    /**
    * Insérer une valeur selon un noeud yaml dans une table
    * @access private
    * @param $array_keys array  Table des noeuds
    * @param $yaml_data  array  Table de retour
    * @param $value  string/array  Données à insérer dans la table
    */
    private function SetValueByNodeToArray ( $array_keys, &$yaml_data, $value = '' ){

        if( !is_bool($value) && !is_null($value) && empty($value) ) return;
        $dump_node = array();
        $ref = &$dump_node;
        foreach ($array_keys as $k)  $ref = &$ref[$k];
        $ref = $value;
        $yaml_data = $this->_array_insert($yaml_data, $dump_node);
    }

    /**
    * Récupère une valeur selon un noeud dans une table
    * @access private
    * @param $array_keys array  Table des noeuds
    * @param $yaml_data  array  Table de retour
    */
    private function GetValueByNodeFromArray ( $array_keys, $yaml_data ){
        $ref = &$yaml_data;
        foreach ($array_keys as $k)
            if(!isset($ref[$k]) ) return; else $ref = &$ref[$k];
        return $ref;
    }

    /**
    * Recherche le nombre de document yaml
    * @access private
    * @param $pos position du premier document recherché
    * @param $ndocs nombre de document retourné
    */
    private function _parse_ndocs( $text, $offset, $lenght  ){

        if( preg_match_all('/^[-]{3}(.*?)[\n]+[.]{3}/mis', $text , $match ) )
            return array_slice( $match[1] , $offset, $lenght, true );
        else
            return array();
    }


    /**
    * Parse un document yaml sous forme de tableau
    * @access private
    * @param $yaml_data  string à parser
    */
    private function _parse_yaml_data( $yaml_data){

        // On extrait la première ligne du document
        $line_1 = trim( strstr($yaml_data, "\n", true) );

        // Les données du document yaml
        $data = strstr($yaml_data, "\n");

        // On init $yaml_data pour la table de retour
        $yaml_data = array();

        /**************************************************************/
        /*      GESTION DE BLOCK ITERATOR EN RACINE DE DOCUMENT       */
        /**************************************************************/

        // On supprime le commentaire si présent
        $line_1 = $this->_delete_comment($line_1);

        // Block iterator multi-ligne
        if( false !== strpos($line_1, '|') )
            return $data;

        // Block iterator multi-ligne type code source
        if( false !== strpos($line_1, '>') )
            return trim( preg_replace( '/[\r\n\t ]+/', ' ', $data ) );

        /**********************************/
        /*      ON PREPARE THE LOOP       */
        /**********************************/

        // Extraire data ligne par ligne ( on ajoute un END au document pour garantir une bonne fouille )
        $data = explode( "\n", $data."\n#END");

        // nbr lignes dans les données
        $count_data = count($data);

        // Var pour block iterator
        $init_block_scalar = array( 'enable' => false, 'type' => '', 'position' => 0, 'value' => '' );
        $block_scalar = $init_block_scalar;

        // init pour les noeuds
        $yaml_node = array();

        // init les alias yaml
        $yaml_alias = array();

        /***********************/
        /*      THE LOOP       */
        /***********************/

        // var de comptage de lignes
        $i = 0;

        // On lance la fouille dans les données
        for ($i=0; $i < $count_data ; $i++) {

            // THE LINE
            $line = $data[$i];

            /***********************/
            /* BLOCK ITERATOR ONLY */
            /***********************/

            // On stock les lignes pour les blocks iterator
            if( $block_scalar['enable'] === true ){

                // On remplace les tablulations par des espaces
                $line = str_replace("\t", $this->TAB , $line);

                // On conserve les lignes vides
                if( strlen( trim($line) ) === 0 )
                    $block_scalar['value'] .= "\n";

                // On récupère uniquement les lignes intendées
                elseif( preg_match('/^[ ]{'.$block_scalar['position'].'}(.*)/', $line, $match ) )
                    $block_scalar['value'] .= $match[1]."\n";

                else{

                    // On revient d'une ligne pour checker son contenu
                    $i--;

                    // On prépare les données
                    $block_scalar['value'] = $block_scalar['type'] === '>' ? trim( preg_replace('/[\n ]+/', ' ', $block_scalar['value']) ) : $block_scalar['value'];

                    // On stock les données dans la table
                    $this->SetValueByNodeToArray( $yaml_node, $yaml_data, $block_scalar['value'] );

                    // init du block
                    $block_scalar = $init_block_scalar;

                }

            }

            /***********************/
            /*   INLINE ITERATOR   */
            /***********************/

            // On recherche les iterators ( tableau ou liste)
            elseif( preg_match('/^([ \t]*)([\/\-\w]+)[ \t]*:[ \t]*(.*)/', $line, $match ) || preg_match('/^([ \t]*)[\-]+[ \t]*(.*)/', $line, $match ) ){

                // L'intends du tableau de retour
                $indents = (int) round ( strlen( str_replace("\t", $this->TAB , $match[1]) )/4 );

                // On prépare les valeurs
                if( isset($match[3]) ) {

                    $key = $match[2];
                    // On supprime le commentaire si présent
                    $value = $this->_delete_comment($match[3]);
                }
                else {

                    // On récupère la clé en cours de l'iterator type liste
                    $key = isset($yaml_node[$indents]) ? $yaml_node[$indents]+1 : 0;
                    // On supprime le commentaire si présent
                    $value = $this->_delete_comment($match[2]);
                    // On recherche si table tel que "- label : texte"
                    if( preg_match('/^([\/\-\w]+)[ \t]*:[ \t]*(.*)/', $value, $match) )
                        $value = '{"'. $match[1] .'":"'. $match[2].'"}';
                }

                // On construire le noeud
                $yaml_node[$indents] = $key;
                $yaml_node = array_slice( $yaml_node, 0, $indents+1, true);

                // mode de décodage
                $decode_value = 'value';

                // On recherche si un alias est déclaré
                // Un alias est tjrs déclaré en debut de la chaine de valeur et sa valeur est le reste de la chaine ex: &prenom stephen 
                if( strlen($value)>1 && '&' === $value[0] ){

                    // On récupere le nom du alias
                    $alias = explode(' ', substr($value,1), 2 );

                    // On valid le nom de l'alias
                    if( preg_match( '/^[a-z0-9]+$/i', $alias[0] ) == true ){

                        // On stock le chemin de la valeur du alias
                        $yaml_alias[ $alias[0] ] = $yaml_node;
                        // On renvoi la valeur à décoder
                        $value = !empty($alias[1]) ? $alias[1] : null;
                    }
                }

                // On recherche si un alias est utilisé
                if( strlen($value)>1 && '*' === $value[0] ){

                    // On récupere le nom du alias
                    $alias = explode(' ', substr($value,1), 2 );

                    // On verifie que le nom de l'alias est valide
                    if( array_key_exists($alias[0], $yaml_alias) )
                        $decode_value = $value = '';
                }

                // On recherche si un format doit etre appliqué a la valeur
                if( strlen($value)>2 && '!!' === $value[0].$value[1]  ){

                    // On le format
                    $format = explode(' ', substr($value,2), 2 );

                    if( in_array($format[0], array('str','bool','int','float','seq','map','binary','null','timestamp','value'), true ) ){
                        $decode_value = $format[0];    
                        $value = !empty($format[1]) ? $format[1] : null;
                    }
                }

                // On checked si block iterator
                if( strlen($value)>1 && '>' === $value[0] )
                    $block_scalar = array( 'enable' => true, 'type' => '>', 'position' => strpos($line, '>'), 'value' => '' );

                elseif( strlen($value)>1 && '|' === $value[0] )
                    $block_scalar = array( 'enable' => true, 'type' => '|', 'position' => strpos($line, '|'), 'value' => '' );

                else{

                    // On nettoie la valeur des espaces superflus
                    $value = trim($value);

                    switch ($decode_value) {

                        case 'value':
                            $value = json_decode($value, true) ?: $value;
                            if( in_array($value, array('y','Y','yes','Yes','YES','true','True','TRUE','on','On','ON') ) )
                                $value = true;
                            elseif( in_array($value, array('n','N','no','No','NO','false','False','FALSE','off','Off','OFF') ) )
                                $value = false;
                            elseif( in_array($value, array('null','Null','NULL','~') ) )
                                $value = null;
                            break;

                        case 'str':
                            $value = is_string($value) ? $value : null;
                            break;

                        case 'int':
                            $value = is_integer($value) ? $value : null;
                            break;

                        case 'float':
                            $value = is_float($value) ? $value : null;
                            break;

                        case 'bool':
                            if( in_array($value, array('y','Y','yes','Yes','YES','true','True','TRUE','on','On','ON') ) )
                                $value = true;
                            elseif( in_array($value, array('n','N','no','No','NO','false','False','FALSE','off','Off','OFF') ) )
                                $value = false;
                            else
                                $value = null;
                            break;

                        case 'binary':
                            if( preg_match( '/^[a-z0-9+\/=]+$/i' , $value ) == false )
                                $value = null;
                            break;

                        case 'seq':
                        case 'map':
                            $value = json_decode($value, true) ?: null;
                            break;

                        case 'null':
                            $value = null;
                            break;

                        case 'timestamp':
                            $time = strtotime( $value );
                            if(!$time)    $value = null;
                            else{
                                $year  = date('Y', $time);
                                $month = date('m', $time);
                                $day   = date('d', $time);
                                if( false == checkdate( $month , $day , $year ) )  $value = null;
                                $value = date('c',$time);
                            }
                            break;
                        
                        default:
                            $value = $this->GetValueByNodeFromArray($yaml_alias[$alias[0]], $yaml_data );
                            break;
                    }

                    // On sauvegarde le valeur selon le noeuds trouvé
                    $this->SetValueByNodeToArray( $yaml_node, $yaml_data, $value );
                }
            }
        }

        return $yaml_data;
    }



    # ~

    /***********************************************/
    /*              Public functions               */
    /***********************************************/

    # ~


    /**
    * parse flux yaml
    * @access public
    * @param $text  string      flux texte
    * @param $pos   int         numero de document ou commencer la recherche
    * @param $ndocs int         Nombre de documents recherche
    */
    public function parse( $text, $pos = 0, $ndocs = null ){

        if( !is_string($text) )                      return false;
        if( !is_integer($pos) )                      return false;
        if( !is_integer($ndocs) && $ndocs !== null ) return false;

        // On encode en utf-8
        $text = $this->encode_utf8( $text );

        // On supprime les caractères invisibles
        $text = $this->esc_attr($text);

        // On supprime les retour chariots
        $text = str_replace(array("\r\n", "\r"), "\n", $text);

        if ( strlen($text) == 0 ) return array();

        // On parse chaque documents retenus
        $_ndocs = $this->_parse_yaml_data( implode($this->_parse_ndocs( $text, $pos, $ndocs ) ) );

        return $_ndocs;
    }


    /**
    * parse fichier yaml
    * @access public
    * @param $path   string      Chemin du fichier
    * @param $pos    int         numero de document ou commencer la recherche
    * @param $ndocs  int         Nombre de documents recherche
    * @param $cached bool        Activation du cache
    */
    public function parse_file( $path, $pos = 0, $ndocs = null ){

        if( is_readable($path) && false !== $text = file_get_contents($path) )
            return $this->parse( $text, $pos, $ndocs );
        return false;
    }


    /**
    * créer une représentation yaml
    * @access public
    * @param $data   array/string      Table des donnée ou texte à encoder
    */
    public function emit( $data ){

        if( is_array($data) ){
            $this->encode_iterator = '';
            $yaml_emit = $this->_yaml_encode( $data );
            if ( strlen($yaml_emit) == 0 ) return false;
            return "---\n$yaml_emit...\n";

        } elseif( is_string($data) ){

            if ( strlen($data) == 0 ) return false;
            if( strpos($data, "\n") !== false )
                return "--- |\n$data\n...\n";
            else
                return "---  >\n" . wordwrap($data, 70, "\n") . "\n...\n";
        }
        else return false;
    }


    /**
    * créer une représentation yaml dans un fichier
    * @access public
    * @param $data   array/string      Table des donnée ou texte à encoder
    */
    public function emit_file( $path, $data ){

        if( $yaml_emit = $this->emit($data) )
            return @file_put_contents($path, $this->yaml_file_header.$yaml_emit, LOCK_EX);
        else return false;
    }
}


