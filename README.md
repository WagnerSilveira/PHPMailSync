PHPMailSync
===========


Esta aplicação pode ser utilizada em qualquer servidor ou máquina que possua o interpretador PHP 5.3 ou superior instalado e com a extensão IMAP ativa .

A execução dele no momento é apenas por linha de comando. 
Pode ser baixado o arquivo .phar com os arquivos imap.controle.php e Imap.class.php incluso para executar, sem instalação necessária.

++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

COMO UTILIZAR:

==== Dados para o servidor de Origem ====

--host1 host/ip do servidor imap/pop3

--usuario1 conta@dominio.com

--senha1 *****

--tipo1 ----> opcional (imap ou pop3), nao informado, padrao imap

--ssl1 ----> opcional, nao informado sem ssl

==== Dados para o servidor de Destino(Mesmo padrão que a origem) ===

--host2

--usuario2

--senha2

--tipo2 opcional (imap ou pop3), nao informado, padrao imap

--ssl2 opcional, nao informado sem ssl

Obs: Entre colchetes '[ ]' e função é opcional e pode ser ignorada caso não seja necessário.


EX: php phpmailsync.phar --host1 imap02.provedor1.com.br --usuario1 conta@dominio --senha1 ******** [--tipo1 imap] [--ssl1] --host2 imap04.provedor2.com.br --usuario2 conta@dominio --senha2 ****** -[-tipo2 imap] [--ssl2]


Opções adicionais:

--ignorarespaco: [Opcional] -> Quando informada, permite iniciar a migração caso o espaço disponível na conta de destino 
for menor que o utilizado na conta de origem.

++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

DOWNLOAD:

1.0v -Download : https://www.dropbox.com/s/aygk9es7wvi6hgx/phpmailsync.phar
