<?php
class Validacao{
	/*
		public static function removerArraysDuplicados( Array $valor)
	*/
	public static function removerArraysDuplicados($valor){
		return array_unique($valor);
	}

	public static function validarMaximoDeContas($valor){
		if(count($valor)<11){ 
			return true;
		}else{
			return false;
		}
	}
	/*
		public static function validarMigracaoEntreContas( HostdeOrigem, HostdeDestino, ContaDeorigem, ContaDeDestino)
		Esta função faz com que não seja permitido migrar da conta para ela mesma, isso quando o host de origem e destino forem iguais
	*/
	public static function validarMigracaoEntreContas($valor1,$valor2,$valor3,$valor4){
		if($valor1== $valor2  &&  $valor3 == $valor4 ){
			return true;
		}else{
			return false;
		}
	}
	/*
	public static function validarAlgumacoisa($valor){
		if( estiverOK) {
			return true;
		}else{
			return false;
		}
	}
	
	public static function validarAlgumacoisa($valor){
		if( estiverOK) ){
			return true;
		}else{
			return false;
		}
	}
	*/
}
?>
