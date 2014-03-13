<?php
include('../classes/Conexao.class.php');
class phpmailsyncDao {

	private static $conexao;
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/* Contrutor*/
	public function __construct() {
		 self::$conexao = Conexao::conectarBase();
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function novaMigracao($idmigracao,$status,$tipodemigracao){ 
		  try{
			  $query ="INSERT INTO phpmailsync(idmigracao,status,tipodemigracao)VALUES(?,?,?)";
			  $stat = self::$conexao->prepare($query);
			  $stat->bindValue(1,$idmigracao);
			  $stat->bindValue(2,$status);
			  $stat->bindValue(3,$tipodemigracao);
			  $stat->execute();
			   self::$conexao = null;
			   return true;		
		     }catch(PDOException $e){
			   return false;
		     }
     	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function novoAgendamento($host1,$ssl1,$tipo1,$host2,$ssl2,$tipo2,$contas,$data,$hora,$status,$idmigracao){ 
		  try{
			  $query ="INSERT INTO  phpmailsync_agendamento(host1,ssl1,tipo1,host2,ssl2,tipo2,contas,data,hora,status,idmigracao)VALUES(?,?,?,?,?,?,?,?,?,?,?)";
			  $stat = self::$conexao->prepare($query);
			  $stat->bindValue(1,$host1);
			  $stat->bindValue(2,$ssl1);
			  $stat->bindValue(3,$tipo1);
			  $stat->bindValue(4,$host2);
			  $stat->bindValue(5,$ssl2);
			  $stat->bindValue(6,$tipo2);
			  $stat->bindValue(7,$contas);
			  $stat->bindValue(8,$data);
			  $stat->bindValue(9,$hora);
			  $stat->bindValue(10,$status);
			  $stat->bindValue(11,$idmigracao);
			  $stat->execute();
			   self::$conexao = null;
			   return true;		
		     }catch(PDOException $e){
			   return false;
		     }
     	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public static function iniciarExecucao($conta,$pid,$status,$log,$idmigracao){ 
		  try{
			
			  $query ="INSERT INTO phpmailsync_execucao(conta,pid,status,inicio,log,idmigracao)VALUES(?,?,?,NOW(),?,?)";
			  $stat = self::$conexao->prepare($query);
			  $stat->bindValue(1,$conta);
			  $stat->bindValue(2,$pid);
			  $stat->bindValue(3,$status);
			  $stat->bindValue(4,$log);
			  $stat->bindValue(5,$idmigracao);
			  $stat->execute();
			   self::$conexao = null;
			   return true;		
			
		     }catch(PDOException $e){
			   return 'Erro ao Inserir Dados de execução';
		     }
     	} 	 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     	  public function atualizarStatus($pid,$idmigracao){
		try{
			$query = "UPDATE  phpmailsync_execucao SET phpmailsync_execucao.status='0' , phpmailsync_execucao.fim=NOW() WHERE phpmailsync_execucao.pid= :pid AND phpmailsync_execucao.idmigracao= :idmigracao";
                        $stat =self::$conexao->prepare($query);
                        $stat->bindValue(':pid',$pid, PDO::PARAM_STR);
			$stat->bindValue(':idmigracao',$idmigracao, PDO::PARAM_STR);
			$stat->execute();
		}catch(PDOException $e){
			return 'Erro ao atualizar Status';
		}
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         public function verificarStatusGeral($idmigracao){
		try{
			$query = "select status from phpmailsync_execucao where phpmailsync_execucao.status='1' and phpmailsync_execucao.idmigracao= ? ";
                          $stat = self::$conexao->prepare($query);
			  $stat->bindValue(1,$idmigracao);
			  $stat->execute();
	                  $contador =$stat->rowCount();
			return $contador;
		     }catch(PDOException $e){
			   return 'Erro ao buscar status de execução';
		     }
        }
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        public function atualizarStatusGeral($idmigracao){
		try{
			$query = "UPDATE  phpmailsync  SET phpmailsync.status=0 WHERE phpmailsync.idmigracao= ? ";
                        $stat =self::$conexao->prepare($query);
		        $stat->bindValue(1,$idmigracao);
			$stat->execute();
			return  self::$conexao = null;	
		}catch(PDOException $e){
			return 'Erro ao atualizar Status Geral';
		}
	}
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

}       

             


?>

