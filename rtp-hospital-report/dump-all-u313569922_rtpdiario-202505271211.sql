/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.7.2-MariaDB, for Win64 (AMD64)
--
-- Host: 193.203.175.60    Database: u313569922_rtpdiario
-- ------------------------------------------------------
-- Server version	10.11.10-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `agenda`
--

DROP TABLE IF EXISTS `agenda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `agenda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `dia_semana` varchar(255) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `consulta_por_dia` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `agenda_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`),
  CONSTRAINT `agenda_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6569 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias`
--

DROP TABLE IF EXISTS `categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL COMMENT 'Nome da categoria',
  `unidade_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `fk_categorias_unidade` (`unidade_id`),
  CONSTRAINT `fk_categorias_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de categorias de serviços';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `categorias_ses`
--

DROP TABLE IF EXISTS `categorias_ses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categorias_ses` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da categoria SES',
  `categoria_ses` varchar(255) NOT NULL COMMENT 'Nome da categoria SES',
  `subcategoria_ses` varchar(255) NOT NULL COMMENT 'Nome da subcategoria SES',
  `id_ses` int(11) NOT NULL COMMENT 'Identificador SES',
  `id_unidade` int(11) DEFAULT NULL COMMENT 'Referência à tabela unidade',
  PRIMARY KEY (`id`),
  KEY `fk_categorias_ses_unidade` (`id_unidade`),
  CONSTRAINT `fk_categorias_ses_unidade` FOREIGN KEY (`id_unidade`) REFERENCES `unidade` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de categorias SES e suas subcategorias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comissoes_lancamentos`
--

DROP TABLE IF EXISTS `comissoes_lancamentos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comissoes_lancamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL COMMENT 'ID da unidade',
  `comissao_nucleo_id` int(11) NOT NULL COMMENT 'ID da comissão ou núcleo',
  `valor` tinyint(1) NOT NULL COMMENT 'Valor do lançamento (0 = false, 1 = true)',
  `data` date NOT NULL COMMENT 'Data do lançamento',
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `comissao_nucleo_id` (`comissao_nucleo_id`),
  CONSTRAINT `comissoes_lancamentos_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comissoes_lancamentos_ibfk_2` FOREIGN KEY (`comissao_nucleo_id`) REFERENCES `comissoes_nucleos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lançamentos financeiros das comissões';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comissoes_metas`
--

DROP TABLE IF EXISTS `comissoes_metas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comissoes_metas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL COMMENT 'ID da unidade',
  `comissao_nucleo_id` int(11) NOT NULL COMMENT 'ID da comissão ou núcleo',
  `meta` tinyint(1) NOT NULL COMMENT 'Meta estabelecida (0 = false, 1 = true)',
  `data_inicio` date NOT NULL COMMENT 'Data de início da meta',
  `data_fim` date NOT NULL COMMENT 'Data de término da meta',
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `comissao_nucleo_id` (`comissao_nucleo_id`),
  CONSTRAINT `comissoes_metas_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comissoes_metas_ibfk_2` FOREIGN KEY (`comissao_nucleo_id`) REFERENCES `comissoes_nucleos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro das metas das comissões';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `comissoes_nucleos`
--

DROP TABLE IF EXISTS `comissoes_nucleos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comissoes_nucleos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `descricao` varchar(255) NOT NULL COMMENT 'Descrição da comissão ou núcleo',
  PRIMARY KEY (`id`),
  UNIQUE KEY `descricao` (`descricao`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Lista de comissões e núcleos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `justificativa`
--

DROP TABLE IF EXISTS `justificativa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `justificativa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `justificativa` text DEFAULT NULL,
  `data_cadastro` datetime NOT NULL DEFAULT current_timestamp(),
  `autorizacao` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `justificativa_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`),
  CONSTRAINT `justificativa_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meta`
--

DROP TABLE IF EXISTS `meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `meta` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `servico_id` int(11) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date NOT NULL,
  `ativa` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `meta_ibfk_1` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1118 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pdt`
--

DROP TABLE IF EXISTS `pdt`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pdt` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `meta` varchar(255) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `pdt_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`),
  CONSTRAINT `pdt_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=744 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `produtos_estrategicos`
--

DROP TABLE IF EXISTS `produtos_estrategicos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `produtos_estrategicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL COMMENT 'Nome do produto estratégico',
  `unidade_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `fk_produtos_unidade` (`unidade_id`),
  CONSTRAINT `fk_produtos_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de produtos estratégicos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rtpdiario`
--

DROP TABLE IF EXISTS `rtpdiario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `rtpdiario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL,
  `servico_id` int(11) NOT NULL,
  `agenda_id` int(11) NOT NULL,
  `mes` int(11) NOT NULL DEFAULT 1,
  `ano` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `agendados` int(11) NOT NULL,
  `executados` int(11) NOT NULL,
  `data` date NOT NULL DEFAULT '2024-01-01',
  `executados_por_encaixe` int(11) NOT NULL,
  `servico_derivado` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `servico_id` (`servico_id`),
  KEY `agenda_id` (`agenda_id`),
  CONSTRAINT `rtpdiario_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`),
  CONSTRAINT `rtpdiario_ibfk_2` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`),
  CONSTRAINT `rtpdiario_ibfk_3` FOREIGN KEY (`agenda_id`) REFERENCES `agenda` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50855 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servico`
--

DROP TABLE IF EXISTS `servico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servico` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unidade_id` int(11) NOT NULL,
  `natureza` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `empresa` varchar(255) NOT NULL,
  `meta` varchar(255) NOT NULL,
  `grupo_id` int(11) DEFAULT NULL COMMENT 'Referência para tabela servico_grupo',
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  KEY `idx_grupo_id` (`grupo_id`),
  CONSTRAINT `fk_servico_grupo` FOREIGN KEY (`grupo_id`) REFERENCES `servico_grupo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `servico_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=734 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servico_categorias`
--

DROP TABLE IF EXISTS `servico_categorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servico_categorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único da relação serviço-categoria',
  `id_ses` int(11) NOT NULL COMMENT 'Identificador SES',
  `id_pe` int(11) NOT NULL COMMENT 'Referência ao produto estratégico',
  `id_categoria` int(11) NOT NULL COMMENT 'Referência à categoria',
  `id_subcategoria` int(11) NOT NULL COMMENT 'Referência à subcategoria',
  `servico_id` int(11) NOT NULL COMMENT 'Referência ao serviço',
  `id_unidade` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_servico_categorias_pe` (`id_pe`),
  KEY `fk_servico_categorias_categoria` (`id_categoria`),
  KEY `fk_servico_categorias_subcategoria` (`id_subcategoria`),
  KEY `fk_servico_categorias_servico` (`servico_id`),
  CONSTRAINT `fk_servico_categorias_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_servico_categorias_pe` FOREIGN KEY (`id_pe`) REFERENCES `produtos_estrategicos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_servico_categorias_servico` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_servico_categorias_subcategoria` FOREIGN KEY (`id_subcategoria`) REFERENCES `subcategorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relação entre serviços, categorias e produtos estratégicos';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servico_grupo`
--

DROP TABLE IF EXISTS `servico_grupo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servico_grupo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(50) NOT NULL,
  `descricao` text DEFAULT NULL,
  `cor` varchar(7) DEFAULT '#6B7280' COMMENT 'Cor hexadecimal para interface',
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `idx_nome` (`nome`),
  KEY `idx_ativo` (`ativo`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Grupos médicos para categorização de serviços';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `servicoderivado`
--

DROP TABLE IF EXISTS `servicoderivado`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `servicoderivado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `servico_id` int(11) NOT NULL,
  `natureza` varchar(255) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `servico_id` (`servico_id`),
  CONSTRAINT `servicoderivado_ibfk_1` FOREIGN KEY (`servico_id`) REFERENCES `servico` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=185 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `subcategorias`
--

DROP TABLE IF EXISTS `subcategorias`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subcategorias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL COMMENT 'Nome da subcategoria',
  `unidade_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`),
  KEY `fk_subcategorias_unidade` (`unidade_id`),
  CONSTRAINT `fk_subcategorias_unidade` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabela de subcategorias dentro das categorias';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `unidade`
--

DROP TABLE IF EXISTS `unidade`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `login` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `unidade_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `unidade_id` (`unidade_id`),
  CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`unidade_id`) REFERENCES `unidade` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'u313569922_rtpdiario'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2025-05-27 12:11:38
