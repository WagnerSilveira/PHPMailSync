#!/usr/bin/php-cgi
<?php
/*http://www.linuxformat.com/wiki/index.php/PHP_-_Forking_processes */
session_start();
include('../persistencia/phpmailsyncDao.php');
include('../classes/Smtp.class.php');
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         $dados = $_SESSION["dados"];
        unset($_SESSION["dados"]);
        $ppid= getmypid();
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
                        function sig_handler($signo){
                                switch ($signo) {
                                        case SIGTERM:
                                        exit;
                                        break;
                                        case SIGHUP:
                                        break;
                                        case SIGUSR1:
                                        break;
                                        default:
                                }
                        }
                        pcntl_signal(SIGTERM, "sig_handler");
                        pcntl_signal(SIGHUP,  "sig_handler");
                        pcntl_signal(SIGUSR1, "sig_handler");
                                            
                        
                       /*****************************************************************************/ 
                        $phpmailsyncDao =  new phpmailsyncDao();
		        $phpmailsyncDao->iniciarExecucao($conta,$ppid,$pid,$status,$logs,$idmigracao);
		        unset($phpmailsyncDao);

		        /*****************************************************************************/
		                sleep(220);
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
