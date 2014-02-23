<?php
include('Imap.class.php');
$parametros= array('host1:','usuario1:','senha1:','tipo1:','ssl1:','host2:','usuario2:','senha2:','tipo2:','ssl2:');
$argumentos=getopt(null,$parametros); 

//$origem= new Imap(string host, string usuario ,string senha,string tipo, bool ssl);
//$destino=  new Imap(string host, string usuario ,string senha,string tipo, bool ssl);

$origem = new Imap($argumentos['host1'],$argumentos['usuario1'],$argumentos['senha1'],$argumentos['tipo1'],$argumentos['ssl1']);
$destino= new Imap($argumentos['host2'],$argumentos['usuario2'],$argumentos['senha2'],$argumentos['tipo2'],$argumentos['ssl2']);


if(!$origem->testarConexao()){
	echo "Nao foi possivel conectar o servidor de origem, o servidor nao respondeu atraves do endereco: $origem->servidor  na porta  $origem->porta \n";
	exit;
}
if(!$origem->conectar()){
	echo "Falha de autenticacao no servidor de origem: $origem->servidor com a conta $origem->usuario \n";
	exit;
}
if(!$destino->testarConexao()){
	echo "Nao foi possivel conectar o servidor de destino, o servidor nao respondeu atraves do endereco: $destino->servidor na porta $destino->porta \n";
	exit;
}
if(!$destino->conectar()){
	echo "Falha de autenticacao no servidor de destino: $destino->servidor com a conta $destino->usuario \n";
	exit;
}


$stream= fopen("php://output",'w');
$destino->listarMailBox();
$destino->verificarTipoSeparador();

 foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);
	 fwrite($stream, $destino->criarMailboxInexistentes($origem,$pastasOrigem));
	 $destino->limparImapCache($origem);
	 gc_collect_cycles();
	 gc_disable();
}

 foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);

     fwrite($stream, "Verificando conteudo na pasta $pastasOrigem \n");
	if($destino->verificarMensagensDuplicadas($origem,$pastasOrigem)){
		
		foreach ($destino->verificarMensagensDuplicadas($origem,$pastasOrigem) as $uid){
			 fwrite($stream, $destino->migrarMensagensImap($origem,$pastasOrigem,$uid));
			 $destino->limparImapCache($origem);
			 gc_enable();

		}
		gc_collect_cycles();
		gc_disable();
	}
} 
?>  
