<?php

class Imap{
	private $servidor;
	private $porta;
	private $ssl;
	private $usuario;
	private $senha;
	private $mbox;
	private $quota;
	private $pastas;
    private $tipo;
	private $stream;
	
	public function __construct($servidor,$usuario,$senha,$tipo,$ssl){
	      $this->receberInformacoesDeConexao($servidor,$usuario,$senha,$tipo,$ssl);

	}
	public function __get($atributo){
		return $this->$atributo;
	}
		
	public function __set($atributo,$valor){
		$this->$atributo = $valor;
	}
	
	public function conectar(){
       if($this->tipo=="imap"){
	          if($this->ssl==1){
                    $this->mbox='{'."$this->servidor:$this->porta/imap/ssl/novalidate-cert".'}';
                    $this->stream =@imap_open($this->mbox,$this->usuario, $this->senha,1);
					return $this->stream;
                    
               }else{
                    $this->mbox='{'."$this->servidor:$this->porta/imap/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox,$this->usuario, $this->senha,1);
					return $this->stream;
               }
          } //fecha if IMAP
         if($this->tipo=="pop3"){
               if($this->ssl==1){
			   
                    $this->mbox='{'."$this->servidor:$this->porta/pop3/ssl/novalidate-cert".'}';
                    $this->stream= @imap_open($this->mbox,$this->usuario,$this->senha,1);
				    return $this->stream;
               }else{
                    $this->mbox='{'."$this->servidor:$this->porta/pop3/novalidate-cert".'}';
                    $this->stream= @imap_open($this->mbox,$this->usuario,$this->senha,1);
				    return $this->stream;
               }
         }//fecha if POP3 
	
	}
	public function receberInformacoesDeConexao($servidor,$usuario,$senha,$tipo,$ssl){
	      $this->servidor=$servidor;
	      $this->usuario=$usuario;
	      $this->senha=$senha;
	      $this->tipo=$tipo;
	      $this->ssl=$ssl;
	      if($this->tipo=="imap"){
				$this->porta=($this->ssl==1)?'993':'143';
	      }
	       if($this->tipo=="pop3"){
				$this->porta=($this->ssl==1)?'995':'110';
	      }
	           
     }
	
	public function testarConexao(){
	   if($this->ssl==1){
			   $socket=@fsockopen("ssl://".$this->servidor,$this->porta);
               if($socket){
					fclose($socket);
                     return true;
               }else{
                    return false;
               }
	   }else{
				 $socket=@fsockopen($this->servidor,$this->porta);
				if($socket){
					fclose($socket);
                   return true;
               }else{
                   return false;
               }
        }
	}
	public function verificarQuotaDeUso(){
		$quotaDeUsoKB = ($this->quota["usage"]);
		$quotaDeUsoMB = ($this->quota["usage"]*1024)/1048576;
		$quotaDeUsoGB = ($this->quota["usage"]*1024)/1073741824; 
          if($quotaDeUsoMB >= 1024){
		     return number_format($quotaDeUsoGB,2)." GB"; 
		}else{
		      if($quotaDeUsoKB >= 1024){
			     $quotaDeUsoMB = number_format($quotaDeUsoMB,2);
			     return  $quotaDeUsoMB." MB";
		      }else{
			     return  $quotaDeUsoKB." KB";
		      }
		}    
	}
	
	public function verificarPorgentagemDeUso(){
		$quotaDeUsoMB = $this->quota["usage"]*1024/1048576;
		$limite=$this->quota["limit"]*1024/1048576;
		$quotaDeUso1=$quotaDeUsoMB*100;
		$quotaDeUso2= round ($quotaDeUso1/$limite,0);
		return  $quotaDeUso2;
		
          
	}
	
	public function verificarQuotaDisponivel(){
		$quotaDisponivelKB =($this->quota["limit"] - $this->quota["usage"]);
		$quotaDisponivelMB =($this->quota["limit"] - $this->quota["usage"])*1024/1048576;
		$quotaDisponivelGB =($this->quota["limit"] - $this->quota["usage"])*1024/1073741824;
		  
		if($quotaDisponivelMB >= 1024 ){
               return number_format($quotaDisponivelGB,2)." GB";
		}else{
		     if($quotaDisponivelKB >= 1024){
		          return number_format($quotaDisponivelMB,2)." MB";
		      }else{
		          return $quotaDisponivelKB." KB";
		      }
		}
	}
		public function verificarQuotaTotal(){
		$quotaTotalKB =($this->quota["limit"]);
		$quotaTotalMB =($this->quota["limit"])*1024/1048576;
		$quotaTotalGB =($this->quota["limit"])*1024/1073741824;
		  
		if($quotaTotalMB >= 1024 ){
               return number_format($quotaTotalGB,2)." GB";
		}else{
		     if($quotaTotalKB >= 1024){
		          return number_format($quotaTotalMB,2)." MB";
		      }else{
		          return $quotaTotalKB." KB";
		      }
		}
	}
	
	public function validarMailBox(){
	      $this->pastas=imap_list($this->stream,$this->mbox,"*");
		 foreach($this->pastas as $pasta){
			$pos = strpos($pasta,"}");
			$this->pastas = substr($pasta,$pos+1);
			$this->pastas =imap_utf7_decode($this->pastas);
			$this->pastas= str_replace("/",".",$this->pastas);
			echo $this->pastas."\n";
		}
	}
	
	
    public function listarMailBoxes(){
        $this->pastas=imap_list($this->stream,$this->mbox,"*");
        foreach($this->pastas as $pasta){
			$pos = strpos($pasta,"}");
			$this->pastas = substr($pasta,$pos+1);
			$this->pastas =imap_utf7_decode($this->pastas);
			echo $this->pastas."\n";
        } 
    }
    public function verificarQuota(){
         $this->quota = imap_get_quotaroot($this->stream, "INBOX");
		 return ('<br/>USO: '.$this->verificarQuotaDeUso().
		 '<br/>PORCENTAGEM DE USO: '.$this->verificarPorgentagemDeUso()." %".
		 '<br/>DISPONIVEL: '.$this->verificarQuotaDisponivel().
		 '<br/>TOTAL: '.$this->verificarQuotaTotal().'<br />'
		 );
    }
	public function verificarQuotaMailBoxes($mailbox){
         $this->quota = imap_get_quotaroot($this->stream,$mailbox);    
    }
}
$origem =  new Imap("imap.gmail.com","wagnerhsilveira@gmail.com","6096412132","imap",1);
$destino= new Imap("imap.wagnersilveira.kinghost.net","teste@wagnersilveira.kinghost.net","40302010aa","imap",0);

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

echo "<pre>";
$origem->listarMailBoxes();
echo "</pre>";

echo "<pre>";
$origem->validarMailBox();
echo "</pre>";
$origem_quota = $origem->verificarQuota();


echo $origem_quota."<br /> ";



echo "<pre>";
$destino_inbox= $destino->listarMailBoxes();
echo "</pre>";
$destino_quota = $destino->verificarQuota();// Não funciona com POP3
echo $destino_quota."<br /> "; 


/* if($origem_quota > $destino_quota){
	echo "<br/>O espaco no destino deve ser igual ou maior a  $origem_quota MB ";
} */
?>
