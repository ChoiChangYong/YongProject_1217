-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- 생성 시간: 17-12-16 06:45
-- 서버 버전: 5.7.20
-- PHP 버전: 7.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 데이터베이스: `ourdb`
--

-- --------------------------------------------------------

--
-- 테이블 구조 `ourdb`
--

CREATE TABLE `ourdb` (
  `Num` int(10) DEFAULT NULL,
  `Id` varchar(30) DEFAULT NULL,
  `Password` varchar(30) DEFAULT NULL,
  `Email` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `ourdb`
--

INSERT INTO `ourdb` (`Num`, `Id`, `Password`, `Email`) VALUES
(1, 'test', 'test', 'test@test');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
