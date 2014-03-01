<?php
/*
 * Direitos Autorais (C) 2014 Wagner Hahn Silveira.
 *
 * Autor:
 *	Wagner Hahn Silveira <wagnerhsilveira@gmail.com>
 *
 * Este software é licenciado sob os termos da Licença Pública Geral GNU
 * License versão 2, como publicada pela Fundação de Software Livre, e
 * pode ser copiada, distribuida, e modificada sob estes termos.
 *
 * Este programa é distribuido na esperança que será util,
 * mas SEM NENHUMA GARANTIA; sem mesmo a garantia implícita de 
 * COMERCIALIZAÇÃO ou de ADEQUAÇÃO A UM DETERMINADO FIM. veja o 
 * Licença Pública Geral GNU para obter mais detalhes.
 *
 */
if(extension_loaded('imap')){
include('Imap.class.php');
if (php_sapi_name()=='cli'){
	$parametros= array('host1:','usuario1:','senha1:','tipo1:','ssl1::','host2:','usuario2:','senha2:','tipo2:','ssl2::','ignorarespaco::');
	$argumentos=getopt(null,$parametros); 
		
	$tipo1=(!isset($argumentos['tipo1']))? 'imap' :$argumentos['tipo1'];
	$tipo2=(!isset($argumentos['tipo2']))? 'imap' :$argumentos['tipo2'];
	
	$ssl1=(isset($argumentos['ssl1']))? '1' : '0';
	$ssl2=(isset($argumentos['ssl1']))? '1' : '0';
	
	$origem = new Imap($argumentos['host1'],$argumentos['usuario1'],$argumentos['senha1'],$tipo1,$ssl1);
	$destino= new Imap($argumentos['host2'],$argumentos['usuario2'],$argumentos['senha2'],$tipo2,$ssl2);
	
}else{

	$origem = new Imap($_GET['host1'],$_GET['usuario1'],$_GET['senha1'],$_GET['tipo1'],$_GET['ssl1']);
	$destino= new Imap($_GET['host2'],$_GET['usuario2'],$_GET['senha2'],$_GET['tipo2'],$_GET['ssl2']);
}

//$origem= new Imap(string host, string usuario ,string senha,string tipo, bool ssl);
//$destino=  new Imap(string host, string usuario ,string senha,string tipo, bool ssl);

$inicio=date('d/m/Y -- H:i:s');
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

echo "\n";
echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
echo 'Iniciando migracao em '.$inicio."\n";
echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
echo "\n";
echo "--- Informacoes da conta - ORIGEM --- \n";
echo $origem->verificarInfoQuota();
echo "Prefixo: ".$origem->verificarPrefixo()."\n";
echo "Separador: ".$origem->verificarTipoSeparador()."\n";
echo "--- Informacoes da conta - DESTINO --- \n";
echo $destino->verificarInfoQuota();
echo "Prefixo: ".$destino->verificarPrefixo()."\n";
echo "Separador: ".$destino->verificarTipoSeparador()."\n";
echo "\n";
echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";

if(!isset($argumentos['ignorarespaco'])){
	if($origem->quotaEmUso > $destino->quotaDisponivel){
	     $espaco= $origem->quotaEmUso - $destino->quotaDisponivel;
		echo " Nao sera possivel iniciar a migracao dos emails \n Sera necessario adicionar mais ".$destino->ajustarMedida($espaco)." de espaco a conta $destino->usuario \n";
		echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
		echo "\n";
		exit;
	}
}
$stream= fopen("php://output",'w');
$destino->listarMailBox();
$destino->verificarTipoSeparador();
echo "\n";
echo 'Verificando pastas  na conta '.$destino->usuario."\n";
echo "\n";
echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);
	fwrite($stream, $destino->criarMailboxInexistentes($origem,$pastasOrigem));
	$destino->limparImapCache($origem);
}

echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
echo "\n";
echo 'Buscando por mensagens inexistentes'."\n";
echo "\n";
echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);
    fwrite($stream, "Verificando conteudo na pasta $pastasOrigem \n");

	if($destino->verificarMensagensDuplicadas($origem,$pastasOrigem)){
		$mensagensNaoExistentes= count($destino->verificarMensagensDuplicadas($origem,$pastasOrigem));
		echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
		echo "Mensagens nao existentes da pasta $pastasOrigem: $mensagensNaoExistentes \n";
		echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
		foreach ($destino->verificarMensagensDuplicadas($origem,$pastasOrigem) as $key=>$uid){

			if($origem->keepAlive()){
				echo "Conexao perdida com o host de origem";	
				exit;
			}
			if($destino->keepAlive()){
				echo "Conexao perdida com o host de destino";
				exit;
			}
			fwrite($stream,'('.("$key"+1).")  ".$destino->migrarMensagensImap($origem,$pastasOrigem,$uid));
			 $destino->limparImapCache($origem);
		
			//echo $key." -->".$destino->setarFlags($origem,$uid)."\n";	
		}
	}
}
echo "++++++++++++++++++++++++++++++++++++++++++++++ \n";
echo "\n";
echo "ESTATISTICAS\n";
echo $destino->gerarEstatisticas()."\n";
echo "Migracao Concluida em ".date('d/m/Y -- H:i:s')."\n";
echo "\n";
echo "++++++++++++++++++++++++++++++++++++++++++++++ \n";
}else{
  echo "A extensão Imap para PHP não está ativa\nPor favor contatar o administrador";
}
?>  
