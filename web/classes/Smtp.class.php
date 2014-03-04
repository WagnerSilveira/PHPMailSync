<?php
class Smtp{
	private $conn;
	private $usuario;
	private $senha;
	private $debug;

	public function Smtp($servidor_smtp,$usuario,$senha,$debug){
		$this->conn = fsockopen($servidor_smtp, 587, $errno, $errstr, 30);
		$this->usuario=$usuario;
		$this->senha=$senha;
		$this->debug=$debug;
		$this->adicionarDadosSMTP("HELO $servidor_smtp");
		
	}
	public function __set($atributo,$valor){
		$this->$atributo = $valor;
	}
	public function __get($atributo){
		return $this->$atributo;
	}
	
		
	public function autenticar(){
		$this->adicionarDadosSMTP("AUTH LOGIN");
		$this->adicionarDadosSMTP(base64_encode($this->usuario));
		$this->adicionarDadosSMTP(base64_encode($this->senha));
	}

	public function enviar($para, $de, $assunto, $mensagem){
		$this->autenticar();
		$this->adicionarDadosSMTP("MAIL FROM: " . $de);
		$this->adicionarDadosSMTP("RCPT TO: " . $para);
		$this->adicionarDadosSMTP("DATA");
		$this->adicionarDadosSMTP($this->cabecTO($para, $de, $assunto));
		$this->adicionarDadosSMTP("\r\n");
		$this->adicionarDadosSMTP($mensagem);
		$this->adicionarDadosSMTP(".");
		$this->close();
		if(isset($this->conn)){
		return true;
		}else{
			return false;
		}
	}

	public function adicionarDadosSMTP($valor){
		return fputs($this->conn, $valor."\r\n");
	}

	 public function cabecTO($para, $de, $assunto){
		$header = "Message-Id: <". date('d/m/Y-His').".". md5(microtime()).".". strtoupper($de) ."> \r\n";
		$header .= "From: Hostmaster  <".$de."> \r\n";
		$header .= "To: <".$para."> \r\n";
		$header .= "Subject: ".$assunto." \r\n";
		$header .= "Date: ". date('D, d M Y H:i:s O') ." \r\n"; 
		$header .= "X-MSMail-Priority: Normal \r\n";
		$header .= "Content-Type: text/html;charset=UTF-8; format=flowed";
		return $header;
	}

	public function close(){
		$this->adicionarDadosSMTP("QUIT");
		if($this->debug == true){
			while (!feof ($this->conn)) {
				fgets($this->conn) . "<br>\n";
			}
		}
		return fclose($this->conn);
	}
}
?>
