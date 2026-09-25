-- Seed data voor MBO Soccer
-- Wachtwoorden zijn gehasht met password_hash('wachtwoord', PASSWORD_BCRYPT)
-- Voorbeeld wachtwoorden:
-- admin@mbosoccer.nl   -> Admin123!
-- trainer@mbosoccer.nl -> Trainer123!
-- speler@mbosoccer.nl  -> Speler123!

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `birthdate`) VALUES
(1, 'Beheerder Club', 'admin@mbosoccer.nl', '$2y$10$wE99qFqfK9vH94W9J0nFLeL9rTq1n6x8mG6B4uU4qO9zYlqRmW79O', 'medewerker', '0612345678', '1985-04-12'),
(2, 'Dennis van Dijk (Trainer)', 'trainer@mbosoccer.nl', '$2y$10$fW3CjC2r29wP1L5Qe.R7t.9Ekn5K3oO5k5bL1UvR9Y7.sU8fL8Xz2', 'trainer', '0687654321', '1990-09-20'),
(3, 'Sem Jansen (Speler)', 'speler@mbosoccer.nl', '$2y$10$V6tC9F4vE.a6L/qW9H8Lp.jB0dK5m7qN5hZ8U9yL3Q2m1V4z7W6Xy', 'lid', '0623456789', '2004-03-15'),
(4, 'Liam de Boer', 'liam@mbosoccer.nl', '$2y$10$V6tC9F4vE.a6L/qW9H8Lp.jB0dK5m7qN5hZ8U9yL3Q2m1V4z7W6Xy', 'lid', '0634567890', '2003-07-22'),
(5, 'Milan Bakker', 'milan@mbosoccer.nl', '$2y$10$V6tC9F4vE.a6L/qW9H8Lp.jB0dK5m7qN5hZ8U9yL3Q2m1V4z7W6Xy', 'lid', '0645678901', '2004-11-05'),
(6, 'Noah Visser', 'noah@mbosoccer.nl', '$2y$10$V6tC9F4vE.a6L/qW9H8Lp.jB0dK5m7qN5hZ8U9yL3Q2m1V4z7W6Xy', 'lid', '0656789012', '2003-01-18');

-- Teams
INSERT INTO `teams` (`id`, `name`, `category`, `trainer_id`) VALUES
(1, 'MBO Soccer 1', 'Senioren A-Klasse', 2),
(2, 'MBO Soccer O19-1', 'Jeugd Onder 19', 2);

-- Teamleden koppelen
INSERT INTO `team_members` (`team_id`, `user_id`, `position`, `shirt_number`) VALUES
(1, 3, 'Aanvaller', 9),
(1, 4, 'Middenvelder', 8),
(1, 5, 'Verdediger', 4),
(1, 6, 'Keeper', 1),
(2, 3, 'Aanvaller', 10);

-- Wedstrijden
INSERT INTO `matches` (`id`, `team_id`, `opponent`, `match_date`, `location`, `field_number`, `status`, `notes`) VALUES
(1, 1, 'FC Tech United', DATE_ADD(NOW(), INTERVAL 3 DAY), 'Thuis', 'Hoofdveld 1', 'Gepland', 'Belangrijke derby, verzamelen om 13:00.'),
(2, 1, 'VV Dev Stars', DATE_ADD(NOW(), INTERVAL 10 DAY), 'Uit', 'Sportpark West', 'Gepland', 'Vervoer per spelersbus.'),
(3, 2, 'SC Code Academy', DATE_ADD(NOW(), INTERVAL 5 DAY), 'Thuis', 'Veld 2', 'Gepland', 'Onder 19 bekerwedstrijd.');

-- Trainingsschema
INSERT INTO `trainings` (`id`, `team_id`, `training_date`, `field_number`, `description`) VALUES
(1, 1, DATE_ADD(NOW(), INTERVAL 1 DAY), 'Veld 2', 'Conditie- en positiespel'),
(2, 1, DATE_ADD(NOW(), INTERVAL 4 DAY), 'Veld 3', 'Tactische bespreking en afronden op goal'),
(3, 2, DATE_ADD(NOW(), INTERVAL 2 DAY), 'Veld 1', 'Basistechniek en standaardsituaties');

-- Aanwezigheid
INSERT INTO `match_attendance` (`match_id`, `user_id`, `status`, `reason`) VALUES
(1, 3, 'aanwezig', 'Fit en klaar voor de aftrap'),
(1, 4, 'afwezig', 'Blessure aan de enkel'),
(1, 5, 'aanwezig', NULL);

-- Opstelling voor Match 1
INSERT INTO `lineups` (`match_id`, `user_id`, `position_on_field`, `is_starter`) VALUES
(1, 6, 'Doelman', 1),
(1, 5, 'Centrale Verdediger', 1),
(1, 3, 'Spits', 1);
