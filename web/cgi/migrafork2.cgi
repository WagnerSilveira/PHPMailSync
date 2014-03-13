#!/usr/bin/php-cgi
<?php
/*http://www.linuxformat.com/wiki/index.php/PHP_-_Forking_processes */
session_start();
include('../persistencia/phpmailsyncDao.php');
include('../classes/Smtp.class.php');
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         $dados = $_SESSION["dados"];
        unset($_SESSION["dados"]);
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        foreach($dados as $key => $processos){
        $processos  =  explode(";", $processos);
                $pid = pcntl_fork();
                if (!$pid) {
	        
	                $saida = 0;
                        $idmigracao=  $processos[0];
                        $conta=  $processos[7];
                        $pid=getmypid();
                        $status=1;
                        $logs =$processos[11]."[".uniqid()."].log";                                         
                       /*****************************************************************************/ 
                        $phpmailsyncDao =  new phpmailsyncDao();
		        $phpmailsyncDao->iniciarExecucao($conta,$pid,$status,$log,$idmigracao);
		        unset($phpmailsyncDao);

		        /*****************************************************************************/
		          sleep(40);
		        /*****************************************************************************/
		           $phpmailsyncDao =  new phpmailsyncDao();
                           $phpmailsyncDao->atualizarStatus($pid,$idmigracao);
                           $valor = $phpmailsyncDao->verificarStatusGeral($idmigracao);
                           if($valor == 0){
                               $phpmailsyncDao->atualizarStatusGeral($idmigracao);
                          } 
                        /*****************************************************************************/
		         exit($key); 
	          }else{

	          }
        }
               
               
          # while (pcntl_waitpid(0, $status) != -1) {
             #    $status = pcntl_wexitstatus($status);
           #}
           
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        header_remove(); 
        $send = serialize($dados);//trasnforma o array em string
        $send = urlencode($send);
        header("Location: redirect.cgi?dados=$send");               


?>
