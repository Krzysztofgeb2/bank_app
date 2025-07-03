-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Lip 03, 2025 at 02:07 PM
-- Wersja serwera: 10.4.28-MariaDB
-- Wersja PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bank_app`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_number` varchar(26) NOT NULL,
  `balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `user_id`, `account_number`, `balance`, `created_at`) VALUES
(1, 1, '12345678901234567890123456', 1555.00, '2025-06-11 12:15:26'),
(3, 3, '32345678901234567890123456', 3000.00, '2025-06-11 12:15:26'),
(4, 8, '83319402707117744627263400', 135.00, '2025-06-12 17:22:48'),
(6, 10, '60259271245591500669446566', 256.00, '2025-06-13 07:15:38'),
(7, 11, '84296496296703732374814639', 9755.00, '2025-06-13 08:28:21'),
(9, 13, '20448414543860603772587607', 111.00, '2025-06-13 12:35:08');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(3, 'admin'),
(2, 'employee'),
(1, 'user');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `transfers`
--

CREATE TABLE `transfers` (
  `id` int(11) NOT NULL,
  `from_account_id` int(11) NOT NULL,
  `to_account_id` int(11) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `transfer_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transfers`
--

INSERT INTO `transfers` (`id`, `from_account_id`, `to_account_id`, `amount`, `transfer_date`) VALUES
(4, 7, 6, 123.00, '2025-06-13 08:58:42'),
(5, 7, 4, 123.00, '2025-06-13 08:59:33'),
(7, 9, 4, 12.00, '2025-06-13 12:38:02');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_verified` tinyint(1) DEFAULT 0,
  `verification_token` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role_id`, `created_at`, `is_verified`, `verification_token`) VALUES
(1, 'jan_kowalski', 'jan@example.com', '$2y$10$abcdefghijklmnopqrstuvxyz1234567890abcd', 1, '2025-06-11 12:15:26', 1, NULL),
(3, 'marek_pracownik', 'marek@example.com', '$2y$10$employeehashxyzemployeehashxyzabcd', 2, '2025-06-11 12:15:26', 1, NULL),
(8, 'kryzys', 'krzys@op', '$2y$10$JbukVGnc6D6YOsgbApwAder8WqpcgbX/ydY.FFwV.aZWujTNknVQ2', 2, '2025-06-12 17:22:48', 1, NULL),
(10, 'Adam', 'aaa@adam', '$2y$10$WDb7ktMJYPPXjNigTXL/peAFeaWLiA6qsdr4iIWQYVXynEqRnI3fS', 3, '2025-06-13 07:15:38', 1, '032d079b23785f12092060235c5e14a2e14d6fef248337983c806df21153bb35'),
(11, '12345', '12345@test.pl', '$2y$10$nojvc0nb8BhcOvnqCgPVZ.CXE77Ajm9hv4r67yp5wrqCs5C3Dp1Gi', 1, '2025-06-13 08:28:21', 1, 'd5448ecd2668ef9e6a1b4581a2eb1f702ff5f559396782a3dfcbd42af3f68ee9'),
(13, 'Krzysztof', 'wielki.swiat@onet.eu', '$2y$10$t5.qWq0yXLrkcNz2RPcx7O/1/QLTZ2kpHMXKTHcTxn4ygphdQHmgG', 1, '2025-06-13 12:35:08', 1, NULL);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `accounts_ibfk_1` (`user_id`);

--
-- Indeksy dla tabeli `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indeksy dla tabeli `transfers`
--
ALTER TABLE `transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transfers_ibfk_1` (`from_account_id`),
  ADD KEY `transfers_ibfk_2` (`to_account_id`);

--
-- Indeksy dla tabeli `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transfers`
--
ALTER TABLE `transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfers`
--
ALTER TABLE `transfers`
  ADD CONSTRAINT `transfers_ibfk_1` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfers_ibfk_2` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
