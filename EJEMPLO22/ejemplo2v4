-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 12, 2025 at 02:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ejemplo2`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumnos`
--

CREATE TABLE `alumnos` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `Nombre_D_Alumno` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumnos`
--

INSERT INTO `alumnos` (`Numero_D_Cuenta`, `Nombre_D_Alumno`) VALUES
(513309, 'ANGELES PEREZ  ROGELIO SEBASTIAN'),
(513329, 'VARGAS HERNANDEZ  ROMINA'),
(513340, 'GUERRERO RIOS  BRAYAN ALEXANDER'),
(513449, 'GODINEZ HERNANDEZ  JUAN PABLO'),
(513508, 'RAMIREZ SANCHEZ  AHMED'),
(513727, 'FLORES CORONA  MARIA JOSE'),
(514075, 'CRUZ CRUZ  SEBASTIAN'),
(514090, 'RAMIREZ TELLEZ  LIZZETH VALENTINA'),
(514183, 'MEDINA MARMOLEJO  DIEGO'),
(514340, 'VILLANUEVA CORTEZ  AZUL SELINA'),
(515149, 'ALVAREZ CRUZ  LENNY DANAHE'),
(515177, 'GONZALEZ BARRERA  KARLA ALEXA'),
(515807, 'RESENDIZ RAMIREZ  FERNANDA'),
(516199, 'TORRES GRESS  LUIS JONATHAN'),
(516440, 'ZU?IGA FALCON  HECTOR ANDRES'),
(516769, 'RIVERA RAMIREZ  JESUS EDUARDO'),
(517102, 'RODRIGUEZ JIMENEZ  DANIELA'),
(517255, 'ANTONIO LORENZO  SHARON JIMENA'),
(517350, 'NERIA PEREZ  MICHELLE ABIGAIL'),
(517570, 'JUAREZ GARCIA  YOANA ARACELY'),
(518011, 'LOPEZ DE JESUS  ANGEL JARED'),
(518103, 'TOBON SALAS  FERNANDA ALEJANDRA'),
(518391, 'HERREJON ALVAREZ  DANIELA NOFRITH'),
(518392, 'LOPEZ AGUILAR VALERIA LIZETH'),
(518393, 'HERNANDEZ CABRERA  MIGUEL ANGEL'),
(518953, 'GUTIERREZ OLVERA  ILSE'),
(519014, 'ESTRADA RAMIREZ  CYNTHIA ZOE'),
(521795, 'DURAN CHAVEZ  CAMILA ZANDILY'),
(522198, 'MEZA MELCHOR  DAVID'),
(523243, 'GONZALEZ SANCHEZ  KIMBERLY'),
(523517, 'OLGUIN GARCIA  STEVEN GILBERTO'),
(523532, 'ESCOBAR ALVARADO  DULCE ABIGAIL');

-- --------------------------------------------------------

--
-- Table structure for table `analisishistorico`
--

CREATE TABLE `analisishistorico` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `analisishistorico`
--

INSERT INTO `analisishistorico` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513329, 0, 9.72, 10.00, 10.00, 9.75),
(513340, 0, 9.44, 0.00, 10.00, 9.00),
(513508, 0, 7.72, 0.00, 0.00, 6.95),
(513727, 0, 9.44, 10.00, 10.00, 9.50),
(514075, 0, 9.44, 10.00, 10.00, 9.50),
(514340, 0, 9.44, 10.00, 10.00, 9.50),
(515149, 0, 9.44, 10.00, 10.00, 9.50),
(515177, 0, 9.44, 10.00, 10.00, 9.50),
(516199, 0, 7.72, 10.00, 10.00, 7.95),
(516440, 0, 7.72, 10.00, 10.00, 7.95),
(516769, 0, 9.72, 0.00, 10.00, 9.25),
(517102, 0, 9.17, 0.00, 0.00, 8.25),
(517255, 0, 9.44, 10.00, 10.00, 9.50),
(517350, 0, 10.00, 10.00, 10.00, 10.00),
(517570, 0, 10.00, 10.00, 10.00, 10.00),
(518011, 0, 8.72, 10.00, 10.00, 8.85),
(518103, 0, 9.17, 10.00, 10.00, 9.25),
(518391, 0, 10.00, 10.00, 10.00, 10.00),
(518392, 0, 9.72, 10.00, 10.00, 9.75),
(518393, 0, 9.17, 10.00, 10.00, 9.25),
(518953, 0, 9.44, 0.00, 10.00, 9.00),
(519014, 0, 9.44, 10.00, 10.00, 9.50),
(521795, 0, 9.44, 10.00, 10.00, 9.50),
(522198, 0, 9.44, 10.00, 10.00, 9.50),
(523243, 0, 9.44, 10.00, 10.00, 9.50),
(523517, 0, 7.72, 10.00, 10.00, 7.95);

-- --------------------------------------------------------

--
-- Table structure for table `diversidadterrestre`
--

CREATE TABLE `diversidadterrestre` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diversidadterrestre`
--

INSERT INTO `diversidadterrestre` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513309, 0, 4.46, 10.00, 10.00, 5.01),
(513329, 0, 8.12, 10.00, 10.00, 8.31),
(513340, 0, 7.21, 0.00, 10.00, 6.99),
(513449, 0, 7.61, 10.00, 10.00, 7.85),
(513508, 0, 7.79, 10.00, 10.00, 8.01),
(513727, 0, 8.89, 10.00, 10.00, 9.00),
(514075, 0, 7.87, 10.00, 10.00, 8.08),
(514090, 0, 8.67, 10.00, 10.00, 8.80),
(514183, 0, 6.96, 10.00, 10.00, 7.26),
(514340, 0, 7.73, 10.00, 10.00, 7.96),
(515149, 0, 8.28, 10.00, 10.00, 8.45),
(516199, 0, 7.16, 10.00, 10.00, 7.44),
(516440, 0, 6.39, 10.00, 10.00, 6.75),
(516769, 0, 7.29, 10.00, 10.00, 7.56),
(517102, 0, 8.56, 10.00, 10.00, 8.70),
(517255, 0, 7.27, 10.00, 10.00, 7.54),
(517350, 0, 8.14, 10.00, 10.00, 8.33),
(517570, 0, 8.72, 10.00, 10.00, 8.85),
(518011, 0, 6.80, 10.00, 10.00, 7.12),
(518103, 0, 6.66, 10.00, 10.00, 6.99),
(518391, 0, 7.00, 10.00, 10.00, 7.30),
(518392, 0, 8.23, 10.00, 10.00, 8.41),
(518393, 0, 7.90, 10.00, 10.00, 8.11),
(518953, 0, 8.50, 10.00, 10.00, 8.65),
(519014, 0, 8.07, 9.52, 10.00, 8.24),
(521795, 0, 8.53, 10.00, 10.00, 8.68),
(522198, 0, 8.60, 10.00, 10.00, 8.74),
(523243, 0, 7.73, 10.00, 10.00, 7.96),
(523517, 0, 3.50, 10.00, 10.00, 4.15),
(523532, 0, 8.37, 10.00, 10.00, 8.53);

-- --------------------------------------------------------

--
-- Table structure for table `expresionartisticas`
--

CREATE TABLE `expresionartisticas` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `expresionartisticas`
--

INSERT INTO `expresionartisticas` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513309, 0, 8.71, 10.00, 10.00, 8.84),
(513329, 0, 9.81, 10.00, 10.00, 9.83),
(513340, 0, 8.43, 10.00, 10.00, 8.59),
(513449, 0, 9.12, 10.00, 10.00, 9.21),
(513508, 0, 7.56, 0.00, 0.00, 6.80),
(513727, 0, 9.82, 10.00, 10.00, 9.84),
(514075, 0, 9.51, 10.00, 10.00, 9.56),
(514090, 0, 9.32, 10.00, 10.00, 9.39),
(514183, 0, 9.39, 10.00, 10.00, 9.45),
(514340, 0, 8.58, 10.00, 10.00, 8.72),
(515149, 0, 9.88, 10.00, 10.00, 9.89),
(515177, 0, 9.69, 10.00, 10.00, 9.72),
(515807, 0, 9.12, 10.00, 10.00, 9.21),
(516199, 0, 9.00, 10.00, 10.00, 9.10),
(516440, 0, 8.81, 10.00, 10.00, 8.93),
(516769, 0, 9.38, 10.00, 10.00, 9.44),
(517102, 0, 9.47, 10.00, 10.00, 9.52),
(517255, 0, 8.89, 10.00, 10.00, 9.00),
(517350, 0, 9.58, 10.00, 10.00, 9.62),
(517570, 0, 9.69, 10.00, 10.00, 9.72),
(518011, 0, 6.94, 10.00, 10.00, 7.25),
(518103, 0, 8.40, 10.00, 10.00, 8.56),
(518391, 0, 9.32, 10.00, 10.00, 9.39),
(518392, 0, 9.51, 10.00, 10.00, 9.56),
(518393, 0, 7.25, 0.00, 10.00, 7.03),
(518953, 0, 10.00, 10.00, 10.00, 10.00),
(519014, 0, 9.69, 10.00, 10.00, 9.72),
(521795, 0, 9.81, 10.00, 10.00, 9.83),
(522198, 0, 7.84, 0.00, 0.00, 7.06),
(523243, 0, 9.31, 10.00, 10.00, 9.38),
(523517, 0, 8.77, 10.00, 10.00, 8.89),
(523532, 0, 9.32, 0.00, 10.00, 8.89);

-- --------------------------------------------------------

--
-- Table structure for table `grupos`
--

CREATE TABLE `grupos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `idioma3`
--

CREATE TABLE `idioma3` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `idioma3`
--

INSERT INTO `idioma3` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513329, 0, 7.68, 10.00, 10.00, 7.80),
(513340, 0, 7.63, 10.00, 10.00, 7.75),
(513449, 0, 6.24, 10.00, 10.00, 6.43),
(513508, 0, 6.41, 10.00, 10.00, 6.59),
(513727, 0, 8.95, 10.00, 10.00, 9.00),
(514075, 0, 7.39, 10.00, 10.00, 7.52),
(514183, 0, 7.25, 10.00, 10.00, 7.39),
(514340, 0, 8.14, 10.00, 10.00, 8.23),
(515149, 0, 5.83, 10.00, 10.00, 6.04),
(515177, 0, 6.89, 10.00, 10.00, 7.05),
(515807, 0, 7.79, 10.00, 10.00, 7.90),
(516199, 0, 7.45, 10.00, 10.00, 7.58),
(517102, 0, 6.65, 10.00, 10.00, 6.82),
(517255, 0, 9.24, 10.00, 10.00, 9.28),
(517350, 0, 7.87, 10.00, 10.00, 7.98),
(517570, 0, 8.23, 10.00, 10.00, 8.32),
(518011, 0, 5.92, 10.00, 10.00, 6.12),
(518103, 0, 5.55, 10.00, 10.00, 5.77),
(518391, 0, 7.01, 10.00, 10.00, 7.16),
(518953, 0, 7.45, 10.00, 10.00, 7.58),
(519014, 0, 8.14, 10.00, 10.00, 8.23),
(521795, 0, 8.01, 10.00, 10.00, 8.11),
(523243, 0, 5.87, 10.00, 10.00, 6.08),
(523532, 0, 9.59, 10.00, 10.00, 9.61);

-- --------------------------------------------------------

--
-- Table structure for table `modelosmatematicos`
--

CREATE TABLE `modelosmatematicos` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `modelosmatematicos`
--

INSERT INTO `modelosmatematicos` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513329, 0, 5.35, 10.00, 10.00, 5.81),
(513340, 0, 5.25, 10.00, 10.00, 5.73),
(513449, 0, 5.88, 10.00, 10.00, 6.29),
(513727, 0, 10.00, 10.00, 10.00, 10.00),
(514075, 0, 4.37, 10.00, 10.00, 4.93),
(514090, 0, 6.57, 10.00, 10.00, 6.91),
(515177, 0, 9.48, 10.00, 10.00, 9.53),
(515807, 0, 7.20, 10.00, 10.00, 7.48),
(516199, 0, 7.18, 10.00, 10.00, 7.46),
(517102, 0, 10.00, 10.00, 10.00, 10.00),
(517255, 0, 6.03, 10.00, 10.00, 6.43),
(517350, 0, 7.28, 10.00, 10.00, 7.55),
(517570, 0, 8.80, 10.00, 0.00, 8.42),
(518103, 0, 4.73, 10.00, 10.00, 5.26),
(518391, 0, 6.72, 10.00, 10.00, 7.05),
(518392, 0, 6.73, 10.00, 10.00, 7.06),
(518953, 0, 6.92, 10.00, 10.00, 7.23),
(521795, 0, 9.02, 10.00, 10.00, 9.12),
(522198, 0, 5.85, 0.00, 0.00, 5.26),
(523243, 0, 7.20, 10.00, 10.00, 7.48),
(523517, 0, 6.72, 10.00, 10.00, 7.05);

-- --------------------------------------------------------

--
-- Table structure for table `producciontexto`
--

CREATE TABLE `producciontexto` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `producciontexto`
--

INSERT INTO `producciontexto` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513309, 0, 8.32, 10.00, 10.00, 8.49),
(513329, 0, 8.58, 10.00, 10.00, 8.72),
(513340, 0, 7.73, 10.00, 10.00, 7.96),
(513449, 0, 8.58, 10.00, 10.00, 8.72),
(513508, 0, 8.42, 0.00, 0.00, 7.58),
(513727, 0, 10.00, 10.00, 10.00, 10.00),
(514075, 0, 7.87, 10.00, 10.00, 8.08),
(514090, 0, 8.74, 10.00, 10.00, 8.87),
(514183, 0, 8.16, 10.00, 10.00, 8.34),
(514340, 0, 9.33, 10.00, 10.00, 9.40),
(515149, 0, 8.00, 10.00, 10.00, 8.20),
(515177, 0, 8.58, 10.00, 10.00, 8.72),
(515807, 0, 8.44, 10.00, 10.00, 8.60),
(516199, 0, 8.74, 10.00, 10.00, 8.87),
(516440, 0, 7.56, 10.00, 10.00, 7.80),
(516769, 0, 7.92, 10.00, 10.00, 8.13),
(517102, 0, 8.71, 10.00, 10.00, 8.84),
(517255, 0, 9.21, 10.00, 10.00, 9.29),
(517350, 0, 8.44, 10.00, 10.00, 8.60),
(517570, 0, 9.60, 10.00, 10.00, 9.64),
(518103, 0, 8.69, 10.00, 10.00, 8.82),
(518391, 0, 9.20, 10.00, 10.00, 9.28),
(518392, 0, 8.71, 10.00, 10.00, 8.84),
(518393, 0, 7.17, 10.00, 10.00, 7.45),
(518953, 0, 9.33, 10.00, 10.00, 9.40),
(519014, 0, 8.71, 10.00, 10.00, 8.84),
(521795, 0, 8.81, 10.00, 10.00, 8.93),
(522198, 0, 7.82, 10.00, 10.00, 8.04),
(523243, 0, 9.47, 10.00, 10.00, 9.52),
(523517, 0, 8.56, 10.00, 10.00, 8.70),
(523532, 0, 8.84, 10.00, 10.00, 8.96);

-- --------------------------------------------------------

--
-- Table structure for table `solucioneslogicas`
--

CREATE TABLE `solucioneslogicas` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solucioneslogicas`
--

INSERT INTO `solucioneslogicas` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513309, 0, 7.52, 10.00, 10.00, 7.77),
(513329, 0, 8.87, 10.00, 10.00, 8.98),
(513340, 0, 7.00, 10.00, 10.00, 7.30),
(513449, 0, 8.44, 10.00, 10.00, 8.60),
(513508, 0, 6.85, 10.00, 10.00, 7.17),
(513727, 0, 8.84, 10.00, 10.00, 8.96),
(514075, 0, 8.34, 10.00, 10.00, 8.51),
(514090, 0, 8.06, 10.00, 10.00, 8.25),
(514183, 0, 8.62, 10.00, 10.00, 8.76),
(514340, 0, 8.35, 10.00, 10.00, 8.52),
(515149, 0, 8.16, 10.00, 10.00, 8.34),
(515177, 0, 8.98, 10.00, 10.00, 9.08),
(515807, 0, 8.76, 10.00, 10.00, 8.88),
(516199, 0, 8.33, 10.00, 10.00, 8.50),
(516440, 0, 6.96, 10.00, 10.00, 7.26),
(516769, 0, 8.53, 10.00, 10.00, 8.68),
(517102, 0, 8.22, 10.00, 10.00, 8.40),
(517255, 0, 8.83, 10.00, 10.00, 8.95),
(517350, 0, 8.61, 10.00, 10.00, 8.75),
(517570, 0, 8.37, 10.00, 10.00, 8.53),
(518011, 0, 6.97, 10.00, 10.00, 7.27),
(518103, 0, 6.37, 10.00, 10.00, 6.73),
(518391, 0, 8.36, 10.00, 10.00, 8.52),
(518393, 0, 6.52, 10.00, 10.00, 6.87),
(518953, 0, 8.84, 10.00, 10.00, 8.96),
(519014, 0, 8.28, 10.00, 10.00, 8.45),
(521795, 0, 9.02, 10.00, 10.00, 9.12),
(522198, 0, 6.85, 10.00, 10.00, 7.17),
(523243, 0, 7.74, 10.00, 10.00, 7.97),
(523517, 0, 7.83, 10.00, 10.00, 8.05),
(523532, 0, 8.52, 10.00, 10.00, 8.67);

-- --------------------------------------------------------

--
-- Table structure for table `transformaciondmateria`
--

CREATE TABLE `transformaciondmateria` (
  `Numero_D_Cuenta` int(11) NOT NULL,
  `parcial` int(1) NOT NULL,
  `HeteroEvaluacion` decimal(5,2) DEFAULT NULL,
  `CoEvaluacion` decimal(5,2) DEFAULT NULL,
  `AutoEvaluacion` decimal(5,2) DEFAULT NULL,
  `promedio` decimal(4,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transformaciondmateria`
--

INSERT INTO `transformaciondmateria` (`Numero_D_Cuenta`, `parcial`, `HeteroEvaluacion`, `CoEvaluacion`, `AutoEvaluacion`, `promedio`) VALUES
(513309, 0, 6.80, 10.00, 10.00, 7.12),
(513329, 0, 8.22, 10.00, 10.00, 8.40),
(513340, 0, 7.62, 10.00, 10.00, 7.86),
(513449, 0, 8.51, 10.00, 10.00, 8.66),
(513508, 0, 7.58, 10.00, 10.00, 7.82),
(513727, 0, 8.84, 10.00, 10.00, 8.96),
(514075, 0, 6.42, 10.00, 10.00, 6.78),
(514090, 0, 8.42, 10.00, 10.00, 8.58),
(514183, 0, 8.16, 10.00, 10.00, 8.34),
(514340, 0, 7.60, 10.00, 10.00, 7.84),
(515149, 0, 7.38, 10.00, 10.00, 7.64),
(515177, 0, 8.69, 10.00, 10.00, 8.82),
(515807, 0, 7.16, 10.00, 10.00, 7.44),
(516199, 0, 8.40, 10.00, 10.00, 8.56),
(516769, 0, 7.56, 10.00, 10.00, 7.80),
(517102, 0, 8.53, 10.00, 10.00, 8.68),
(517255, 0, 7.53, 10.00, 10.00, 7.78),
(517350, 0, 8.36, 10.00, 10.00, 8.52),
(517570, 0, 7.60, 10.00, 10.00, 7.84),
(518103, 0, 7.24, 10.00, 0.00, 7.02),
(518391, 0, 7.42, 10.00, 10.00, 7.68),
(518392, 0, 8.00, 10.00, 10.00, 8.20),
(518393, 0, 6.56, 10.00, 10.00, 6.90),
(518953, 0, 7.51, 10.00, 10.00, 7.76),
(519014, 0, 6.78, 10.00, 10.00, 7.10),
(521795, 0, 7.11, 10.00, 10.00, 7.40),
(522198, 0, 7.36, 10.00, 10.00, 7.62),
(523243, 0, 7.29, 10.00, 10.00, 7.56),
(523517, 0, 6.71, 10.00, 10.00, 7.04),
(523532, 0, 7.82, 10.00, 10.00, 8.04);

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_alumno_materia`
-- (See below for the actual view)
--
CREATE TABLE `vw_alumno_materia` (
`Numero_D_Cuenta` int(11)
,`Nombre_D_Alumno` varchar(100)
,`materia` varchar(22)
,`HeteroEvaluacion` decimal(5,2)
,`CoEvaluacion` decimal(5,2)
,`AutoEvaluacion` decimal(5,2)
,`promedio` decimal(4,2)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_alumno_materia`
--
DROP TABLE IF EXISTS `vw_alumno_materia`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_alumno_materia`  AS SELECT `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`, `a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`, 'analisisHistorico' AS `materia`, `ah`.`HeteroEvaluacion` AS `HeteroEvaluacion`, `ah`.`CoEvaluacion` AS `CoEvaluacion`, `ah`.`AutoEvaluacion` AS `AutoEvaluacion`, `ah`.`promedio` AS `promedio` FROM (`analisishistorico` `ah` join `alumnos` `a` on(`ah`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`))union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'diversidadTerrestre' AS `materia`,`dt`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`dt`.`CoEvaluacion` AS `CoEvaluacion`,`dt`.`AutoEvaluacion` AS `AutoEvaluacion`,`dt`.`promedio` AS `promedio` from (`diversidadterrestre` `dt` join `alumnos` `a` on(`dt`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`)) union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'idioma3' AS `materia`,`i3`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`i3`.`CoEvaluacion` AS `CoEvaluacion`,`i3`.`AutoEvaluacion` AS `AutoEvaluacion`,`i3`.`promedio` AS `promedio` from (`idioma3` `i3` join `alumnos` `a` on(`i3`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`)) union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'modelosMatematicos' AS `materia`,`mm`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`mm`.`CoEvaluacion` AS `CoEvaluacion`,`mm`.`AutoEvaluacion` AS `AutoEvaluacion`,`mm`.`promedio` AS `promedio` from (`modelosmatematicos` `mm` join `alumnos` `a` on(`mm`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`)) union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'produccionTexto' AS `materia`,`pt`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`pt`.`CoEvaluacion` AS `CoEvaluacion`,`pt`.`AutoEvaluacion` AS `AutoEvaluacion`,`pt`.`promedio` AS `promedio` from (`producciontexto` `pt` join `alumnos` `a` on(`pt`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`)) union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'solucionesLogicas' AS `materia`,`sl`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`sl`.`CoEvaluacion` AS `CoEvaluacion`,`sl`.`AutoEvaluacion` AS `AutoEvaluacion`,`sl`.`promedio` AS `promedio` from (`solucioneslogicas` `sl` join `alumnos` `a` on(`sl`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`)) union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'transformacionDMateria' AS `materia`,`tm`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`tm`.`CoEvaluacion` AS `CoEvaluacion`,`tm`.`AutoEvaluacion` AS `AutoEvaluacion`,`tm`.`promedio` AS `promedio` from (`transformaciondmateria` `tm` join `alumnos` `a` on(`tm`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`)) union all select `a`.`Numero_D_Cuenta` AS `Numero_D_Cuenta`,`a`.`Nombre_D_Alumno` AS `Nombre_D_Alumno`,'expresionArtisticas' AS `materia`,`ea`.`HeteroEvaluacion` AS `HeteroEvaluacion`,`ea`.`CoEvaluacion` AS `CoEvaluacion`,`ea`.`AutoEvaluacion` AS `AutoEvaluacion`,`ea`.`promedio` AS `promedio` from (`expresionartisticas` `ea` join `alumnos` `a` on(`ea`.`Numero_D_Cuenta` = `a`.`Numero_D_Cuenta`))  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`Numero_D_Cuenta`);

--
-- Indexes for table `analisishistorico`
--
ALTER TABLE `analisishistorico`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `diversidadterrestre`
--
ALTER TABLE `diversidadterrestre`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `expresionartisticas`
--
ALTER TABLE `expresionartisticas`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `grupos`
--
ALTER TABLE `grupos`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `idioma3`
--
ALTER TABLE `idioma3`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `modelosmatematicos`
--
ALTER TABLE `modelosmatematicos`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `producciontexto`
--
ALTER TABLE `producciontexto`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `solucioneslogicas`
--
ALTER TABLE `solucioneslogicas`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- Indexes for table `transformaciondmateria`
--
ALTER TABLE `transformaciondmateria`
  ADD PRIMARY KEY (`Numero_D_Cuenta`,`parcial`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `grupos`
--
ALTER TABLE `grupos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analisishistorico`
--
ALTER TABLE `analisishistorico`
  ADD CONSTRAINT `fk_ah_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ahi_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `diversidadterrestre`
--
ALTER TABLE `diversidadterrestre`
  ADD CONSTRAINT `fk_dt_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_td_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_xd2_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_xd_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `expresionartisticas`
--
ALTER TABLE `expresionartisticas`
  ADD CONSTRAINT `fk_ea_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `idioma3`
--
ALTER TABLE `idioma3`
  ADD CONSTRAINT `fk_i3_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `modelosmatematicos`
--
ALTER TABLE `modelosmatematicos`
  ADD CONSTRAINT `fk_mm_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `producciontexto`
--
ALTER TABLE `producciontexto`
  ADD CONSTRAINT `fk_pt_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `solucioneslogicas`
--
ALTER TABLE `solucioneslogicas`
  ADD CONSTRAINT `fk_sl_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;

--
-- Constraints for table `transformaciondmateria`
--
ALTER TABLE `transformaciondmateria`
  ADD CONSTRAINT `fk_tm_alumno` FOREIGN KEY (`Numero_D_Cuenta`) REFERENCES `alumnos` (`Numero_D_Cuenta`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
