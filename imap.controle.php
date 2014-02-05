<?php
include('Imap.class.php');
error_reporting();

$origem= new Imap(string host, string usuario ,string senha,string tipo, bool ssl);
$destino=  new Imap(string host, string usuario ,string senha,string tipo, bool ssl);


if(!$origem->testarConexao()){
	echo "Nao foi possivel conectar o servidor de origem, o servidor nao respondeu atraves do endereco:<strong> $origem->servidor </strong> na porta <strong> $origem->porta </strong>";
	exit;
}
if(!$origem->conectar()){
	echo "Falha de autenticacao no servidor de origem: $origem->servidor com a conta $origem->usuario <br />";

	exit;
}
if(!$destino->testarConexao()){
	echo "Nao foi possivel conectar o servidor de destino, o servidor nao respondeu atraves do endereco:<strong>  $destino->servidor </strong> na porta <strong>  $destino->porta </strong>";

	exit;
}
if(!$destino->conectar()){
	echo "Falha de autenticacao no servidor de destino: $destino->servidor com a conta $destino->usuario <br />";
	exit;
}

?>  
