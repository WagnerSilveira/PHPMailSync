-- phpMyAdmin SQL Dump
-- version 3.3.3
-- http://www.phpmyadmin.net
--
-- Servidor: mysql01-farm13.kinghost.net
-- Tempo de Geração: Mar 04, 2014 as 08:12 PM
-- Versão do Servidor: 5.5.32
-- Versão do PHP: 5.2.17

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Banco de Dados: ``
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `phpmailsync`
--

CREATE TABLE IF NOT EXISTS `phpmailsync` (
  `idmigracao` varchar(30) NOT NULL,
  `host1` varchar(180) NOT NULL,
  `ssl1` int(1) NOT NULL,
  `tipo1` varchar(4) NOT NULL,
  `host2` varchar(180) NOT NULL,
  `ssl2` int(1) NOT NULL,
  `tipo2` varchar(4) NOT NULL,
  `contas` text NOT NULL,
  `status` int(1) NOT NULL,
  PRIMARY KEY (`idmigracao`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Extraindo dados da tabela `phpmailsync`
--


-- --------------------------------------------------------

--
-- Estrutura da tabela `phpmailsync_execucao`
--

CREATE TABLE IF NOT EXISTS `phpmailsync_execucao` (
  `execid` int(11) NOT NULL AUTO_INCREMENT,
  `conta` varchar(90) NOT NULL,
  `ppid` int(10) DEFAULT NULL,
  `pid` varchar(10) NOT NULL,
  `status` varchar(10) NOT NULL,
  `inicio` varchar(25) NOT NULL,
  `fim` varchar(25) DEFAULT NULL,
  `logs` varchar(90) NOT NULL,
  `idmigracao` varchar(30) NOT NULL,
  PRIMARY KEY (`execid`),
  KEY `idmigracao` (`idmigracao`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=0 ;

--
-- Extraindo dados da tabela `phpmailsync_execucao`
--


--
-- Restrições para as tabelas dumpadas
--

--
-- Restrições para a tabela `phpmailsync_execucao`
--
ALTER TABLE `phpmailsync_execucao`
  ADD CONSTRAINT `phpmailsync_execucao_ibfk_1` FOREIGN KEY (`idmigracao`) REFERENCES `phpmailsync` (`idmigracao`);
