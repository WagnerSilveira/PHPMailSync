<?php
session_start();
?>
<!DOCTYPE html>

<!--
Interessante
http://pleac.sourceforge.net/pleac_php/ 

 -->

<html>
	<head>
		<title>PHPMailSync</title>
		
		<style>
			@import url("css/geral.css");
		</style> 
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script>	
			$('document').ready(function(){
				$( "#migrar" ).click(	
					function() {
						$.ajax({
							type: "POST",
							url: "controle/validador.php",
							data: $('form[name="migracao"]').serialize(),
							
							statusCode: {
							      200: function(data) {
							      		 $("#ajaxResponse").html(data);
									   // if(confirm(data)){
   										//	 window.location.reload(); 
   	   									//}		

								}
							 }
						 })
					})
				        $( "#agendar" ).click(
				                function() {
						        $.ajax({
							        type: "POST",
							        url: "controle/validador.php",
							        data: $('form[name="migracao"]').serialize(),
							
							        statusCode: {
							              200: function(data) {
							              		 $("#ajaxResponse").html(data);
									           // if(confirm(data)){
           										//	 window.location.reload(); 
           	   									//}		

								        }
							         }
						 })
                                         })
				});
		</script>
	</head>
	<body>
		<header>
		
		</header>
		
		<section class='centro'>
			<h1 class='titulo'> PHPMailSync </h1>
			<!-- Dados para o host de origem -->
			<form name='migracao' action='' method='POST' content-type='multipart/form-data'>
				<label>Host de origem: </label>
				<input type='text' name='host1' value="<?php if(isset($_SESSION['host1'] )){ echo  $_SESSION['host1'];}?>"/> 
				<label>SSL:</label>
				<input type="checkbox" name="ssl1" <?php if(isset($_SESSION['ssl1']) && $_SESSION['ssl1'] == 1){ echo 'checked=checked' ;}?>">
				<label>Tipo:</label>
				<select name='tipo1'>
					<option value="imap">IMAP</option>
					<!--<option value="pop3">POP3</option>--> 
				</select>
				<br/>
				<!-- Dados para o host de destino --> 
				<label>Host de destino:</label>
				<input type='text' name='host2' value="<?php if(isset($_SESSION['host2'] )){ echo  $_SESSION['host2'];}?>"/> 
				<label>SSL:</label>
				<input type="checkbox" name="ssl2" <?php if(isset($_SESSION['ssl2']) && $_SESSION['ssl2'] == 1){ echo 'checked=checked' ;}?>>
				<label>Tipo:</label>
				<select name='tipo2'>
					<option value="imap">IMAP</option>
					<!--<option value="pop3">POP3</option>--> 
				</select>
				<!-- Dados para o host de destino --> 
				<textarea name='contas' style="margin: 2px; height: 258px; width: 554px" placeholder='conta@dominio;senhaorigem;conta@dominio;senhadestino<Enter> ' ><?php if(isset($_SESSION['contas'] )){ echo  $_SESSION['contas'];}?></textarea>
				<br/>
				
				<input type='button' id='migrar' value="Iniciar Migracao" /> 
			        <select name='tipo' id='tipo'>
                                  <option value="manual">Migração Manual</option>
                                  <option value="agendamento">Agendamento</option>
                                </select>
                                <br/>
                                <div id='configAgendamento' style='display: none;'>
                                        <label>Agendamento de Migracao--> </label>
                                         <label> Data: </label>
                                        <select name='data' >
                                                       <?php
                                                       for($i=1;$i<=31;$i++){
                                                        
                                                                 if($i<10){
                                                                        echo "<option value='0$i'>0$i</option> \n";
                                                                  }else{
                                                                        echo "<option value='$i'>$i</option> \n";
                                                                  }
                                                  
                                                        } 
                                                        ?>
                                        </select>
                                        
                                        <label> Hora: </label>
                                        <select name='hora' >
                                                       <?php
                                                       for($i=0;$i<=23;$i++){
                                                        
                                                                 if($i<10){
                                                                        echo "<option value='0$i'>0$i h 00</option> \n";
                                                                  }else{
                                                                        echo "<option value='$i'>$i h 00</option> \n";
                                                                  }
                                                  
                                                        } 
                                                        ?>
                                        </select>
                                        
                                </div>
                                <script>
                                        
                                                $( "#tipo" ).change(function() {
                                                  
                                                  var tipo = $( "#tipo option:selected").val();
                                                  
                                                  if (tipo == 'agendamento') {
		                                          $('#configAgendamento').show();
	                                            }else{
	                                                 $('#configAgendamento').hide();
	                                            } 
                                                });
                                                
                                                
                                                   $( "#tipo" ).change(function() {
                                                  
                                                  var tipo = $( "#tipo option:selected").val();
                                                  
                                                  if (tipo == 'agendamento') {
                                                  
		                                          $('#migrar').val('Agendar Migracao');
	                                            }else{
	                                                   $('#migrar').val('Iniciar Migracao');
	                                            } 
                                                });
                                        
                                </script>
                                
                                
                                
			</form>
			
		</section>
		
		<footer>
			<pre id='ajaxResponse' class='centro'>
			</pre>
		
		</footer>
	</body>
		
</html>


