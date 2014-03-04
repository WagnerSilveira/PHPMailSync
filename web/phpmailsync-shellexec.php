<?php
echo shell_exec("php PHPMailSync/imap.controle.php --host1 ".$_GET['host1']." --usuario1 ".$_GET['usuario1']."  --senha1 ".$_GET['senha1']."  --tipo1 ".$_GET['tipo1'] ." --host2 ".$_GET['host2'] ."  --usuario2 ".$_GET['usuario2'] ."  --senha2 ".$_GET['senha2'] ." > ".$_GET['log'].".txt ");
?>
