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
	private $contatorMsgMigradas;
	private $contatorMsgExistentes;
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
	
	//	Funcao Conectar Original
	
	public function conectar(){
       if($this->tipo=="imap"){
	          if($this->ssl==1){
                    $this->mbox='{'."$this->servidor:$this->porta/imap/ssl/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox,$this->usuario, $this->senha,NULL,0);
					return $this->stream;
                    
               }else{
                    $this->mbox='{'."$this->servidor:$this->porta/imap/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox,$this->usuario, $this->senha,NULL,0);
					
					return $this->stream;
               }
          } //fecha if IMAP
         if($this->tipo=="pop3"){
               if($this->ssl==1){
			   
                    $this->mbox='{'."$this->servidor:$this->porta/pop3/ssl/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox,$this->usuario,$this->senha,1);
				    return $this->stream;
               }else{
                    $this->mbox='{'."$this->servidor:$this->porta/pop3/novalidate-cert".'}';
                    $this->stream=@imap_open($this->mbox,$this->usuario,$this->senha,1);
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
			   $socket=@fsockopen("ssl://".$this->servidor,$this->porta,$errno,$errstr,1);
               if($socket){
					fclose($socket);
                     return true;
               }else{
                    return false;
               }
	   }else{
				 $socket=@fsockopen($this->servidor,$this->porta,$errno,$errstr,1);
				if($socket){
					fclose($socket);
                   return true;
               }else{
                   return false;
               }
        }
	}
	//Em Desenvolvimento
	public function reconectar($origem){
	
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
		$this->separador= imap_getmailboxes($this->stream,$this->mbox,"*");
		$this->separador= $this->separador[0]->delimiter;
		return $this->separador;
	 }
	 
	// Funcao para ser utilizada no host de destino
	public function verificarPadraoMailbox($origem,$pastas){
		/*Esta funcao necessita das funcoes abaixo
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
				return $pastas;
		}
	}
	public function criarMailboxInexistentes($origem,$pastas){
		$pastas=$this->verificarPadraoMailbox($origem,$pastas);
		if(!array_search($this->mbox.$pastas,$this->pastas)){
			$pastas =imap_utf7_decode($pastas);
			if(imap_createmailbox($this->stream, imap_utf7_encode($this->mbox.$pastas))){
				if(!@imap_subscribe($this->stream,$this->mbox.imap_utf7_encode($pastas))){
					return "Falha na inscricao da pasta: $pastas"."\n";
				}
				return "Pasta: $pastas  criada com sucesso !"."\n";
			}else{
				$erros = imap_errors();
				return "Falha na criacao da pasta: $pastas --> ".$erros[0]."\n";
			}
		}
		return "Pasta já existe: $pastas  \n";
	}
	
	public function limparImapCache($origem){
		imap_gc($origem->stream, IMAP_GC_ELT | IMAP_GC_ENV | IMAP_GC_TEXTS);
		imap_gc($this->stream, IMAP_GC_ELT | IMAP_GC_ENV | IMAP_GC_TEXTS);
	}
	
	public function verificarMensagensDuplicadas($origem,$pastas){
	     //Função verifica mensagem pela Message-ID
		imap_reopen($origem->stream,$origem->mbox.$pastas);
		$pastasDestino=$this->verificarPadraoMailbox($origem,$pastas);
		imap_reopen($this->stream,$this->mbox.$pastasDestino);
		
		$totalOrigem = imap_num_msg($origem->stream);
		$totalDestino = imap_num_msg($this->stream);
		
		$mensagensOrigem=null;
		$mensagensDestino=null;
		$naoexistentes=null;
		
		if($totalOrigem > 0){
		     //$MessageIdOrigem= @imap_fetch_overview($origem->stream,"1:{$totalOrigem->Nmsgs}");
			 $MessageIdOrigem= @imap_fetch_overview($origem->stream,"1:*");
		}
		
		if($totalDestino > 0){
		           //$MessageIdDestino= @imap_fetch_overview($this->stream,"1:{$totalDestino->Nmsgs}");
		           $MessageIdDestino= @imap_fetch_overview($this->stream,"1:*");
                    if(isset($MessageIdDestino)){
                         foreach($MessageIdDestino as $key => $mensagem){
                              if(isset($MessageIdDestino[$key]->message_id)){
                                   $mensagensDestino[$key] = $MessageIdDestino[$key]->message_id;
                              }
                         }
                    }
                    if($mensagensDestino){
                         if(isset($MessageIdOrigem)){
                              foreach($MessageIdOrigem as $key => $mensagem){
                                if(isset($MessageIdOrigem[$key]->message_id)){
                                   if (!in_array($MessageIdOrigem[$key]->message_id,$mensagensDestino)){
                                         $naoexistentes[] = $MessageIdOrigem[$key]->uid;
                                   }
                                }
                              }
                         }
		     }
		}else{
		     if(isset($MessageIdOrigem)){
			     foreach($MessageIdOrigem as $key => $mensagem){
				     $naoexistentes[] = $MessageIdOrigem[$key]->uid;
			     }
			}
		}
		return $naoexistentes;
	}	
	
	public function listarMensagensPorPastas($origem,$pastas){
		imap_reopen($this->stream,$this->mbox.$pastas);
		$cabecalhos = imap_headers($this->stream);
		foreach($cabecalhos as $mensagens){
			echo $mensagens."\n";
		}
	}

	public function migrarMensagensImap($origem,$pastasOrigem,$uid){
	
		$pastasDestino=$this->verificarPadraoMailbox($origem,$pastasOrigem);
		$msgNum= imap_msgno($origem->stream,$uid);				
		$header = imap_headerinfo($origem->stream,$msgNum);
		$msgVisualisada = $header->Unseen;        
		                     
		$cabecalho = imap_fetchheader($origem->stream,$uid,FT_UID);
		$corpo = imap_body($origem->stream,$uid,FT_UID | FT_PEEK);
		
		usleep(150000);/*  ==> Essa funcao serve para diminuir o load da máquina
		
		      Sem o Usleep, o uso da CPU chega a 25%, com ele no máximo ate 3%
	            150000 micro_segundos igual a 0,15 segundos de espera 
	     */
		if (imap_append($this->stream,$this->mbox.$pastasDestino,$cabecalho."\r\n".$corpo)) {
		//Verifica flags da mensagem
				if ($msgVisualisada != "U") {
					  if (! imap_setflag_full($this->stream,$msgNum,' \\SEEN')) {
						   echo "Nao pode setar a Flag  \\SEEN ";
					  }
				}
				return " Migrando mensagem UID= $uid  - Quantidade de memoria utilizada = ".$this->ajustarMedidaBytes(memory_get_usage(True))."\n";

		}else {
		       $erros = imap_errors();
			  return "Mensagem UID -$uid nao pode ser migrada --> ".$erros[0]."\n";
		}
	
	}
	public function listarTotalMensagensPorMailbox($pastas){
		imap_reopen($this->stream,$this->mbox.$pastas);
		$totalMsgs = imap_num_msg($this->stream);
		return $totalMsgs;
		
	}
	public function ajustarMedida($medidaEmKB){
	    $kiloBytes =$medidaEmKB;
		$megaBytes =$medidaEmKB*1024/1048576;
		$gigaBytes =$medidaEmKB*1024/1073741824;
		
		if($megaBytes >= 1024){
				$gigaBytes = number_format($gigaBytes,2)." GB"; 
				return $gigaBytes;
		}else{
			if($kiloBytes >= 1024){
				$megaBytes = number_format($megaBytes,2)." MB";
				return $megaBytes;
			}else{
				return $kiloBytes." KB";
			}
		}    
	}
	public function ajustarMedidaBytes($medidaEmBytes){
		$medidaEmKB= $medidaEmBytes/1024;
		return $this->ajustarMedida($medidaEmKB);
	}
	
	public function receberInfoQuotaTotal(){
		$this->quota = imap_get_quotaroot($this->stream, "INBOX");
	}
	
	public function verificarQuotaTotal(){
		//Usar apenas para a Raiz (INBOX)
		$this->quotaTotal= $this->ajustarMedida($this->quota["limit"]);
		return $this->quotaTotal;
	}
	
	public function verificarQuotaDeUso(){
		$this->quotaEmUso= $this->ajustarMedida($this->quota["usage"]);
		return $this->quotaEmUso; 
	}
	
	public function verificarQuotaDisponivel(){
		//Usar apenas para a Raiz (INBOX)
		$this->quotaDisponivel= $this->ajustarMedida($this->quota["limit"] - $this->quota["usage"]);
		return $this->quotaDisponivel;
	}
	
	public function verificarPorgentagemDeUso(){
			//Usar apenas para a Raiz (INBOX)
		$porcentagemDeUso= ($this->quota["usage"]*100)/$this->quota["limit"];
		$porcentagemDeUso= round ($porcentagemDeUso,0);
		return $porcentagemDeUso." %";
	}
	
    public function verificarInfoQuota(){
		$this->receberInfoQuotaTotal();
		 return ('USO: '.$this->verificarQuotaDeUso().
		 "\n".'PORCENTAGEM DE USO: '.$this->verificarPorgentagemDeUso().
		 "\n".'DISPONIVEL: '.$this->verificarQuotaDisponivel().
		 "\n".'TOTAL: '.$this->verificarQuotaTotal().'<br />'
		 );
    }
	/*Funcao Depreciada
	Leva um tempo muito longo para sua execução
	public function verificarQuotaPorPasta($pastas){
			imap_reopen($this->stream,$this->mbox.$pastas);
			$info = imap_mailboxmsginfo($this->stream);
			return "Pasta: $pastas  -- Total de mensagens: $info->Nmsgs  Tamanho:".$info->Size."\n";
    }
	*/
}

?>
