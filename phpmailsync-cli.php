<?php
/*
 * Direitos Autorais (C) 2014 Wagner Hahn Silveira.
 *
 * Autor:
 *      Wagner Hahn Silveira <wagnerhsilveira@gmail.com>
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
include('PhpMailSync.class.php');

$stream= fopen("php://output",'w');

if (php_sapi_name()=='cli'){

	$parametros= array('host1:','usuario1:','senha1:','tipo1:','ssl1::','host2:','usuario2:','senha2:','tipo2:','ssl2::','ignorarespaco::');
	$argumentos=getopt(null,$parametros); 
		
	$tipo1=(!isset($argumentos['tipo1']))? 'imap' :$argumentos['tipo1'];
	$tipo2=(!isset($argumentos['tipo2']))? 'imap' :$argumentos['tipo2'];
	
	$ssl1=(isset($argumentos['ssl1']))? '1' : '0';
	$ssl2=(isset($argumentos['ssl2']))? '1' : '0';
	
	$origem = new PhpMailSync($argumentos['host1'],$argumentos['usuario1'],$argumentos['senha1'],$tipo1,$ssl1);
	$destino= new PhpMailSync($argumentos['host2'],$argumentos['usuario2'],$argumentos['senha2'],$tipo2,$ssl2);
	
}else{
        fwrite($stream,'Este script deve ser executado pela linha de comando'."\n");
        exit();
}


$inicio=date('d/m/Y -- H:i:s');
if(!$origem->testarConexao()){
	fwrite($stream,"Nao foi possivel conectar o servidor de origem, o servidor nao respondeu atraves do endereco: $origem->servidor  na porta  $origem->porta \n");
	exit;
}
if(!$origem->conectar()){
	fwrite($stream,"Falha de autenticacao no servidor de origem: $origem->servidor com a conta $origem->usuario \n");
	exit;
}
if(!$destino->testarConexao()){
	fwrite($stream,"Nao foi possivel conectar o servidor de destino, o servidor nao respondeu atraves do endereco: $destino->servidor na porta $destino->porta \n");
	exit;
}
if(!$destino->conectar()){
	fwrite($stream,"Falha de autenticacao no servidor de destino: $destino->servidor com a conta $destino->usuario \n");
	exit;
}

fwrite($stream,
     "\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++'."\n".
     'Iniciando migracao em '.$inicio."\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++'."\n".
     "\n"."\n".
     '--- Informacoes da conta - ORIGEM --- '."\n".
      $origem->verificarInfoQuota().
     'Prefixo: '.$origem->verificarPrefixo()."\n".
     'Separador: '.$origem->verificarTipoSeparador()."\n".
     '--- Informacoes da conta - DESTINO --- '."\n".
     $destino->verificarInfoQuota().
     'Prefixo: '.$destino->verificarPrefixo()."\n".
     'Separador: '.$destino->verificarTipoSeparador()."\n".
     "\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++'."\n"
     );


if(!isset($argumentos['ignorarespaco'])){
	if($origem->quotaEmUso > $destino->quotaDisponivel){
	     $espaco= $origem->quotaEmUso - $destino->quotaDisponivel;
		fwrite($stream,
		'Nao sera possivel iniciar a migracao dos emails'."\n".
		'Sera necessario adicionar mais '.$destino->ajustarMedida($espaco).' de espaco a conta '.$destino->usuario."\n".
		'+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n\n");
		exit;
	}
}

$destino->listarMailBox();
$destino->verificarTipoSeparador();
fwrite($stream,
     "\n".
     'Verificando pastas  na conta '.$destino->usuario."\n".
     "\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n" ); 
     
foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);
	fwrite($stream, $destino->criarMailboxInexistentes($origem,$pastasOrigem));
	$destino->limparImapCache($origem);
}

fwrite($stream,
     '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n".
     "\n".
     'Buscando por mensagens inexistentes'."\n".
     "\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n");
     
     
foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);
   	fwrite($stream, "Verificando conteudo na pasta $pastasOrigem \n");

	if($mensagensNaoExistentes=$destino->verificarMensagensDuplicadas($origem,$pastasOrigem)){
		$msgsNaoExistentes=count($mensagensNaoExistentes);		
		echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
		echo "Mensagens nao existentes da pasta $pastasOrigem: $msgsNaoExistentes		      \n";
		echo '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n";
		foreach ($mensagensNaoExistentes as $key=>$uid){

			if($origem->keepAlive()){
				echo "Conexao perdida com o host de origem\n";	
				exit;
			}
			if($destino->keepAlive()){
				echo "Conexao perdida com o host de destino\n";
				exit;
			}
			fwrite($stream,'('.("$key"+1).")  ".$destino->migrarMensagensImap($origem,$pastasOrigem,$uid));
			$destino->limparImapCache($origem);
						
		}
	}
}
echo "++++++++++++++++++++++++++++++++++++++++++++++ \n";
echo "\n";
echo "ESTATISTICAS\n";
echo $destino->gerarEstatisticas()."\n";
echo "Migracao iniciada em:  $inicio \n";
echo "Migracao concluida em: ".date('d/m/Y -- H:i:s')."\n";
echo "\n";
echo "++++++++++++++++++++++++++++++++++++++++++++++ \n";
}else{
  echo "A extensão Imap para PHP não está ativa\nPor favor contatar o administrador";
}
?>  
