<?php
session_start();
include('../classes/Validacao.class.php');
include('../persistencia/phpmailsyncDao.php');


	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if((trim($_POST['host1'])!="") && (trim($_POST['host2'])!="") &&  isset($_POST['contas']) && isset($_POST['tipo1']) && isset($_POST['tipo2'])){		
		/*
		if(!isset($_SESSION["idmigracao"])){
			$idmigracao= uniqid();
			$_SESSION["idmigracao"] = $idmigracao;
		}else{
			$idmigracao = $_SESSION["idmigracao"];
		}
		*/
		$idmigracao= uniqid();
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/* Coloca dados submetidos na Sessão*/
		$host1=trim($_POST['host1']);
		$host2=trim($_POST['host2']);
		$tipo1=$_POST['tipo1'];
		$tipo2=$_POST['tipo2'];
		$ssl1=(isset($_POST['ssl1']))? '1' : '0';
		$ssl2=(isset($_POST['ssl2']))? '1' : '0';
		$contas=$_POST['contas'];
		$totaldecontas=0;
		$erros = array();
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		/* Coloca dados submetidos na Sessão*/
		$_SESSION["host1"] = $host1;
		$_SESSION["tipo1"] = $tipo1;
		$_SESSION["ssl1"]  = $ssl1;
		$_SESSION["contas"] = $contas;
		$_SESSION["host2"] = $host2;
		$_SESSION["tipo2"] = $tipo2;
		$_SESSION["ssl2"]  = $ssl2;
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		
				
		if(!Validacao::validarMaximoDeContas($contas)){
			$erros[] =  "Maximo 10 contas por vez \n";

		}
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(isset($_POST['contas'])&& $_POST['contas'] !="" ){
		$arraydeContas = explode("\r\n",$_POST['contas']);
		foreach($arraydeContas as $key => $conta){
			$conta= explode(";",$conta);
			if(isset($conta[0]) && isset($conta[1]) && isset($conta[2]) && isset($conta[3])){	
				$linha= $key+1;
				if(Validacao::validarMigracaoEntreContas($host1,$host2,$conta[0],$conta[2])){
					$erros[] = "Nao e possivel migrar para contas iguais no mesmo host\nOcorrencia: linha $linha \nConta: $conta[0] \n \n";
				}
				$dados[] = $idmigracao.";".$host1.";".$conta[0].";".$conta[1].";".$ssl1.";".$tipo1.";".$host2.";".$conta[2].";".$conta[3].";".$ssl2.";".$tipo2.";".$conta[2];	
			}else{
			        $erros[] = "Dados Incompletos";
			}
		}
		//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if(count($erros)==0){
			$_SESSION["dados"] = Validacao::removerArraysDuplicados($dados);
			
			
			if($_POST['tipo'] == 'agendamento' ){
			         $data=$_POST['data'];
			         $hora = $_POST['hora'];
			         $status='3';
			         $phpmailsyncDao =  new phpmailsyncDao();
			         $phpmailsyncDao->novaMigracao($idmigracao,2,"Agendamento");
			         $phpmailsyncDao =  new phpmailsyncDao();
			         $phpmailsyncDao->novoAgendamento($host1,$ssl1,$tipo1,$host2,$ssl2,$tipo2,$contas,$data,$hora,$status,$idmigracao);
			        echo "Migração agendada para o dia $data as $hora horas";
			}else{
			        $phpmailsyncDao =  new phpmailsyncDao();
			        if($phpmailsyncDao->novaMigracao($idmigracao,1,"Manual")){				
				        header("Location: ../cgi/migrafork2.cgi");
			        }
			}
		}else{
				var_dump($erros);
		}
	  }
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	}//Fecha Primeiro IF
					
?>
