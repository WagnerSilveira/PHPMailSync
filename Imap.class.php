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
		$this->pastas = substr($mailbox,$pos+1);
		return $this->pastas;
	}


	public function verificarTipoSeparador(){
			$this->separador= imap_getmailboxes($this->stream,$this->mbox, "*");
			$this->separador= $this->separador[0]->delimiter;
			return $this->separador;
	 }
	 
	// Funçao para ser utilizada no host de destino
	public function alterarPadraoMailbox($origem,$pastas){
	     $delimitador= $origem->verificarTipoSeparador();
	     $this->listarMailBox();
	     $this->verificarTipoSeparador();
	     $pastas_novoarray=explode($delimitador,$pastas);
          $pastas=implode($this->separador,$pastas_novoarray);
          
	     if(@preg_grep("/INBOX".$this->separador."/",$this->pastas)){
					$pastas="INBOX".$this->separador.$pastas; 
					$pastas= str_replace('INBOX'.$this->separador.'INBOX','INBOX',$pastas);
					 if(! array_search($this->mbox.$pastas,$this->pastas)){
							$pastas =imap_utf7_decode($pastas);
							if(@imap_createmailbox($this->stream, imap_utf7_encode($this->mbox.$pastas))){
								imap_subscribe($this->stream,$this->mbox.imap_utf7_encode($pastas));
								return " Pasta: $pastas  criada com sucesso !"."\n";
							}else{
								$erros = imap_errors();
								return "Falha na criacao da pasta: $pastas --> ".$erros[0]."\n";
							}
						
                      }
	     }else if(@preg_grep("/Inbox".$this->separador."/",$this->pastas)) {
				$pastas="Inbox".$this->separador.$pastas; 
				$pastas=str_replace('Inbox'.$this->separador.'Inbox','Inbox',$pastas);
				
				if(! array_search($this->mbox.$pastas,$this->pastas)){
						$pastas =imap_utf7_decode($pastas);
						if(imap_createmailbox($this->stream, imap_utf7_encode($this->mbox.$pastas))){
							imap_subscribe($this->stream,$this->mbox.imap_utf7_encode($pastas));
							return " Pasta: $pastas criada com sucesso !"."\n";
						}else{
							$erros = imap_errors();
							return "Falha na criacao da pasta: $pastas --> ".$erros[0]."\n";
						}
				}
		 }else{
				if(preg_match("/INBOX\\".$this->separador."/",$pastas) == true){
					$pastas=@preg_filter("/INBOX\\".$this->separador."/","",$pastas);
				}
				if(preg_match("/Inbox\\".$this->separador."/",$pastas) == true){
					$pastas=@preg_filter("/Inbox\\".$this->separador."/","",$pastas);
				}
				 if(! array_search($this->mbox.$pastas,$this->pastas)){
							$pastas =imap_utf7_decode($pastas);
						 	if(imap_createmailbox($this->stream,imap_utf7_encode($this->mbox.$pastas))){
								imap_subscribe($this->stream,$this->mbox.imap_utf7_encode($pastas));
								return " Pasta: $pastas criada com sucesso !"."\n";
							}else{
								$erros = imap_errors();
								return "Falha na criacao da pasta: $pastas --> ".$erros[0]."\n";
							} 
				} 
		}
		
	}
	public function criarMailboxInexistentes($pastas){
	
	      if(! array_search($this->mbox.$pastas,$this->pastas)){
			$pastas =imap_utf7_decode($pastas);
			if(@imap_createmailbox($this->stream, imap_utf7_encode($this->mbox.$pastas))){
				imap_subscribe($this->stream,$this->mbox.imap_utf7_encode($pastas));
				return " Pasta: $pastas  criada com sucesso !"."\n";
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
