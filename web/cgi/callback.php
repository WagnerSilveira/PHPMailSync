<?php
declare(ticks = 1); 
include('../persistencia/phpmailsyncDao.php');                        

$conta='teste3@wagnersilveira.kinghost.net';
$pid='145';
$ppid=254;
$status=1;
$logs='teste.log_53207ac7c4b39.log';
$idmigracao='5320713764a7c';


$phpmailsyncDao =  new phpmailsyncDao();
$phpmailsyncDao->iniciarExecucao($conta,$ppid,$pid,$status,$logs,$idmigracao);
pcntl_signal(SIGTERM,function($signo){
	echo 'teste';
});

pcntl_signal(SIGINT, function(){
global $pid, $idmigracao;

$phpmailsyncDao =  new phpmailsyncDao();
$phpmailsyncDao->atualizarStatus($pid,$idmigracao);
$valor = $phpmailsyncDao->verificarStatusGeral($idmigracao);
if($valor == 0){
	$phpmailsyncDao->atualizarStatusGeral($idmigracao);
}
exit;
});

while(1){
//
}
?>
