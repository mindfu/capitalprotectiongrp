-- phpMyAdmin SQL Dump
-- version 3.4.11.1deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 29, 2013 at 12:15 AM
-- Server version: 5.5.29
-- PHP Version: 5.4.6-1ubuntu1.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `capitalp`
--

-- --------------------------------------------------------

--
-- Table structure for table `leverage_ratios`
--

CREATE TABLE IF NOT EXISTS `leverage_ratios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `debt` decimal(3,2) NOT NULL,
  `equity` decimal(3,2) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `portfolios`
--

CREATE TABLE IF NOT EXISTS `portfolios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `initial_equity` decimal(19,4) NOT NULL,
  `leverage` enum('Yes','No') NOT NULL,
  `leverage_bank_cost` int(11) NOT NULL,
  `additional_leverage_bank_cost` int(11) NOT NULL,
  `bank_leverage` enum('3 Month LIBOR','6 Month LIBOR') NOT NULL,
  `show_results_net_fund` enum('Yes','No') NOT NULL,
  `fund_of_funds_management_fee` decimal(3,2) NOT NULL,
  `fund_of_funds_performance_fee` decimal(3,2) NOT NULL,
  `leverage_ratio_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `leverage_ratio_id` (`leverage_ratio_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
