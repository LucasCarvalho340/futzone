-- FUTZONE - schema.sql (estrutura + carga inicial)
-- Admin de teste: admin@futzone.com / admin123 (troque depois!)

CREATE DATABASE IF NOT EXISTS `futzone` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `futzone`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/11/2026 às 22:09
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `futzone`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `categorias`
--

CREATE TABLE `categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `categorias`
--

INSERT INTO `categorias` (`id`, `nome`) VALUES
(1, 'Campo'),
(3, 'Futsal'),
(4, 'Infantil'),
(2, 'Society');

-- --------------------------------------------------------

--
-- Estrutura para tabela `itens_pedido`
--

CREATE TABLE `itens_pedido` (
  `id` int(10) UNSIGNED NOT NULL,
  `pedido_id` int(10) UNSIGNED NOT NULL,
  `produto_id` int(10) UNSIGNED NOT NULL,
  `quantidade` int(10) UNSIGNED NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(10) UNSIGNED NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `forma_pagamento` enum('PIX','Boleto','Cartão') NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('Pendente','Pago','Enviado','Entregue','Cancelado') NOT NULL DEFAULT 'Pendente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `produtos`
--

CREATE TABLE `produtos` (
  `id` int(10) UNSIGNED NOT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(120) NOT NULL,
  `descricao` text NOT NULL,
  `preco` decimal(10,2) NOT NULL,
  `estoque` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `imagem` varchar(255) NOT NULL DEFAULT 'sem-imagem.jpg',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Despejando dados para a tabela `produtos`
--

INSERT INTO `produtos` (`id`, `categoria_id`, `nome`, `descricao`, `preco`, `estoque`, `imagem`, `ativo`, `created_at`) VALUES
(1, 1, 'Nike Mercurial Vapor', 'Chuteira de campo leve, feita para velocidade. Ótima aderência e ajuste firme no pé.', 499.90, 15, 'nike-mercurial-vapor.jpg', 1, '2026-11-09 20:02:48'),
(2, 1, 'Adidas Predator', 'Chuteira de campo com foco em controle e precisão nos passes e finalizações.', 549.90, 12, 'adidas-predator.jpg', 1, '2026-11-09 20:02:48'),
(3, 1, 'Puma Future', 'Chuteira de campo com ajuste flexível e confortável, ideal para jogadores criativos.', 459.90, 10, 'puma-future.jpg', 1, '2026-11-09 20:02:48'),
(4, 1, 'Nike Phantom', 'Chuteira de campo com textura no peito do pé para melhor toque na bola.', 529.90, 8, 'nike-phantom.jpg', 1, '2026-11-09 20:02:48'),
(5, 1, 'Adidas F50', 'Chuteira de campo ultraleve para atacantes rápidos e decisivos.', 579.90, 6, 'adidas-f50.jpg', 1, '2026-11-09 20:02:48'),
(6, 2, 'Nike Mercurial Society', 'Versão society da Mercurial. Solado com travas baixas para grama sintética.', 349.90, 14, 'nike-mercurial-society.jpg', 1, '2026-11-09 20:02:48'),
(7, 2, 'Adidas Predator Society', 'Chuteira society com excelente controle de bola e conforto para jogos longos.', 369.90, 10, 'adidas-predator-society.jpg', 1, '2026-11-09 20:02:48'),
(8, 2, 'Puma Future Society', 'Chuteira society leve, com boa estabilidade para mudanças de direção.', 329.90, 9, 'puma-future-society.jpg', 1, '2026-11-09 20:02:48'),
(9, 3, 'Nike Lunar Gato', 'Tênis de futsal com solado de borracha para quadra e ótima tração.', 299.90, 12, 'nike-lunar-gato.jpg', 1, '2026-11-09 20:02:48'),
(10, 3, 'Adidas Top Sala', 'Chuteira de futsal clássica, resistente e confortável para treinos e jogos.', 279.90, 7, 'adidas-top-sala.jpg', 1, '2026-11-09 20:02:48'),
(11, 4, 'Chuteira Infantil Nike', 'Chuteira infantil leve e segura, perfeita para as primeiras partidas.', 199.90, 20, 'infantil-nike.jpg', 1, '2026-11-09 20:02:48'),
(12, 4, 'Chuteira Infantil Adidas', 'Chuteira infantil com fechamento fácil e ótimo conforto para crianças.', 189.90, 18, 'infantil-adidas.jpg', 1, '2026-11-09 20:02:48');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo` enum('cliente','admin') NOT NULL DEFAULT 'cliente',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_categorias_nome` (`nome`);

--
-- Índices de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_itens_pedido` (`pedido_id`),
  ADD KEY `idx_itens_produto` (`produto_id`);

--
-- Índices de tabela `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pedidos_usuario` (`usuario_id`);

--
-- Índices de tabela `produtos`
--
ALTER TABLE `produtos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_produtos_categoria` (`categoria_id`),
  ADD KEY `idx_produtos_nome` (`nome`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuarios_email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `itens_pedido`
--
ALTER TABLE `itens_pedido`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `produtos`
--
ALTER TABLE `produtos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `itens_pedido`
--
ALTER TABLE `itens_pedido`
  ADD CONSTRAINT `fk_itens_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_itens_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `fk_pedidos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;

--
-- Restrições para tabelas `produtos`
--
ALTER TABLE `produtos`
  ADD CONSTRAINT `fk_produtos_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON UPDATE CASCADE;
--
-- Usuário administrador de teste (senha em hash bcrypt)
--
INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `tipo`) VALUES
(1, 'Administrador', 'admin@futzone.com', '$2y$10$lfKjssWamAZPC3yIdK4EbeAkF.V4Nuc3ajJzvddA9KTKO7F30Q/Vi', 'admin');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
