<?php
class Imap{
	private $servidor;
	private $porta;
	private $ssl;
	private $usuario;
	private $senha;
	private $mbox;
	private $quota;
	private $quotaEmUso;
	private $quotaDisponivel;
	private $quotaTotal;
	private $pastas;
	private $separador;
	private $prefixo;
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
                    $this->stream =@imap_open($this->mbox.$this->pastas,$this->usuario, $this->senha,1);
					return $this->stream;
                    
               }else{
                    $this->mbox='{'."$this->servidor:$this->porta/imap/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox.$this->pastas,$this->usuario, $this->senha,1);
					return $this->stream;
               }
          } //fecha if IMAP
         if($this->tipo=="pop3"){
               if($this->ssl==1){
			   
                    $this->mbox='{'."$this->servidor:$this->porta/pop3/ssl/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox.$this->pastas,$this->usuario,$this->senha,1);
				    return $this->stream;
               }else{
                    $this->mbox='{'."$this->servidor:$this->porta/pop3/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox.$this->pastas,$this->usuario,$this->senha,1);
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
	
	 public function listarMailBox(){
	    $this->pastas= imap_list($this->stream,$this->mbox, "*");
		return $this->pastas;
		
	} 
	public function listarPastas($mailbox){
		$pos = strpos($mailbox,"}");
		$pastas = substr($mailbox,$pos+1);
		return $pastas;
	}


	public function verificarTipoSeparador(){
			$this->separador= imap_getmailboxes($this->stream,$this->mbox, "*");
			$this->separador= $this->separador[0]->delimiter;
			return $this->separador;
	 }
	 
	// Funçao para ser utilizada no host de destino
	public function verificarPadraoMailbox($origem,$pastas){
			/*Esta função necessita das funções abaixo
				$this->listarMailBox();
				$this->verificarTipoSeparador();
			
			*/
		$delimitador= $origem->verificarTipoSeparador();
		$pastas_novoarray=explode($delimitador,$pastas);
	        $pastas=implode($this->separador,$pastas_novoarray);
          
	     if(@preg_grep("/INBOX".$this->separador."/",$this->pastas)){
					$pastas="INBOX".$this->separador.$pastas; 
					$pastas= str_replace('INBOX'.$this->separador.'INBOX','INBOX',$pastas);
					return $pastas;
	     }else if(@preg_grep("/Inbox".$this->separador."/",$this->pastas)) {
					$pastas="Inbox".$this->separador.$pastas; 
					$pastas=str_replace('Inbox'.$this->separador.'Inbox','Inbox',$pastas);
					return $pastas;
		 }else{
				if(preg_match("/INBOX\\".$this->separador."/",$pastas)){
						$pastas=@preg_filter("/INBOX\\".$this->separador."/","",$pastas);
						return $pastas;
				}
				if(preg_match("/Inbox\\".$this->separador."/",$pastas)){
						$pastas=@preg_filter("/Inbox\\".$this->separador."/","",$pastas);
						return $pastas;
					
				}
		}
	}
	public function criarMailboxInexistentes($pastas){
	      if(! array_search($this->mbox.$pastas,$this->pastas)){
			$pastas =imap_utf7_decode($pastas);
			if(@imap_createmailbox($this->stream, imap_utf7_encode($this->mbox.$pastas))){
					return " Pasta: $pastas  criada com sucesso !"."\n";
				if(!@imap_subscribe($this->stream,$this->mbox.imap_utf7_encode($pastas))){
					return "Falha na inscrição da pasta: $pastas"."\n";
				}
			}else{
				$erros = imap_errors();
				return "Falha na criacao da pasta: $pastas --> ".$erros[0]."\n";
			}
	}
	
	
	public function migrarMensagens($origem,$pastas){
	$origemMailbox= @imap_open($origem->mailbox.$pastas,$origem->usuario,$origem->senha);
	//$destinoMailbox= @imap_open($this->mailbox.$pastas,$this->usuario,$this->senha);
	var_dump($origemMailbox);
	imap_close($origemMailbox);
	//var_dump($destinoMailbox);
	}
	
	
	public function listarTotalMensagensPorMailbox($pasta){
		$mailbox= imap_open($pasta,$this->usuario,$this->senha,1);
		$cabecalho = imap_headers($mailbox);
		$total = count($cabecalho);
		imap_close($mailbox);
		return "Total de mensagens: $total mensagens";
		
	}
	public function receberInfoQuotaTotal(){
		$this->quota = imap_get_quotaroot($this->stream, "INBOX");
	}
	
	public function verificarQuotaDeUso(){
		$quotaDeUsoKB = ($this->quota["usage"]);
		$quotaDeUsoMB = ($this->quota["usage"]*1024)/1048576;
		$quotaDeUsoGB = ($this->quota["usage"]*1024)/1073741824; 
         if($quotaDeUsoMB >= 1024){
			$this->quotaEmUso = number_format($quotaDeUsoGB,2)." GB"; 
		     return $this->quotaEmUso;
		}else{
		      if($quotaDeUsoKB >= 1024){
			     $this->quotaEmUso = number_format($quotaDeUsoMB,2)." MB";
				 return $this->quotaEmUso;
				 
		      }else{
				$this->quotaEmUso = $quotaDeUsoKB." KB";
			     return $this->quotaEmUso;
		      }
		}    
	}
	public function verificarQuotaDisponivel(){
		//Usar apenas para a Raiz (INBOX)
		$quotaDisponivelKB =($this->quota["limit"] - $this->quota["usage"]);
		$quotaDisponivelMB =($this->quota["limit"] - $this->quota["usage"])*1024/1048576;
		$quotaDisponivelGB =($this->quota["limit"] - $this->quota["usage"])*1024/1073741824;
		  
		if($quotaDisponivelMB >= 1024 ){
			$this->quotaDisponivel=number_format($quotaDisponivelGB,2)." GB";
            return $this->quotaDisponivel;
		}else{
		     if($quotaDisponivelKB >= 1024){
				$this->quotaDisponivel=number_format($quotaDisponivelMB,2)." MB";
		         return $this->quotaDisponivel; 
		      }else{
				$this->quotaDisponivel=$quotaDisponivelKB." KB";
		        return $this->quotaDisponivel;
		      }
		}
	}
	public function verificarPorgentagemDeUso(){
		//Usar apenas para a Raiz (INBOX)
		$quotaDeUsoMB = $this->quota["usage"]*1024/1048576;
		$limite=$this->quota["limit"]*1024/1048576;
		$quotaDeUso1=$quotaDeUsoMB*100;
		$quotaDeUso2= round ($quotaDeUso1/$limite,0);
		return  $quotaDeUso2." %";
	}
	
	public function verificarQuotaTotal(){
		//Usar apenas para a Raiz (INBOX)
		$quotaTotalKB =($this->quota["limit"]);
		$quotaTotalMB =($this->quota["limit"])*1024/1048576;
		$quotaTotalGB =($this->quota["limit"])*1024/1073741824;
		  
		if($quotaTotalMB >= 1024 ){
			   $this->quotaTotal=number_format($quotaTotalGB,2)." GB";
			   return $this->quotaTotal;
		}else{
			 if($quotaTotalKB >= 1024){
				$this->quotaTotal= number_format($quotaTotalMB,2)." MB";
				return $this->quotaTotal;
			  }else{
				$this->quotaTotal= $quotaTotalKB." KB";
				return $this->quotaTotal;
			  }
		}
	}
    public function verificarInfoQuota(){
		$this->receberInfoQuotaTotal();
		 return ('USO: '.$this->verificarQuotaDeUso().
		 '\n PORCENTAGEM DE USO: '.$this->verificarPorgentagemDeUso().
		 '\n DISPONIVEL: '.$this->verificarQuotaDisponivel().
		 '\n TOTAL: '.$this->verificarQuotaTotal().'<br />'
		 );
    }
	public function verificarQuotaPorPasta($mailbox){
         $this->quota=imap_get_quotaroot($this->stream,$mailbox);  
		 
    }
}
