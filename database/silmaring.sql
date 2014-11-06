-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Nov 06, 2014 at 04:36 PM
-- Server version: 5.5.25a
-- PHP Version: 5.4.4

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `silmaring`
--
CREATE DATABASE IF NOT EXISTS `silmaring` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `silmaring`;

-- --------------------------------------------------------

--
-- Table structure for table `course`
--

CREATE TABLE IF NOT EXISTS `course` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `teacher_id` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `published` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_to_course` (`teacher_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `course`
--

INSERT INTO `course` (`id`, `teacher_id`, `name`, `description`, `price`, `published`) VALUES
(6, 2, 'Matemaatika Eksamikursus', 'Kursus on mÃµeldud matemaatika eksamit sooritavatele Ãµpilastele', 10.00, 1),
(7, 2, 'Eesti Keele Eksamikursus', 'See on eesti keele eksami kursus', 10.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `lesson`
--

CREATE TABLE IF NOT EXISTS `lesson` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `subject_id` int(10) DEFAULT NULL,
  `name` int(255) DEFAULT NULL,
  `content` text,
  PRIMARY KEY (`id`),
  KEY `fk_subject_to_lesson` (`subject_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'teacher'),
(3, 'student');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE IF NOT EXISTS `subject` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `course_id` int(10) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  KEY `fk_course_to_subject` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `role_id` int(10) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_role_to_user` (`role_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `role_id`, `firstname`, `lastname`, `email`, `password`) VALUES
(1, 1, 'Admin', 'Silmaring', 'admin@silmaring.ee', '2e33a9b0b06aa0a01ede70995674ee23'),
(2, 2, 'Ãµpetaja1', 'Silmaring', 'opetaja1@silmaring.ee', 'ec3054b21b076be9afc66aa734cc5c0b'),
(3, 2, 'Ãµpetaja2', 'Silmaring', 'opetaja2@silmaring.ee', 'b1f50e3d85af0d2989f7b0632cae47a8'),
(4, 3, 'Ãµpilane1', 'Silmaring', 'opilane1@silmaring.ee', '94ea3e70b8ef4bd170fd5ca5bf3f9803'),
(5, 3, 'Ãµpilane2', 'Silmaring', 'opilane2@silmaring.ee', '927cc87205b95f46323130bc31573178');

-- --------------------------------------------------------

--
-- Table structure for table `user_course`
--

CREATE TABLE IF NOT EXISTS `user_course` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `user_id` int(10) NOT NULL,
  `course_id` int(10) NOT NULL,
  `status` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_user_to_user_course` (`user_id`),
  KEY `fk_course_to_user_course` (`course_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `user_course`
--

INSERT INTO `user_course` (`id`, `user_id`, `course_id`, `status`) VALUES
(4, 4, 6, NULL),
(5, 4, 7, 1);

ALTER TABLE `course`
  ADD CONSTRAINT `fk_user_to_course` FOREIGN KEY (`teacher_id`) REFERENCES `user` (`id`);

ALTER TABLE `lesson`
  ADD CONSTRAINT `fk_subject_to_lesson` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE;

ALTER TABLE `subject`
  ADD CONSTRAINT `fk_course_to_subject` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE;

ALTER TABLE `user`
  ADD CONSTRAINT `fk_role_to_user` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

ALTER TABLE `user_course`
  ADD CONSTRAINT `fk_user_to_user_course` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE;

ALTER TABLE `user_course`
  ADD CONSTRAINT `fk_course_to_user_course` FOREIGN KEY (`course_id`) REFERENCES `course` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
