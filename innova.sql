-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 05/11/2025 às 16:30
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `innova`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes`
--

CREATE TABLE `configuracoes` (
  `id` int(11) NOT NULL,
  `chave` varchar(100) NOT NULL,
  `valor` text NOT NULL,
  `tipo` enum('string','json','boolean','integer') DEFAULT 'string',
  `descricao` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `configuracoes`
--

INSERT INTO `configuracoes` (`id`, `chave`, `valor`, `tipo`, `descricao`, `created_at`, `updated_at`) VALUES
(1, 'empresa_nome', 'InnovaMeta Solutions', 'string', 'Nome da empresa', '2025-10-23 17:00:00', '2025-10-23 17:00:00'),
(2, 'empresa_telefone', '(21) 98773-6603', 'string', 'Telefone para contato', '2025-10-23 17:00:00', '2025-10-23 17:00:00'),
(3, 'empresa_email', 'contato@innovameta.com', 'string', 'Email para contato', '2025-10-23 17:00:00', '2025-10-23 17:00:00'),
(4, 'empresa_endereco', 'Rua Marechal Soares de Andreia 90, Realengo RJ', 'string', 'Endereço da empresa', '2025-10-23 17:00:00', '2025-10-23 17:00:00'),
(5, 'whatsapp_numero', '5521987736603', 'string', 'Número do WhatsApp para contato', '2025-10-23 17:00:00', '2025-10-23 17:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_sistema`
--

CREATE TABLE `logs_sistema` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `acao` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `mensagens`
--

CREATE TABLE `mensagens` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `tipo` enum('cliente','admin','sistema') DEFAULT 'cliente',
  `lida` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `orcamentos`
--

CREATE TABLE `orcamentos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `plano_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao_projeto` text NOT NULL,
  `prazo_desejado` varchar(50) DEFAULT NULL,
  `orcamento_maximo` decimal(10,2) DEFAULT NULL,
  `orcamento_base` decimal(10,2) NOT NULL,
  `orcamento_negociado` decimal(10,2) DEFAULT NULL,
  `orcamento_final` decimal(10,2) DEFAULT NULL,
  `status` enum('rascunho','pendente','em_analise','aprovado','recusado','cancelado') DEFAULT 'rascunho',
  `observacoes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `orcamentos`
--

INSERT INTO `orcamentos` (`id`, `codigo`, `usuario_id`, `plano_id`, `titulo`, `descricao_projeto`, `prazo_desejado`, `orcamento_maximo`, `orcamento_base`, `orcamento_negociado`, `orcamento_final`, `status`, `observacoes`, `created_at`, `updated_at`) VALUES
(1, 'ORC202511058656', 13, 1, 'Orçamento - Solutions', 'rgiuhliwe', '2 meses', 20000.00, 750.00, 5000.00, NULL, 'aprovado', NULL, '2025-11-05 13:44:24', '2025-11-05 13:48:07'),
(2, 'ORC202511058859', 14, 2, 'Orçamento - Innova', 'site terapeuta aura', '2 meses', 3000.00, 2500.00, 2600.00, NULL, 'aprovado', NULL, '2025-11-05 14:07:15', '2025-11-05 14:09:43'),
(3, 'ORC202511059405', 13, 3, 'Orçamento - Meta', 'khdvbiH V', '3 meses', 5252525.00, 1500.00, NULL, NULL, 'aprovado', NULL, '2025-11-05 14:11:13', '2025-11-05 14:11:49');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pagamentos`
--

CREATE TABLE `pagamentos` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `parcelas` int(11) DEFAULT 1,
  `metodo` enum('pix','cartao_credito','cartao_debito','boleto','transferencia') DEFAULT 'pix',
  `status` enum('pendente','processando','aprovado','recusado','cancelado','estornado') DEFAULT 'pendente',
  `codigo_transacao` varchar(100) DEFAULT NULL,
  `data_pagamento` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `planos`
--

CREATE TABLE `planos` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `descricao` text NOT NULL,
  `preco_base` decimal(10,2) NOT NULL,
  `tipo` enum('desenvolvimento','consultoria','manutencao') DEFAULT 'desenvolvimento',
  `destaque` tinyint(1) DEFAULT 0,
  `ordem_exibicao` int(11) DEFAULT 0,
  `ativo` tinyint(1) DEFAULT 1,
  `caracteristicas` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`caracteristicas`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `planos`
--

INSERT INTO `planos` (`id`, `nome`, `slug`, `descricao`, `preco_base`, `tipo`, `destaque`, `ordem_exibicao`, `ativo`, `caracteristicas`, `created_at`, `updated_at`) VALUES
(1, 'Solutions', 'solutions', 'Ideal para quem está começando e precisa de presença online profissional.', 750.00, 'desenvolvimento', 0, 1, 1, '[\"Site One Page\", \"Formulário de Contato\", \"SEO Básico\", \"Design elaborado\", \"Integração com redes sociais\"]', '2025-10-23 17:00:00', '2025-10-23 17:00:00'),
(2, 'Innova', 'innova', 'Solução perfeita para Inovar com Melhores Resultados.', 2500.00, 'desenvolvimento', 1, 2, 1, '[\"Páginas inovadoras estilizadas\", \"Blog ou Portfólio\", \"Uso de linguagens de back e front end completas (SQL, PHP)\", \"Suporte técnico 1 ano\"]', '2025-10-23 17:00:00', '2025-10-23 17:00:00'),
(3, 'Meta', 'meta', 'Um site para empresas em desenvolvimento e que necessita de ajuda.', 1500.00, 'desenvolvimento', 0, 3, 1, '[\"Até 5 páginas\", \"Dashboard Autoral\", \"Hospedagem 1 ano\", \"Integração com WhatsApp e Google Maps\"]', '2025-10-23 17:00:00', '2025-10-23 17:00:00');

-- --------------------------------------------------------

--
-- Estrutura para tabela `projetos`
--

CREATE TABLE `projetos` (
  `id` int(11) NOT NULL,
  `orcamento_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('planejamento','desenvolvimento','revisao','finalizado','entregue') DEFAULT 'planejamento',
  `data_inicio` date DEFAULT NULL,
  `data_entrega_prevista` date DEFAULT NULL,
  `data_entrega_real` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `genero` enum('homem','mulher','outro') DEFAULT 'outro',
  `senha` varchar(255) NOT NULL,
  `nivel` enum('admin','cliente','premium') DEFAULT 'cliente',
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expira` datetime DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `cpf`, `data_nascimento`, `genero`, `senha`, `nivel`, `reset_token`, `reset_token_expira`, `ativo`, `created_at`, `updated_at`) VALUES
(6, 'fruit', 'davidnea1234@gmail.com', '(21) 98773-6603', '206.839.777-33', '2007-12-06', 'homem', '$2y$10$Dz5ksKSfVP32lsi/AyiNruqHVk.1IZGjbX2fXwSZqDqGGVqCHAm/G', 'admin', '248a637aa1536c3613a1bef97bd15c3201eab53d1317c8f1a7bf37b4b6383b32', '2025-10-22 04:32:32', 1, '2025-07-16 06:58:05', '2025-10-22 07:32:32'),
(10, 'arthur nea', 'sararadolixo@gmail.com', '(21) 96803-1634', '463.222.478-1', '2013-07-12', 'homem', '$2y$10$uCw7DbK.VgHC2cWjQMg3zuOgVxOqMvy6ED4A0BxeBV1MRhuSg9uN2', 'cliente', NULL, NULL, 1, '2025-07-17 01:46:42', '2025-07-17 01:46:42'),
(11, 'MARCELE CRISTINA', 'marcelecristina47@gmail.com', '(21) 98285-6312', '169.833.277-22', '1988-09-10', 'mulher', '$2y$10$HrqFhjSr38DAtjkhi0jk.u6mxzZA.KxIhHGBkw6sOEY6SKGskwq2m', 'cliente', NULL, NULL, 1, '2025-07-29 00:09:16', '2025-07-29 00:09:16'),
(12, 'FABIO DE OLIVEIRA RODRIGUES', 'fabiochico25@gmail.com', '(21) 96462-1921', '092.689.767-57', '1980-04-08', 'homem', '$2y$10$.PO17qvmG5qN7CfHGgxU7uJWJZtcY8RsZ19zDYQa2gRIem99xbIN2', 'cliente', NULL, NULL, 1, '2025-07-31 21:00:52', '2025-07-31 21:00:52'),
(13, 'Davi', 'odavisuporte@gmail.com', '(21) 99914-2907', '150.002.567-43', '2007-08-29', 'homem', '$argon2id$v=19$m=65536,t=4,p=1$UnFyV1lCYzBSWGRnUFlaLg$dQh30RO04Cu759jSuAe3uUGBlqQG8Xv1XPY4Fp9nyWg', 'admin', NULL, NULL, 1, '2025-11-05 13:43:42', '2025-11-05 13:44:44'),
(14, 'Lucas', 'lcRajada@gmail.com', '(21) 97685-3385', '052.467.837-58', '2008-06-02', 'homem', '$argon2id$v=19$m=65536,t=4,p=1$ODd2ellKdzVkYm0xQzY2cg$HLUG0k+y5syXHQEuUVEer1iVA5qFF+HmfjjV2gc5Zx0', 'cliente', NULL, NULL, 1, '2025-11-05 14:06:41', '2025-11-05 14:06:41');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `chave` (`chave`);

--
-- Índices de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_log_acao` (`acao`),
  ADD KEY `idx_log_data` (`created_at`);

--
-- Índices de tabela `mensagens`
--
ALTER TABLE `mensagens`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orcamento_id` (`orcamento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_mensagem_data` (`created_at`);

--
-- Índices de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `plano_id` (`plano_id`),
  ADD KEY `idx_orcamento_status` (`status`),
  ADD KEY `idx_orcamento_data` (`created_at`);

--
-- Índices de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orcamento_id` (`orcamento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_pagamento_status` (`status`),
  ADD KEY `idx_pagamento_data` (`created_at`);

--
-- Índices de tabela `planos`
--
ALTER TABLE `planos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_plano_tipo` (`tipo`),
  ADD KEY `idx_plano_ativo` (`ativo`),
  ADD KEY `idx_plano_ordem` (`ordem_exibicao`);

--
-- Índices de tabela `projetos`
--
ALTER TABLE `projetos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orcamento_id` (`orcamento_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `idx_projeto_status` (`status`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cpf` (`cpf`),
  ADD KEY `idx_usuario_email` (`email`),
  ADD KEY `idx_usuario_nivel` (`nivel`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `configuracoes`
--
ALTER TABLE `configuracoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `logs_sistema`
--
ALTER TABLE `logs_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `mensagens`
--
ALTER TABLE `mensagens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `orcamentos`
--
ALTER TABLE `orcamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `pagamentos`
--
ALTER TABLE `pagamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `planos`
--
ALTER TABLE `planos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `projetos`
--
ALTER TABLE `projetos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `logs_sistema`
--
ALTER TABLE `logs_sistema`
  ADD CONSTRAINT `logs_sistema_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `mensagens`
--
ALTER TABLE `mensagens`
  ADD CONSTRAINT `mensagens_ibfk_1` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensagens_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `mensagens_ibfk_3` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `orcamentos`
--
ALTER TABLE `orcamentos`
  ADD CONSTRAINT `orcamentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orcamentos_ibfk_2` FOREIGN KEY (`plano_id`) REFERENCES `planos` (`id`);

--
-- Restrições para tabelas `pagamentos`
--
ALTER TABLE `pagamentos`
  ADD CONSTRAINT `pagamentos_ibfk_1` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pagamentos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Restrições para tabelas `projetos`
--
ALTER TABLE `projetos`
  ADD CONSTRAINT `projetos_ibfk_1` FOREIGN KEY (`orcamento_id`) REFERENCES `orcamentos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `projetos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
