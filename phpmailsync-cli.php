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



/**
*     Captura logs  e coloca no arquivo phpmailsync_erros.log na pasta temporaria do servidor
*/
ini_set("log_errors", 1); 
ini_set( 'error_log', sys_get_temp_dir().'/phpmailsync_erros.log' );
/*

*/

//error_reporting(0);

if(extension_loaded('imap')){
include('PhpMailSync.class.php');
if (php_sapi_name()=='cli'){
 
        /**
	*
	*       Recebe os parametros vindos da linha de comando 
	*       
        *       Obrigatorios: 
	*       --host1 
	*       --usuario1
	*       --senha1
	*       --tipo1
	*       --ssl1 
	*
	*       --host2
	*       --usuario2
	*       --senha2
	*       --tipo2
	*       --ssl2
	*
	*       Opcionais
	*       --ignorarespaco
	*/
	$parametros= array('host1:','usuario1:','senha1:','tipo1:','ssl1::','host2:','usuario2:','senha2:','tipo2:','ssl2::','ignorarespaco::','help');
	$argumentos=getopt(null,$parametros); 
	$tipo1=(!isset($argumentos['tipo1']))? 'imap' :$argumentos['tipo1'];
	$tipo2=(!isset($argumentos['tipo2']))? 'imap' :$argumentos['tipo2'];
	$ssl1=(isset($argumentos['ssl1']))? '1' : '0';
	$ssl2=(isset($argumentos['ssl2']))? '1' : '0';
	if(isset($argumentos['h']) || isset($argumentos['help'])){
echo "\n++++ AJUDA PHPMailSync ++++ \n
Repositorio: https://github.com/wagner852/PHPMailSync \n
=== Parametros Origem  === \n
*** Obrigatorios *** 
--host1         <string> : Servidor de origem
--usuario1      <string> : Conta de email na origem
--senha1        <string> : Senha da conta de email na origem
**Opcionais** 
--tipo1         <string> : Padrão imap, imap/pop3
--ssl1                   : Utilizar SSL para a conexao na origem\n
=== Parametros Destino  === \n
*** Obrigatorios ***
--host2         <string> : Servidor de destino
--usuario2      <string> : Conta de email na destino
--senha2        <string> : Senha da conta de email na destino
**Opcionais**
--tipo2         <string> : Padrão imap, imap/pop3
--ssl2                   : Utilizar SSL para a conexao na destino\n
=== Parametros Adicionais ===\n
--ignorarespaco          : Permite iniciar a migração caso o espaço disponível na conta de destino for menor que o utilizado na conta de origem
--help ou -h             :  Abre o item de ajuda
++++++++++++++++++++++++++++++ \n
";
     exit();
	}
	
	
	/**
	*
	*
	*       Testas se os parametros obrigatórios foram passados
	*/
	if(!isset($argumentos['host1'])){echo "Parametro obrigatorio  --host1  nao informado\n"; exit;}
	if(!isset($argumentos['usuario1'])){echo "Parametro obrigatorio  --usuario1  nao informado\n"; exit;}
	if(!isset($argumentos['senha1'])){echo "Parametro obrigatorio  --senha1  nao informado\n"; exit;}
	if(!isset($argumentos['host2'])){echo "Parametro obrigatorio  --host2  nao informado\n"; exit;}
	if(!isset($argumentos['usuario2'])){echo "Parametro obrigatorio  --usuario2  nao informado\n"; exit;}
	if(!isset($argumentos['senha2'])){echo "Parametro obrigatorio  --senha2  nao informado\n"; exit;}
	
	/**
	*
	*
	*       Instancia a classe PhpMailSync para 
	*/
	$origem = new PhpMailSync($argumentos['host1'],$argumentos['usuario1'],$argumentos['senha1'],$tipo1,$ssl1);
	$destino= new PhpMailSync($argumentos['host2'],$argumentos['usuario2'],$argumentos['senha2'],$tipo2,$ssl2);
	


}else{
  echo  'Este script deve ser executado pela linha de comando'."\n";
        exit();
}


$inicio=date('d/m/Y -- H:i:s');
if(!$origem->testarConexao()){
	echo( "Nao foi possivel conectar o servidor de origem , o servidor nao respondeu atraves do endereco: $origem->servidor  na porta  $origem->porta \n");
	exit;
}
if(!$origem->conectar()){
	echo( "Falha de autenticacao no servidor de origem: $origem->servidor com a conta $origem->usuario \n");
	exit;
}
if(!$destino->testarConexao()){
	echo( "Nao foi possivel conectar o servidor de destino, o servidor nao respondeu atraves do endereco: $destino->servidor na porta $destino->porta \n");
	exit;
}
if(!$destino->conectar()){
	echo( "Falha de autenticacao no servidor de destino: $destino->servidor com a conta $destino->usuario \n");
	exit;
}
 
echo( 
     "\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++'."\n".
     'Iniciando migracao em '.$inicio."\n".
     '+++++++++++++++++++++++++++++++++++++++++++++++++'."\n".
     "\n"."\n".
     '--- Informacoes da conta - ORIGEM --- '."\n".
      $origem->verificarInfoQuota().
     'Prefixo: '.$origem->verificarPrefixo()."\n".
     'Separador: '.$origem->verificarTipoSeparador()."\n\n"
	 );
echo $origem->calcularEspacos();
echo ("\n".'--- Informacoes da conta - DESTINO --- '."\n".
     $destino->verificarInfoQuota().
     'Prefixo: '.$destino->verificarPrefixo()."\n".
     'Separador: '.$destino->verificarTipoSeparador()."\n\n"
     );
echo $destino->calcularEspacos();
echo("\n".'+++++++++++++++++++++++++++++++++++++++++++++++++'."\n");
	


if(!isset($argumentos['ignorarespaco'])){
	if($origem->quotaEmUso > $destino->quotaDisponivel){
	     $espaco= $origem->quotaEmUso - $destino->quotaDisponivel;
		echo( 
		'Nao sera possivel iniciar a migracao dos emails'."\n".
		'Sera necessario adicionar mais '.$destino->ajustarMedida($espaco).' de espaco a conta '.$destino->usuario."\n".
		'+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n\n");
		exit;
	}
}

$destino->listarMailBox();
$destino->verificarTipoSeparador();
foreach($origem->listarMailBox() as $mailbox){
	$pastasOrigem=$origem->listarPastas($mailbox);
	echo(  $destino->criarMailboxInexistentes($origem,$pastasOrigem));
	$destino->limparImapCache($origem);
   	
	echo( "Verificando conteudo na pasta $pastasOrigem \n");
        
	if($mensagensNaoExistentes=$destino->verificarMensagensDuplicadas($origem,$pastasOrigem)){
		$msgsNaoExistentes=count($mensagensNaoExistentes);		
		echo(   '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n".
	                     "Mensagens nao existentes da pasta $pastasOrigem: $msgsNaoExistentes \n".
		              '+++++++++++++++++++++++++++++++++++++++++++++++++ '."\n");
		foreach ($mensagensNaoExistentes as $key=>$uid){

			if($origem->keepAlive()){
				echo(  "Conexao perdida com o host de origem\n");	
				exit;
			}
			if($destino->keepAlive()){
				echo( "Conexao perdida com o host de destino\n");
				exit;
			}
			echo( '('.(--$msgsNaoExistentes).")  ".$destino->migrarMensagensImap($origem,$pastasOrigem,$uid));
			$destino->limparImapCache($origem);
						
		}
	}
}

echo( 
 "++++++++++++++++++++++++++++++++++++++++++++++ \n".
"\n".
"ESTATISTICAS\n".
"Listagem de pastas e quantidade de mensagens \n".
"+++++ Pastas na origem ++++++ \n");

$origem->listarInfoPorPasta();

echo ("\n
+++++ Pastas no destino ++++++ \n");
$destino->listarInfoPorPasta();
echo("\n--------------------------------------------\n".
$destino->gerarEstatisticas()."\n".
"Migracao iniciada em:  $inicio \n".
"Migracao concluida em: ".date('d/m/Y -- H:i:s')."\n".
"\n".
"++++++++++++++++++++++++++++++++++++++++++++++ \n");

    
}else{
        echo(  "A extensão Imap para PHP não está ativa\nPor favor contatar o administrador");
        exit;
}
?>  
