-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 12-Nov-2017 às 00:13
-- Versão do servidor: 10.1.28-MariaDB
-- PHP Version: 7.1.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `voucher`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `recipient`
--

CREATE TABLE `recipient` (
  `name` varchar(50) NOT NULL,
  `email` varchar(80) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `special_offer`
--

CREATE TABLE `special_offer` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `discount` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `voucher_codes`
--

CREATE TABLE `voucher_codes` (
  `code` varchar(10) NOT NULL,
  `recipient_email` varchar(80) NOT NULL,
  `special_offer_id` int(11) NOT NULL,
  `expiration_date` date NOT NULL,
  `usaged` tinyint(1) DEFAULT NULL COMMENT '0 means false, 1 means true',
  `date_usage` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `recipient`
--
ALTER TABLE `recipient`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `special_offer`
--
ALTER TABLE `special_offer`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `voucher_codes`
--
ALTER TABLE `voucher_codes`
  ADD PRIMARY KEY (`code`),
  ADD KEY `special_offer_id` (`special_offer_id`),
  ADD KEY `recipient_email` (`recipient_email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `special_offer`
--
ALTER TABLE `special_offer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Constraints for dumped tables
--

--
-- Limitadores para a tabela `voucher_codes`
--
ALTER TABLE `voucher_codes`
  ADD CONSTRAINT `recipient_email` FOREIGN KEY (`recipient_email`) REFERENCES `recipient` (`email`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `special_offer_id` FOREIGN KEY (`special_offer_id`) REFERENCES `special_offer` (`id`) ON DELETE NO ACTION ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
