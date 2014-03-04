<?php

/**
 * Description of Conexao
 *
 * @author Wagner Silveira
 */
 
include('../config.php');
class Conexao extends PDO {
      
     private static $conectar;
     
     public function __construct($dsn, $usuario, $senha){
         parent::__construct($dsn, $usuario, $senha);
     }
     
    public static function conectarBase(){
          $config =  new Config(); 
       if(!isset(self::$conectar)){
             try{
                 self::$conectar = new Conexao("$config->driver:host=$config->servidorBd;dbname=$config->bancoDeDados","$config->usuarioBd","$config->senhaBd");
             }catch (Exception $exception){
                 return ("Erro ao estabelecer conexão com o banco de dados <br/>
                       <pre>$exception</pre> ");
                 
             }
         }
         return self::$conectar;
    }
    

}
?>
