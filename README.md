PHPMailSync
===========


#####Informação

Esta aplicação pode ser utilizada em qualquer servidor ou máquina que possua o interpretador PHP 5.3 ou superior instalado e com a extensão IMAP ativa .

A execução dele no momento é apenas por linha de comando. 
Pode ser baixado o arquivo .phar com os arquivos imap.controle.php e Imap.class.php incluso para executar, sem instalação necessária.
___

#####Requisitos


PHP 5.3 ou superior<br />Biblioteca PHP_IMAP

    Windows->  php_imap.dll
    Linux-> imap.so

Instalar no PHP windows
    
    Instalador: http://windows.php.net/downloads/releases/php-5.3.28-nts-Win32-VC9-x86.msi
    Biblioteca php_imap.dll:  http://originaldll.com/file/php_imap.dll/29395.html


Instalar PHP no Linux

    sudo apt-get install php5
    sudo apt-get install php5-imap


___

#####Como Utilizar

*Dados para o servidor de Origem*

Parametro | Descrição
----------------- | -------------------
| --host1    | host/ip do servidor imap/pop3
| --usuario1 | conta@dominio.com
| --senha1   | *****
| --tipo1    | **Opcional** (imap ou pop3), não informado, padrão imap
| --ssl1     | **Opcional**, não informado, padrão sem SSL  


*Dados para o servidor de Destino(Mesmo padrão que a origem)*

Parametro | Descrição
----------------- | -------------------
| --host2    | host/ip do servidor imap/pop3
| --usuario2 | conta@dominio.com
| --senha2   | *****
| --tipo2    | **Opcional** (imap ou pop3), não informado, padrão imap
| --ssl2     | **Opcional**, não informado, padrão sem SSL   


**Obs:** Entre colchetes '[ ]' e função é opcional e pode ser ignorada caso não seja necessário.

    php phpmailsync.phar  --host1 imap02.provedor1.com.br --usuario1 conta@dominio --senha1 ********  [--tipo1 imap] [--ssl1] --host2  imap04.provedor2.com.br --usuario2 conta@dominio --senha2  ****** -[-tipo2 imap] [--ssl2] 

**Opções adicionais:** 

--ignorarespaco: **Opcional**, Quando informada, permite  iniciar a migração caso o espaço disponível na conta de destino for menor que o utilizado na conta de origem.
___

#####Download

1.0v: https://www.dropbox.com/sh/zegphwngimxfjak/ZIcSYKeOel
Ultima atualização: https://www.dropbox.com/s/z2pgj2qtw07ivfa/phpmailsync-cli.phar
