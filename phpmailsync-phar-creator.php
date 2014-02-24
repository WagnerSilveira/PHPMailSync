<?php
$p = new Phar('phpmailsync.phar');
$p->startBuffering();
$p->addFile('Imap.class.php');
$p->addFile('imap.controle.php');
$p->setStub($p->createDefaultStub('imap.controle.php'));
$p->stopBuffering();

?>
