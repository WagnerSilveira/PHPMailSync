<?php
/*
 * Direitos Autorais (C) 2014 Wagner Hahn Silveira.
 *
 * Autor:
 *	Wagner Hahn Silveira <wagnerhsilveira@gmail.com>
 *
 * Este software é licenciado sob os termos da Licença Pública Geral GNU
 * License versão 2, como publicada pela Fundação de Software Livre, e
 * pode ser copiada, distribuida, e modificada sob estes termos.
 *
 * Este programa é distribuido na esperança que será util,
 * mas SEM NENHUMA GARANTIA; sem mesmo a garantia implícita de 
 * COMERCIALIZAÇÃO ou de ADEQUAÇÃO A UM DETERMINADO FIM. veja o 
 * Licença Pública Geral GNU para obter mais detalhes.
 *
 */
$p = new Phar('phpmailsync-cli.phar');
$p->startBuffering();
$p->addFile('Imap.class.php');
$p->addFile('phpmailsync-cli.php');
$p->setStub($p->createDefaultStub('phpmailsync-cli.php'));
$p->stopBuffering();

?>
