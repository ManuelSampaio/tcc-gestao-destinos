-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19-Abr-2025 às 18:16
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `turismo_angola`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id_avaliacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `nota` decimal(3,1) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `data_avaliacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nome_categoria` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nome_categoria`) VALUES
(1, 'Praias e Litoral'),
(2, 'Parques Nacionais e Reservas'),
(3, 'Montanhas e Formações Rochosas'),
(4, 'Patrimônio Histórico-Cultural'),
(5, 'Ecoturismo e Natureza'),
(6, 'Desertos e Savanas'),
(7, 'Turismo Urbano'),
(8, 'Turismo Rural e Comunitário'),
(9, 'Cultura e Festivais'),
(10, 'Turismo Religioso'),
(11, 'Roteiros Etnográficos');

-- --------------------------------------------------------

--
-- Estrutura da tabela `destinos_turisticos`
--

CREATE TABLE `destinos_turisticos` (
  `id` int(11) NOT NULL,
  `nome_destino` varchar(255) NOT NULL,
  `descricao` text NOT NULL,
  `imagem` varchar(255) NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `is_maravilha` tinyint(1) DEFAULT 0,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_localizacao` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `destinos_turisticos`
--

INSERT INTO `destinos_turisticos` (`id`, `nome_destino`, `descricao`, `imagem`, `id_categoria`, `is_maravilha`, `data_cadastro`, `id_localizacao`) VALUES
(1, 'Fenda da Tundavala', 'Formação geológica impressionante com um desfiladeiro de mais de 1000m de profundidade, oferecendo vistas panorâmicas espetaculares do planalto para a planície.', 'tundavala.jpg', 3, 1, '2025-04-13 15:19:11', 1),
(2, 'Floresta do Maiombe', 'Uma das florestas tropicais mais densas de África, com biodiversidade única e raridades botânicas, considerada o pulmão de Angola.', 'maiombe.jpg', 5, 1, '2025-04-13 15:19:11', 2),
(3, 'Grutas do Nzenzo', 'Formações rochosas e cavernas misteriosas, um destino espiritual para muitas comunidades locais com formações calcárias impressionantes.', 'nzenzo.jpg', 3, 1, '2025-04-13 15:19:11', 3),
(4, 'Lagoa Carumbo', 'Uma lagoa de origem vulcânica com água cristalina, rodeada de paisagens impressionantes e uma biodiversidade rica.', 'carumbo.jpg', 5, 1, '2025-04-13 15:19:11', 4),
(5, 'Morro do Môco', 'O ponto mais alto de Angola, localizado no Huambo, com 2.620 metros de altitude e biodiversidade única adaptada à altitude.', 'moco.jpg', 3, 1, '2025-04-13 15:19:11', 5),
(6, 'Quedas de Kalandula', 'Uma das maiores quedas d\'água de África em volume d\'água, com 105 metros de altura e 400 metros de largura.', 'kalandula.jpg', 5, 1, '2025-04-13 15:19:11', 6),
(7, 'Quedas do Rio Chiumbe', 'Quedas d\'água de beleza natural ímpar, localizada na fronteira com a República Democrática do Congo, com cascatas impressionantes.', 'chiumbe.jpg', 5, 1, '2025-04-13 15:19:11', 7),
(8, 'Ilha de Luanda', 'Península com belas praias, restaurantes e vida noturna vibrante, sendo um dos principais pontos turísticos da capital angolana.', 'ilha_luanda.jpg', 1, 0, '2025-04-14 11:50:25', 8),
(9, 'Parque Nacional da Quiçama', 'Um dos maiores parques de safari de Angola, abrigando diversas espécies como elefantes, antílopes e búfalos. Local ideal para observação da vida selvagem.', 'quicama.jpg', 2, 0, '2025-04-14 11:50:25', 9),
(10, 'Parque Nacional do Bicuar', 'Reserva natural com rica biodiversidade, abrigando mais de 16 espécies de antílopes além de outros mamíferos e aves. Excelente para safáris fotográficos.', 'bicuar.jpg', 2, 0, '2025-04-14 11:50:25', 10),
(11, 'Parque Nacional do Iona', 'O maior parque nacional de Angola, com paisagens desérticas deslumbrantes, fauna adaptada e uma diversidade geológica impressionante junto ao oceano.', 'iona.jpg', 2, 0, '2025-04-14 11:50:25', 11),
(12, 'Miradouro da Lua', 'Formações rochosas espetaculares formadas pela erosão, criando uma paisagem semelhante à superfície lunar. Um dos pontos turísticos mais fotografados perto de Luanda.', 'miradouro_lua.jpg', 3, 0, '2025-04-14 11:50:25', 12),
(13, 'Baía Azul', 'Uma das praias mais bonitas de Angola, com águas cristalinas e areias douradas. Local perfeito para banhos de mar e prática de esportes aquáticos.', 'baia_azul.jpg', 1, 0, '2025-04-14 11:50:25', 13),
(14, 'Baía dos Tigres', 'Antiga cidade portuguesa agora abandonada, situada numa península que se transformou em ilha. Local misterioso com ruínas cobertas de areia.', 'baia_tigres.jpg', 1, 0, '2025-04-14 11:50:25', 14),
(15, 'Serra da Leba', 'Estrada serpenteante famosa por suas curvas sinuosas e vistas panorâmicas deslumbrantes. Um marco da engenharia colonial portuguesa.', 'serra_leba.jpg', 3, 0, '2025-04-14 11:50:25', 15),
(16, 'Mussulo', 'Península paradisíaca perto de Luanda com resorts exclusivos, praias de areias brancas e águas calmas. Destino perfeito para relaxamento.', 'mussulo.jpeg', 1, 0, '2025-04-14 11:50:25', 16),
(17, 'Parque de Mangroves', 'Ecossistema de mangue preservado em Cabinda, com flora e fauna únicas. Importante reserva ecológica do norte de Angola.', 'mangroves.jpg', 5, 0, '2025-04-14 11:50:25', 17),
(18, 'Fortaleza de São Miguel', 'Fortificação histórica construída pelos portugueses no século XVI. Hoje abriga o Museu das Forças Armadas e é um importante patrimônio histórico.', 'sao-miguel.jpeg', 4, 0, '2025-04-14 11:50:25', 18),
(19, 'Praia Morena', 'Uma das mais populares praias de Benguela, com infraestrutura para visitantes e águas ideais para banho. Ponto de encontro da população local nos fins de semana.', 'praia_morena.jpg', 1, 0, '2025-04-14 11:50:25', 19),
(20, 'Deserto do Namibe', 'Paisagem desértica única no sudoeste de Angola, com dunas, formações rochosas e a rara planta Welwitschia mirabilis. Um destino para amantes da natureza extrema.', 'deserto_namibe.webp', 6, 0, '2025-04-14 11:50:25', 20),
(21, 'Quedas do Epupa', 'Série de quedas d\'água espetaculares ao longo do rio Cunene, na fronteira entre Angola e Namíbia. As quedas se estendem por mais de 1,5 km, com várias cascatas separadas por ilhas rochosas e baobás centenários, criando um panorama único.', 'quedas_epupa.jpg', 5, 0, '2025-04-14 11:50:25', 21),
(22, 'Welwitschia Mirabilis', 'Reserva que protege exemplares da Welwitschia mirabilis, planta endêmica que pode viver mais de 1000 anos. Um verdadeiro fóssil vivo no deserto de Namibe.', 'welwitschia.jpg', 5, 0, '2025-04-14 11:50:25', 22),
(23, 'Rio Kwanza', 'O maior rio inteiramente angolano, oferecendo oportunidades para pesca esportiva, passeios de barco e paisagens deslumbrantes ao longo de seu curso.', 'rio_kwanza.jpg', 5, 0, '2025-04-14 11:53:58', 23),
(24, 'Cidade de Mbanza Congo', 'Antiga capital do Reino do Congo, hoje Patrimônio Mundial da UNESCO. A cidade preserva ruínas da catedral, do palácio real e outros edifícios históricos que remontam ao século XV, testemunhando o encontro entre a Europa e a África Central.', 'mbanza_congo.jpg', 5, 0, '2025-04-14 11:53:58', 24),
(25, 'Futungo de Belas', 'Área residencial e turística com belas praias e vista para o mar, próxima a Luanda. Lugar ideal para caminhadas e contemplação do pôr do sol.', 'futungo_belas.jpg', 1, 0, '2025-04-14 11:53:58', 25),
(26, 'Rio Longa', 'Rio de águas calmas com margens verdes e rica biodiversidade. Excelente local para observação de aves e passeios de canoa no Cuanza Sul.', 'rio_longa.webp', 5, 0, '2025-04-14 11:53:58', 26),
(27, 'Lagoa Massabi', 'Bela lagoa costeira em Cabinda, com águas tranquilas e vegetação tropical ao redor. Local preservado e ideal para o contato com a natureza.', 'lagoa_massabi.jpg', 5, 0, '2025-04-14 11:53:58', 27),
(28, 'Pedras Negras de Pungo Andongo', 'Formações rochosas gigantes de granito negro, associadas a lendas e histórias locais. Um local de importância histórica e geológica em Malanje.', 'pedras_negras.jpg', 3, 0, '2025-04-14 11:53:58', 28),
(29, 'Serra da Chela', 'Cadeia montanhosa com vistas panorâmicas espetaculares, cachoeiras e clima ameno. Um dos principais atrativos naturais da província da Huíla.', 'serra_chela.jpeg', 3, 0, '2025-04-14 11:53:58', 29),
(30, 'Praia do Bispo', 'Uma das praias urbanas mais populares de Luanda, localizada próxima ao centro da cidade. Oferece belas vistas da baía de Luanda, águas calmas e é frequentada tanto por moradores locais quanto por turistas, com diversos restaurantes e bares nas proximidades.', 'praia_bispo.jpeg', 1, 0, '2025-04-14 11:53:58', 30),
(31, 'Parque Natural Regional da Chimalavera', 'Reserva natural com fauna e flora características do sul de Angola. Um espaço preservado para caminhadas ecológicas e observação de animais.', 'chimalavera.jpg', 2, 0, '2025-04-14 11:53:58', 31),
(32, 'Cristo Rei de Lubango', 'Monumento emblemático situado no topo de um morro na cidade de Lubango, província da Huíla. A estátua de Cristo de braços abertos, semelhante ao Cristo Redentor do Rio de Janeiro, oferece vistas panorâmicas da cidade e é um importante ponto turístico religioso e cultural.', 'cristo_rei.jpg', 4, 0, '2025-04-14 11:53:58', 32),
(33, 'Reserva Natural Integral do Luando', 'Área protegida criada especialmente para a preservação da palanca vermelha, uma espécie endêmica de antílope angolano. Localizada nas províncias de Malanje e Bié, a reserva abrange mais de 8.000 km² de savanas e matas ribeirinhas.', 'luando.jpg', 5, 0, '2025-04-14 11:53:58', 33),
(34, 'Santuário da Palanca Negra Gigante', 'Área protegida dedicada à conservação da Palanca Negra Gigante, espécie endêmica e símbolo nacional de Angola. Destino para ecoturismo responsável.', 'palanca_negra.jpg', 2, 0, '2025-04-14 11:53:58', 34),
(35, 'Barra do Cuanza', 'Local onde o rio Cuanza encontra o Oceano Atlântico, formando um ecossistema único. Popular para pesca esportiva e passeios de barco.', 'barra_cuanza.jpeg', 1, 0, '2025-04-14 11:53:58', 35),
(36, 'Serra da Neve', 'Formação montanhosa com clima único e paisagens dramáticas na província do Namibe. Destino para trilhas e fotografia de natureza.', 'serra_neve.jpeg', 3, 0, '2025-04-14 11:53:58', 36),
(37, 'Baía das Pipas', 'Enseada natural com águas calmas e cristalinas, ideal para natação e mergulho. Um refúgio tranquilo na costa do Namibe.', 'baia_pipas.jpeg', 1, 0, '2025-04-14 11:53:58', 37);

-- --------------------------------------------------------

--
-- Estrutura da tabela `imagens`
--

CREATE TABLE `imagens` (
  `id_imagem` int(11) NOT NULL,
  `id_destino` int(11) NOT NULL,
  `caminho_imagem` varchar(255) NOT NULL,
  `ordem` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `localizacoes`
--

CREATE TABLE `localizacoes` (
  `id_localizacao` int(11) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `nome_local` varchar(100) NOT NULL DEFAULT 'Local sem nome',
  `id_provincia` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `localizacoes`
--

INSERT INTO `localizacoes` (`id_localizacao`, `latitude`, `longitude`, `nome_local`, `id_provincia`) VALUES
(1, -15.07740000, 13.39640000, 'Fenda da Tundavala', 10),
(2, -4.77530000, 12.80640000, 'Floresta do Maiombe', 4),
(3, -7.23240000, 16.02650000, 'Grutas do Nzenzo', 17),
(4, -10.03630000, 20.83910000, 'Lagoa Carumbo', 12),
(5, -14.92380000, 13.50060000, 'Morro do Môco', 9),
(6, -9.14990000, 15.83440000, 'Quedas de Kalandula', 14),
(7, -10.97680000, 22.90340000, 'Quedas do Rio Chiumbe', 13),
(8, -8.81470000, 13.23020000, 'Ilha de Luanda', 11),
(9, -9.69150000, 13.76880000, 'Parque Nacional da Quiçama', 11),
(10, -12.16350000, 15.83660000, 'Parque Nacional do Bicuar', 16),
(11, -16.76950000, 12.36950000, 'Parque Nacional do Iona', 16),
(12, -9.36240000, 14.93190000, 'Miradouro da Lua', 11),
(13, -12.34650000, 13.54690000, 'Baía Azul', 2),
(14, -12.14920000, 15.16250000, 'Baía dos Tigres', 16),
(15, -12.78860000, 15.76060000, 'Serra da Leba', 10),
(16, -8.23590000, 13.37160000, 'Mussulo', 11),
(17, -5.78240000, 12.13890000, 'Parque de Mangroves', 4),
(18, -8.77180000, 13.22830000, 'Fortaleza de São Miguel', 11),
(19, -12.36920000, 13.53700000, 'Praia Morena', 2),
(20, -12.59190000, 13.40380000, 'Deserto do Namibe', 16),
(21, -7.47170000, 20.39750000, 'Cachoeiras dos Ditchas', 12),
(22, -12.58240000, 13.40810000, 'Welwitschia Mirabilis', 16),
(23, -9.42210000, 13.62940000, 'Rio Kwanza', 7),
(24, -13.02770000, 15.57600000, 'Tchivinguiro', 10),
(25, -8.34860000, 13.33510000, 'Futungo de Belas', 11),
(26, -10.67990000, 13.94560000, 'Rio Longa', 7),
(27, -6.16750000, 12.35350000, 'Lagoa Massabi', 4),
(28, -9.43160000, 16.29690000, 'Pedras Negras de Pungo Andongo', 14),
(29, -14.76480000, 17.81240000, 'Serra da Chela', 10),
(30, -10.98460000, 17.54890000, 'Vale do Cuito', 3),
(31, -12.84370000, 15.24520000, 'Parque Natural Regional da Chimalavera', 2),
(32, -13.53080000, 15.06380000, 'Estação Zootécnica da Humpata', 10),
(33, -6.27240000, 14.83690000, 'Quedas do Zaza', 18),
(34, -11.85530000, 17.63170000, 'Palanca Negra Gigante', 15),
(35, -10.71370000, 13.41250000, 'Barra do Cuanza', 11),
(36, -14.76480000, 13.36820000, 'Serra da Neve', 16),
(37, -15.18090000, 12.15240000, 'Baía das Pipas', 16),
(38, 12.12344300, 12.12399600, 'manuel', 5),
(39, 12.12344300, 12.12399600, 'manuel', 5),
(40, 12.12344300, 12.12399600, 'manuel', 5);

-- --------------------------------------------------------

--
-- Estrutura da tabela `provincias`
--

CREATE TABLE `provincias` (
  `id_provincia` int(11) NOT NULL,
  `nome_provincia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `provincias`
--

INSERT INTO `provincias` (`id_provincia`, `nome_provincia`) VALUES
(1, 'Bengo'),
(2, 'Benguela'),
(3, 'Bié'),
(4, 'Cabinda'),
(5, 'Cuando Cubango'),
(6, 'Cuanza Norte'),
(7, 'Cuanza Sul'),
(8, 'Cunene'),
(9, 'Huambo'),
(10, 'Huíla'),
(11, 'Luanda'),
(12, 'Lunda Norte'),
(13, 'Lunda Sul'),
(14, 'Malanje'),
(15, 'Moxico'),
(16, 'Namibe'),
(17, 'Uíge'),
(18, 'Zaire');

-- --------------------------------------------------------

--
-- Estrutura da tabela `provincias_temp`
--

CREATE TABLE `provincias_temp` (
  `id_provincia` int(11) NOT NULL,
  `nome_provincia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `provincias_temp`
--

INSERT INTO `provincias_temp` (`id_provincia`, `nome_provincia`) VALUES
(1, 'Bengo'),
(2, 'Benguela'),
(3, 'Bié'),
(4, 'Cabinda'),
(5, 'Cuando Cubango'),
(6, 'Cuanza Norte'),
(7, 'Cuanza Sul'),
(8, 'Cunene'),
(9, 'Huambo'),
(10, 'Huíla'),
(11, 'Luanda'),
(12, 'Lunda Norte'),
(13, 'Lunda Sul'),
(14, 'Malanje'),
(15, 'Moxico'),
(16, 'Namibe'),
(17, 'Uíge'),
(18, 'Zaire');

-- --------------------------------------------------------

--
-- Estrutura da tabela `solicitacoes_acesso`
--

CREATE TABLE `solicitacoes_acesso` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `novo_nivel` enum('admin','comum') NOT NULL,
  `status` enum('pendente','aprovado','rejeitado') DEFAULT 'pendente',
  `data_solicitacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `aprovado_por` int(11) DEFAULT NULL,
  `data_aprovacao` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `tipo_usuario` enum('admin','comum','super_admin') NOT NULL DEFAULT 'comum'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nome`, `email`, `senha`, `tipo_usuario`) VALUES
(1, 'Manuel Afonso', 'manuelafonso@gmail.com', '$2y$10$HVJEN1KPbsLxB6Lexgl2Q.5UYWtBLiHiSha1vT0mjEY8g7dNrwb6m', 'super_admin'),
(2, 'Maria Sofia', 'mariasofia@gmail.com', '$2y$10$DCNLXiDe5.RvwwwhfBz9Wew9JKfqC7pbAWszLcCihJMF4TWuqC5Qi', 'comum'),
(27, 'Manuel Sampaio', 'manuelsampaio@gmail.com', '$2y$10$goU2P9ufMSE8Cpxh06oTPuW8ZA6dxlp9qPHiOj25oxX79W0tx0MgO', 'admin'),
(29, 'Manuel Sampaio1', 'manuelsampaio1@gmail.com', '$2y$10$2kemFxK8VTi/Fo9o5IpQ0.Vep/S1cbgAuEOxRHdAQlcQbdzqteFtu', 'comum');

-- --------------------------------------------------------

--
-- Estrutura stand-in para vista `view_destinos_completos`
-- (Veja abaixo para a view atual)
--
CREATE TABLE `view_destinos_completos` (
`id` int(11)
,`nome_destino` varchar(255)
,`descricao` text
,`nome_local` varchar(100)
,`nome_provincia` varchar(50)
,`latitude` decimal(10,8)
,`longitude` decimal(11,8)
,`imagem` varchar(255)
,`id_categoria` int(11)
,`is_maravilha` tinyint(1)
,`data_cadastro` timestamp
);

-- --------------------------------------------------------

--
-- Estrutura para vista `view_destinos_completos`
--
DROP TABLE IF EXISTS `view_destinos_completos`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_destinos_completos`  AS SELECT `dt`.`id` AS `id`, `dt`.`nome_destino` AS `nome_destino`, `dt`.`descricao` AS `descricao`, `l`.`nome_local` AS `nome_local`, `p`.`nome_provincia` AS `nome_provincia`, `l`.`latitude` AS `latitude`, `l`.`longitude` AS `longitude`, `dt`.`imagem` AS `imagem`, `dt`.`id_categoria` AS `id_categoria`, `dt`.`is_maravilha` AS `is_maravilha`, `dt`.`data_cadastro` AS `data_cadastro` FROM ((`destinos_turisticos` `dt` join `localizacoes` `l` on(`dt`.`id_localizacao` = `l`.`id_localizacao`)) join `provincias` `p` on(`l`.`id_provincia` = `p`.`id_provincia`)) ;

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id_avaliacao`),
  ADD UNIQUE KEY `unique_avaliacao` (`id_usuario`,`id_destino`),
  ADD UNIQUE KEY `unique_avaliacoes` (`id_usuario`,`id_destino`),
  ADD KEY `fk_avaliacao_destino` (`id_destino`);

--
-- Índices para tabela `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Índices para tabela `destinos_turisticos`
--
ALTER TABLE `destinos_turisticos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_destino_localizacao` (`id_localizacao`);

--
-- Índices para tabela `imagens`
--
ALTER TABLE `imagens`
  ADD PRIMARY KEY (`id_imagem`),
  ADD KEY `fk_imagens_destino` (`id_destino`);

--
-- Índices para tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  ADD PRIMARY KEY (`id_localizacao`),
  ADD KEY `fk_provincia` (`id_provincia`);

--
-- Índices para tabela `provincias`
--
ALTER TABLE `provincias`
  ADD PRIMARY KEY (`id_provincia`);

--
-- Índices para tabela `provincias_temp`
--
ALTER TABLE `provincias_temp`
  ADD PRIMARY KEY (`id_provincia`);

--
-- Índices para tabela `solicitacoes_acesso`
--
ALTER TABLE `solicitacoes_acesso`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_solicitacoes_usuario` (`id_usuario`),
  ADD KEY `fk_solicitacoes_aprovador` (`aprovado_por`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id_avaliacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de tabela `destinos_turisticos`
--
ALTER TABLE `destinos_turisticos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de tabela `imagens`
--
ALTER TABLE `imagens`
  MODIFY `id_imagem` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  MODIFY `id_localizacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de tabela `provincias`
--
ALTER TABLE `provincias`
  MODIFY `id_provincia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `provincias_temp`
--
ALTER TABLE `provincias_temp`
  MODIFY `id_provincia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de tabela `solicitacoes_acesso`
--
ALTER TABLE `solicitacoes_acesso`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `avaliacoes_ibfk_2` FOREIGN KEY (`id_destino`) REFERENCES `destinos_turisticos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avaliacao_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos_turisticos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avaliacao_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_avaliacoes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `destinos_turisticos`
--
ALTER TABLE `destinos_turisticos`
  ADD CONSTRAINT `destinos_turisticos_ibfk_1` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id_localizacao`),
  ADD CONSTRAINT `fk_destino_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id_localizacao`);

--
-- Limitadores para a tabela `imagens`
--
ALTER TABLE `imagens`
  ADD CONSTRAINT `fk_imagens_destino` FOREIGN KEY (`id_destino`) REFERENCES `destinos_turisticos` (`id`) ON DELETE CASCADE;

--
-- Limitadores para a tabela `localizacoes`
--
ALTER TABLE `localizacoes`
  ADD CONSTRAINT `fk_provincia` FOREIGN KEY (`id_provincia`) REFERENCES `provincias` (`id_provincia`);

--
-- Limitadores para a tabela `solicitacoes_acesso`
--
ALTER TABLE `solicitacoes_acesso`
  ADD CONSTRAINT `fk_solicitacoes_aprovador` FOREIGN KEY (`aprovado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_solicitacoes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitacoes_acesso_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `solicitacoes_acesso_ibfk_2` FOREIGN KEY (`aprovado_por`) REFERENCES `usuarios` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
