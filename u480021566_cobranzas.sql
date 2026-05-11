-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-05-2026 a las 17:56:18
-- Versión del servidor: 11.8.6-MariaDB-log
-- Versión de PHP: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `u480021566_cobranzas`
--
CREATE DATABASE IF NOT EXISTS `u480021566_cobranzas` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `u480021566_cobranzas`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Base_Telefonos_Historico`
--

DROP TABLE IF EXISTS `Base_Telefonos_Historico`;
CREATE TABLE IF NOT EXISTS `Base_Telefonos_Historico` (
  `documento` char(8) NOT NULL,
  `telefono` char(9) NOT NULL,
  `origen` varchar(20) NOT NULL,
  `fecha_act` char(6) NOT NULL,
  PRIMARY KEY (`documento`,`telefono`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Cartera_1y2`
--

DROP TABLE IF EXISTS `Cartera_1y2`;
CREATE TABLE IF NOT EXISTS `Cartera_1y2` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `CARTERA` varchar(20) NOT NULL,
  `CUENTA` varchar(50) DEFAULT NULL,
  `DNI` varchar(15) NOT NULL,
  `OPERACION` varchar(50) NOT NULL,
  `TITULAR` varchar(150) NOT NULL,
  `ANIO_CASTIGO` varchar(4) DEFAULT NULL,
  `MONEDA` varchar(20) DEFAULT NULL,
  `ENTIDAD` varchar(100) DEFAULT NULL,
  `PRODUCTO` varchar(100) DEFAULT NULL,
  `COSECHA` varchar(50) DEFAULT NULL,
  `NRO_TARJETA` varchar(30) DEFAULT NULL,
  `DEPARTAMENTO` varchar(80) DEFAULT NULL,
  `FECHA_COMPRA` date DEFAULT NULL,
  `EDAD` int(11) DEFAULT NULL,
  `SEXO` varchar(10) DEFAULT NULL,
  `ESTADO_CIVIL` varchar(30) DEFAULT NULL,
  `CAPITAL` decimal(15,2) DEFAULT NULL,
  `INTERES` decimal(15,2) DEFAULT NULL,
  `DEUDA_TOTAL` decimal(15,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uni_dni_operacion` (`DNI`,`OPERACION`),
  KEY `idx_dni` (`DNI`),
  KEY `idx_operacion` (`OPERACION`),
  KEY `idx_cartera` (`CARTERA`),
  KEY `DNI` (`DNI`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Gestiones_1y2`
--

DROP TABLE IF EXISTS `Gestiones_1y2`;
CREATE TABLE IF NOT EXISTS `Gestiones_1y2` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `documento` varchar(15) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `value2` varchar(100) DEFAULT NULL,
  `value1` varchar(100) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `operacion` varchar(100) NOT NULL,
  `entidad` varchar(255) DEFAULT NULL,
  `cartera` varchar(255) DEFAULT NULL,
  `dateprocessed` timestamp NULL DEFAULT NULL,
  `fechaAgenda` datetime DEFAULT NULL,
  `callerid` varchar(100) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `pagar_por_cuota` double DEFAULT NULL,
  `nroCuotas` varchar(100) DEFAULT NULL,
  `fecha_promesa` datetime DEFAULT NULL,
  `campaign` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_operacion` (`operacion`),
  KEY `idx_cartera` (`cartera`),
  KEY `idx_dateproc` (`dateprocessed`),
  KEY `idx_fecha_promo` (`fecha_promesa`),
  KEY `idx_documento` (`documento`),
  KEY `documento` (`documento`),
  KEY `callerid` (`callerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Gestiones_APDAYC`
--

DROP TABLE IF EXISTS `Gestiones_APDAYC`;
CREATE TABLE IF NOT EXISTS `Gestiones_APDAYC` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `documento` varchar(100) DEFAULT NULL,
  `LIC_ID` varchar(100) DEFAULT NULL,
  `socio` varchar(150) DEFAULT NULL,
  `value2` varchar(100) DEFAULT NULL,
  `value1` varchar(100) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `fechaAgenda` datetime DEFAULT NULL,
  `dateprocessed` datetime DEFAULT NULL,
  `callerid` varchar(100) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `montoPromesa` decimal(15,2) DEFAULT NULL,
  `nroCuota` int(11) DEFAULT NULL,
  `fecha_promesa` datetime DEFAULT NULL,
  `campaign` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_documento` (`documento`),
  KEY `idx_dateprocessed` (`dateprocessed`),
  KEY `idx_campaign` (`campaign`),
  KEY `idx_lic_id` (`LIC_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Gestiones_Propia3`
--

DROP TABLE IF EXISTS `Gestiones_Propia3`;
CREATE TABLE IF NOT EXISTS `Gestiones_Propia3` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `documento` varchar(100) DEFAULT NULL,
  `nombre` varchar(200) DEFAULT NULL,
  `value2` varchar(100) DEFAULT NULL,
  `value1` varchar(100) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `operacion` varchar(100) NOT NULL,
  `ctl` varchar(255) DEFAULT NULL,
  `dateprocessed` timestamp NULL DEFAULT NULL,
  `fechaAgenda` datetime DEFAULT NULL,
  `callerid` varchar(100) DEFAULT NULL,
  `comment` varchar(500) DEFAULT NULL,
  `pagar_por_cuota` double DEFAULT NULL,
  `nroCuotas` varchar(100) DEFAULT NULL,
  `fecha_promesa` datetime DEFAULT NULL,
  `campaign` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_operacion` (`operacion`),
  KEY `idx_dateproc` (`dateprocessed`),
  KEY `idx_fecha_promo` (`fecha_promesa`),
  KEY `idx_documento` (`documento`),
  KEY `documento` (`documento`),
  KEY `callerid` (`callerid`),
  KEY `idx_dup_gp3` (`documento`,`dateprocessed`,`comment`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Gestiones_Propia4`
--

DROP TABLE IF EXISTS `Gestiones_Propia4`;
CREATE TABLE IF NOT EXISTS `Gestiones_Propia4` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `documento` varchar(150) DEFAULT NULL,
  `cliente` varchar(200) DEFAULT NULL,
  `value2` varchar(300) DEFAULT NULL,
  `value1` varchar(300) DEFAULT NULL,
  `fullname` varchar(100) DEFAULT NULL,
  `operacion` varchar(50) DEFAULT NULL,
  `entidad` varchar(150) DEFAULT NULL,
  `dateprocessed` timestamp NULL DEFAULT NULL,
  `fechaAgenda` datetime DEFAULT NULL,
  `callerid` varchar(200) DEFAULT NULL,
  `comment` varchar(2000) DEFAULT NULL,
  `importe_financiamiento` double DEFAULT NULL,
  `nroCuotas` varchar(150) DEFAULT NULL,
  `fecha_promesa` datetime DEFAULT NULL,
  `campaign` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_operacion` (`operacion`),
  KEY `idx_dateproc` (`dateprocessed`),
  KEY `idx_fecha_promo` (`fecha_promesa`),
  KEY `idx_documento` (`documento`),
  KEY `documento` (`documento`),
  KEY `callerid` (`callerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Llamadas_Abandonadas`
--

DROP TABLE IF EXISTS `Llamadas_Abandonadas`;
CREATE TABLE IF NOT EXISTS `Llamadas_Abandonadas` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `fecha_evento` datetime NOT NULL,
  `event` varchar(32) DEFAULT NULL,
  `callidnum` varchar(32) DEFAULT NULL,
  `guid` varchar(45) DEFAULT NULL,
  `queue` varchar(128) DEFAULT NULL,
  `enterdate` datetime DEFAULT NULL,
  `posabandon` varchar(255) DEFAULT NULL,
  `posoriginal` varchar(255) DEFAULT NULL,
  `callerid` varchar(255) DEFAULT NULL,
  `timewait` varchar(255) DEFAULT NULL,
  `documento` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_fecha` (`fecha_evento`),
  KEY `idx_documento` (`documento`),
  KEY `idx_queue` (`queue`),
  KEY `documento` (`documento`),
  KEY `callerid` (`callerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Llamadas_AMD`
--

DROP TABLE IF EXISTS `Llamadas_AMD`;
CREATE TABLE IF NOT EXISTS `Llamadas_AMD` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calldate` datetime NOT NULL,
  `campaign` varchar(45) DEFAULT NULL,
  `dst` varchar(80) DEFAULT NULL,
  `disposition` varchar(45) DEFAULT NULL,
  `userfield` varchar(255) DEFAULT NULL,
  `contact` bigint(20) UNSIGNED DEFAULT NULL,
  `dialbase` varchar(200) DEFAULT NULL,
  `doc` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_calldate` (`calldate`),
  KEY `idx_doc` (`doc`(50)),
  KEY `idx_campaign` (`campaign`),
  KEY `idx_disp` (`disposition`),
  KEY `doc` (`doc`(768)),
  KEY `dst` (`dst`),
  KEY `idx_contact` (`contact`),
  KEY `idx_userfield` (`userfield`),
  KEY `idx_dialbase` (`dialbase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Llamadas_IVR`
--

DROP TABLE IF EXISTS `Llamadas_IVR`;
CREATE TABLE IF NOT EXISTS `Llamadas_IVR` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `calldate` datetime NOT NULL,
  `campaign` varchar(45) DEFAULT NULL,
  `dst` varchar(80) DEFAULT NULL,
  `disposition` varchar(45) DEFAULT NULL,
  `userfield` varchar(255) DEFAULT NULL,
  `contact` bigint(20) UNSIGNED DEFAULT NULL,
  `dialbase` varchar(200) DEFAULT NULL,
  `doc` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_calldate` (`calldate`),
  KEY `idx_doc` (`doc`(50)),
  KEY `idx_campaign` (`campaign`),
  KEY `idx_disp` (`disposition`),
  KEY `idx_dst` (`dst`),
  KEY `idx_contact` (`contact`),
  KEY `idx_userfield` (`userfield`),
  KEY `idx_dialbase` (`dialbase`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Pagos_1y2`
--

DROP TABLE IF EXISTS `Pagos_1y2`;
CREATE TABLE IF NOT EXISTS `Pagos_1y2` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `SISTEMA` tinyint(3) UNSIGNED NOT NULL,
  `DNI` varchar(15) NOT NULL,
  `OPERACION` varchar(50) NOT NULL,
  `MONEDA` varchar(20) DEFAULT NULL,
  `FECHA` date NOT NULL,
  `MONTO` decimal(15,2) NOT NULL,
  `GESTOR` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_dni` (`DNI`),
  KEY `idx_operacion` (`OPERACION`),
  KEY `idx_fecha` (`FECHA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Pagos_3`
--

DROP TABLE IF EXISTS `Pagos_3`;
CREATE TABLE IF NOT EXISTS `Pagos_3` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `SISTEMA` tinyint(3) UNSIGNED NOT NULL,
  `DNI` varchar(15) NOT NULL,
  `OPERACION` varchar(50) NOT NULL,
  `MONEDA` varchar(20) DEFAULT NULL,
  `FECHA` date NOT NULL,
  `MONTO` decimal(15,2) NOT NULL,
  `GESTOR` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_p3_dni` (`DNI`),
  KEY `idx_p3_operacion` (`OPERACION`),
  KEY `idx_p3_fecha` (`FECHA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Pagos_4`
--

DROP TABLE IF EXISTS `Pagos_4`;
CREATE TABLE IF NOT EXISTS `Pagos_4` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `SISTEMA` tinyint(3) UNSIGNED NOT NULL,
  `DNI` varchar(15) NOT NULL,
  `OPERACION` varchar(50) NOT NULL,
  `MONEDA` varchar(20) DEFAULT NULL,
  `FECHA` date NOT NULL,
  `MONTO` decimal(15,2) NOT NULL,
  `GESTOR` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_p4_dni` (`DNI`),
  KEY `idx_p4_operacion` (`OPERACION`),
  KEY `idx_p4_fecha` (`FECHA`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `Tel_Historico_Staging`
--

DROP TABLE IF EXISTS `Tel_Historico_Staging`;
CREATE TABLE IF NOT EXISTS `Tel_Historico_Staging` (
  `documento` char(8) DEFAULT NULL,
  `telefono` char(9) DEFAULT NULL,
  `origen` varchar(20) DEFAULT NULL,
  `fecha_act` char(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipificaciones`
--

DROP TABLE IF EXISTS `tipificaciones`;
CREATE TABLE IF NOT EXISTS `tipificaciones` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tipificacion` varchar(200) NOT NULL,
  `resultado` varchar(100) NOT NULL,
  `mc` varchar(50) NOT NULL,
  `peso` int(11) NOT NULL,
  `origen` varchar(25) DEFAULT NULL,
  `orden` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tipificacion` (`tipificacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
