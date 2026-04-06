-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 02. Apr 2026 um 15:50
-- Server-Version: 10.4.32-MariaDB
-- PHP-Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `chord_app`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `language` varchar(5) NOT NULL DEFAULT 'en',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `language`, `created_at`, `user_id`) VALUES
(1, 'admin', '$2y$10$SLv0ZGuYA0mnCai1S21cz.SdANQmp4eYKoHZT9IAZpwTm3X4eO2e.', 'en', '2026-03-01 19:06:22', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `chord_versions`
--

CREATE TABLE `chord_versions` (
  `id` int(10) UNSIGNED NOT NULL,
  `song_id` int(10) UNSIGNED NOT NULL,
  `version_label` varchar(100) NOT NULL,
  `notes` varchar(255) DEFAULT '',
  `content` mediumtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `audio_url` varchar(500) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `chord_versions`
--

INSERT INTO `chord_versions` (`id`, `song_id`, `version_label`, `notes`, `content`, `created_at`, `updated_at`, `audio_url`) VALUES
(1, 1, 'KEY A', '', 'One of the Caribbean\'s most elegant and exclusive enclaves, Lyford Cay, located on the western edge of New Providence Island in The Bahamas, has served as a refuge for global leaders, international luminaries, and royal families for more than half a century.\r\n\r\nIn the heart of the community is the Lyford Cay Club, a meticulously appointed private retreat that lavishes attention and five-star amenities on club members and their guests with an impeccable combination of world-class service and recreation options. On-site facilities include a state-of-the-art tennis and fitness center, an 18-hole Rees Jones-designed golf course, sailing, fishing, and water sports, indulgent spa services, and much more.', '2026-03-01 19:10:11', NULL, ''),
(2, 1, 'KEY B', '', 'One of the Caribbean\'s most elegant and exclusive enclaves, Lyford Cay, located on the western edge of New Providence Island in The Bahamas, has served as a refuge for global leaders, international luminaries, and royal families for more than half a century.\r\n\r\nIn the heart of the community is the Lyford Cay Club, a meticulously appointed private retreat that lavishes attention and five-star amenities on club members and their guests with an impeccable combination of world-class service and recreation options. On-site facilities include a state-of-the-art tennis and fitness center, an 18-hole Rees Jones-designed golf course, sailing, fishing, and water sports, indulgent spa services, and much more.\r\n\r\nOne of the Caribbean\'s most elegant and exclusive enclaves, Lyford Cay, located on the western edge of New Providence Island in The Bahamas, has served as a refuge for global leaders, international luminaries, and royal families for more than half a century.\r\n\r\nIn the heart of the community is the Lyford Cay Club, a meticulously appointed private retreat that lavishes attention and five-star amenities on club members and their guests with an impeccable combination of world-class service and recreation options. On-site facilities include a state-of-the-art tennis and fitness center, an 18-hole Rees Jones-designed golf course, sailing, fishing, and water sports, indulgent spa services, and much more.', '2026-03-01 19:15:12', NULL, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `password_resets`
--

CREATE TABLE `password_resets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 4, 'd1ad374692814750c79987c143c9f64a562540c53f61007338bcd3d706e9e2d5', '2026-03-02 23:14:04', '2026-03-02 22:44:24', '2026-03-02 21:44:04');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `songs`
--

CREATE TABLE `songs` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `artist` varchar(255) DEFAULT '',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `audio_url` varchar(500) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `songs`
--

INSERT INTO `songs` (`id`, `title`, `artist`, `created_at`, `updated_at`, `audio_url`) VALUES
(1, 'You Raise me Up', 'CMGI_NORD', '2026-03-01 19:09:30', '2026-03-01 20:47:30', '/nordchords/public/assets/audio/song_1772398050_b2c0877c.m4a'),
(2, 'Du bist Gott', '', '2026-04-02 13:33:15', NULL, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `language` varchar(5) NOT NULL DEFAULT 'en',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Daten für Tabelle `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `language`, `created_at`) VALUES
(1, 'Fru Ndi', 'carlsontantoh@gmail.com', '$2y$10$aNcKb2F92dDwsIm2q7Bv0.eY3YZMOMZD95/POA9vG8QYUAVvub2QS', 'fr', '2026-03-01 19:51:30'),
(2, 'elon', 'carlsontantoh2@gmail.com', '$2y$10$hv94Y2wZmklhnrSG1c7Wau3P0rxs2kTy3tG6pN2DKHb6I6Qp.ijI2', 'en', '2026-03-01 20:24:02'),
(3, 'carlson', 'carlsontantoh25@gmail.com', '$2y$10$jqZHxhQZuCig.lh4CTn9lufWum0ODjdQit6yS/BLOw2U3OmMtIQPm', 'en', '2026-03-01 20:32:13'),
(4, 'carl', 'carl@gmail.com', '$2y$10$YuJcukKR5mCERvuzWp8premg8NWowsv8.ikKFt57yzc.e.2g0Noia', 'de', '2026-03-01 20:48:46'),
(6, 'jiji', 'jiji@gmail.com', '$2y$10$UzOJ/RhKILxxznXQQWgPc.FtUCq/BgQt3JIin1PRRjezyiCS3LlVu', 'en', '2026-03-02 20:37:09');

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indizes für die Tabelle `chord_versions`
--
ALTER TABLE `chord_versions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_song_version_label` (`song_id`,`version_label`);

--
-- Indizes für die Tabelle `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_password_resets_user` (`user_id`),
  ADD KEY `idx_password_resets_expires` (`expires_at`);

--
-- Indizes für die Tabelle `songs`
--
ALTER TABLE `songs`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `chord_versions`
--
ALTER TABLE `chord_versions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT für Tabelle `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT für Tabelle `songs`
--
ALTER TABLE `songs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `chord_versions`
--
ALTER TABLE `chord_versions`
  ADD CONSTRAINT `fk_chord_versions_song` FOREIGN KEY (`song_id`) REFERENCES `songs` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_password_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
