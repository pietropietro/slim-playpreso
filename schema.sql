----USERS
--list of changes
-- ip_address --> country
-- preso_money --> points

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(20) NOT NULL,
  `points` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;



---LEAGUES
--changes
--league_id --> id
--removed logo_url

CREATE TABLE `leagues` (
  `id` int(11) NOT NULL,
  `is_cup` tinyint(1) NOT NULL DEFAULT '0',
  `country` varchar(20) NOT NULL,
  `area` varchar(100) NOT NULL DEFAULT 'europe',
  `league_name` varchar(50) NOT NULL,
  `league_tag` varchar(3) NOT NULL,
  `area_level` int(11) NOT NULL,
  `league_level` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `leagues`
--
ALTER TABLE `leagues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `league_name` (`league_name`);

  --
-- AUTO_INCREMENT for table `leagues`
--
ALTER TABLE `leagues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;








CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `team_name` varchar(30) DEFAULT NULL,
  `team_tag` varchar(3) DEFAULT NULL,
  `last_five` varchar(5) DEFAULT NULL,
  `country` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);









--MATCHES
--changes:
-- removed constraint home/awayteam_id NOT NULL
-- added FK league_id
-- added field for external apis recognition

CREATE TABLE `matches` (
    `id` int(11) NOT NULL,
    `external_api_matchid` int(11) NOT NULL,
    `external_api_name` varchar(30) NOT NULL,
    `league_id` int(11) NOT NULL,
    `league_name` varchar(50) NOT NULL, 
    `hometeam_id` int(11) ,
    `awayteam_id` int(11) ,
    `hometeam_name` varchar(30) NOT NULL,
    `awayteam_name` varchar(30) NOT NULL,
    `score_home` int(11) NOT NULL,
    `score_away` int(11) NOT NULL,
    `round` int(11) NOT NULL,
    `date_start` datetime NOT NULL,
    `rescheduled` tinyint(1) NOT NULL DEFAULT '0',
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `verified_at` timestamp NULL DEFAULT NULL,
    `rescheduled_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `league_id` (`league_id`);


--
-- Constraints for table `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `leagues` (`id`),












---PPLEAGUETYPES
CREATE TABLE `ppLeagueTypes` (
  `id` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `level` int(11) NOT NULL,
  `rounds` int(11) NOT NULL,
  `cost` int(11) NOT NULL,
  `image_url` varchar(200) DEFAULT NULL,
  `red` int(11) NOT NULL DEFAULT '0',
  `green` int(11) NOT NULL DEFAULT '0',
  `blue` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppLeagueTypes`
--
ALTER TABLE `ppLeagueTypes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `ppLeagueTypes`
--
ALTER TABLE `ppLeagueTypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;









--PPLEAGUES
--noOfUsers ---> users_count
-- removed redundant started + finished BOOL

CREATE TABLE `ppLeagues` (
  `id` int(11) NOT NULL,
  `ppLeagueType_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `users_count` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppLeagues`
--
ALTER TABLE `ppLeagues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppLeagueType_id` (`ppLeagueType_id`);

  --
-- AUTO_INCREMENT for table `ppLeagues`
--
ALTER TABLE `ppLeagues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Constraints for table `ppLeagues`
--
ALTER TABLE `ppLeagues`
  ADD CONSTRAINT `ppLeagues_ibfk_1` FOREIGN KEY (`ppLeagueType_id`) REFERENCES `ppLeagueTypes` (`id`);













--PPCUPTYPES

CREATE TABLE `ppCupTypes` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `simultaneous` tinyint(4) DEFAULT NULL,
  `loopy` tinyint(4) NOT NULL DEFAULT '1',
  `participants` int(11) DEFAULT NULL,
  `image_url` varchar(200) DEFAULT NULL,
  `red` int(11) DEFAULT NULL,
  `green` int(11) DEFAULT NULL,
  `blue` int(11) DEFAULT NULL,
  `cost` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppCupTypes`
--
ALTER TABLE `ppCupTypes`
  ADD PRIMARY KEY (`id`);

  --
-- AUTO_INCREMENT for table `ppCupTypes`
--
ALTER TABLE `ppCupTypes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;











--PPCUPS
--removed redundant bool values started/finished
CREATE TABLE `ppCups` (
  `id` int(11) NOT NULL,
  `ppCupType_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppCups`
--
ALTER TABLE `ppCups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppCupType_id` (`ppCupType_id`);

  --
-- AUTO_INCREMENT for table `ppCups`
--
ALTER TABLE `ppCups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Constraints for table `ppCups`
--
ALTER TABLE `ppCups`
  ADD CONSTRAINT `presoCups_ibfk_1` FOREIGN KEY (`ppCupType_id`) REFERENCES `ppCupTypes` (`id`);











---PPCUPGROUPS
-- size --> participants for naming consistency
-- removed redundant started/finished bool values
CREATE TABLE `ppCupGroups` (
  `id` int(11) NOT NULL,
  `participants` int(11) DEFAULT NULL,
  `ppCup_id` int(11) DEFAULT NULL,
  `level` int(11) NOT NULL,
  `rounds` int(11) NOT NULL DEFAULT '1',
  `groupTag` varchar(100) DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `finished_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppCupGroups`
--
ALTER TABLE `ppCupGroups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppCup_id` (`ppCup_id`);

--
-- AUTO_INCREMENT for table `ppCupGroups`
--
ALTER TABLE `ppCupGroups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Constraints for table `ppCupGroups`
--
ALTER TABLE `ppCupGroups`
  ADD CONSTRAINT `ppCupGroups_ibfk_1` FOREIGN KEY (`ppCup_id`) REFERENCES `ppCups` (`id`);









---ppRounds
--changes 
--tablename ppRounds ---> ppRounds
CREATE TABLE `ppRounds` (
  `id` int(11) NOT NULL,
  `ppLeague_id` int(11) DEFAULT NULL,
  `ppCupGroup_id` int(11) DEFAULT NULL,
  `round` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppRounds`
--
ALTER TABLE `ppRounds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppLeague_id` (`ppLeague_id`),
  ADD KEY `ppRounds_ibfk_2` (`ppCupGroup_id`);

  --
-- AUTO_INCREMENT for table `ppRounds`
--
ALTER TABLE `ppRounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Constraints for table `ppRounds`
--
ALTER TABLE `ppRounds`
  ADD CONSTRAINT `ppRounds_ibfk_1` FOREIGN KEY (`ppLeague_id`) REFERENCES `ppLeagues` (`id`),
  ADD CONSTRAINT `ppRounds_ibfk_2` FOREIGN KEY (`ppCupGroup_id`) REFERENCES `ppCupGroups` (`id`);
  










CREATE TABLE `ppRoundMatches` (
  `id` int(11) NOT NULL,
  `ppRound_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `ppRoundMatches`
--
ALTER TABLE `ppRoundMatches`
    ADD PRIMARY KEY (`id`),
    ADD KEY `ppRound_id` (`ppRound_id`),
    ADD KEY `match_id` (`match_id`);

ALTER TABLE `ppRoundMatches`
    ADD UNIQUE KEY `uq_match_round` (`match_id`,`ppRound_id`);


--
-- AUTO_INCREMENT for table `ppRoundMatches`
--
ALTER TABLE `ppRoundMatches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `ppRoundMatches`
--
ALTER TABLE `ppRoundMatches`
  ADD CONSTRAINT `ppRoundMatches_ibfk_1` FOREIGN KEY (`ppRound_id`) REFERENCES `ppRounds` (`id`),
  ADD CONSTRAINT `ppRoundMatches_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`);








---GUESSES
--changes:
--preso_score --> score
--guess_id --> id
-- removed ip_address

CREATE TABLE `guesses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `match_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `verified_at` timestamp NULL DEFAULT NULL,
  `guessed_at` timestamp NULL DEFAULT NULL,
  `guess_home` int(11) NOT NULL,
  `guess_away` int(11) NOT NULL,
  `score` int(11) DEFAULT NULL,
  `UNOX2` tinyint(1) NOT NULL DEFAULT '0',
  `GGNG` tinyint(1) NOT NULL DEFAULT '0',
  `UO25` tinyint(1) NOT NULL DEFAULT '0',
  `PRESO` tinyint(1) NOT NULL DEFAULT '0',
  `ppRoundMatch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `guesses`
--
ALTER TABLE `guesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_user_mbm` (`user_id`,`ppRoundMatch_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `match_id` (`match_id`),
  ADD KEY `ppRoundMatch_id` (`ppRoundMatch_id`);

--
-- AUTO_INCREMENT for table `guesses`
--
ALTER TABLE `guesses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for table `guesses`
--
ALTER TABLE `guesses`
  ADD CONSTRAINT `guesses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `guesses_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`),
  ADD CONSTRAINT `guesses_ibfk_3` FOREIGN KEY (`ppRoundMatch_id`) REFERENCES `ppRoundMatches` (`id`);











--- from usersInPpLeagues && usersInPpCupGroups ---> userParticipations
CREATE TABLE `userParticipations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ppLeague_id` int(11),  
  `ppCup_id` int(11),
  `ppCupGroup_id` int(11),
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


--
-- Indexes for table `userParticipations`
--
ALTER TABLE `userParticipations`
    ADD PRIMARY KEY (`id`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `ppCup_id` (`ppCup_id`),
    ADD KEY `ppCupGroup_id` (`ppCupGroup_id`),
    ADD KEY `ppLeague_id` (`ppLeague_id`),
    ADD UNIQUE KEY `uq_usersPL` (`user_id`,`ppLeague_id`),
    ADD UNIQUE KEY `uq_usersPCG` (`user_id`,`ppCupGroup_id`);

--
-- AUTO_INCREMENT for table `userParticipations`
--
ALTER TABLE `userParticipations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Constraints for table `userParticipations`
--
ALTER TABLE `userParticipations`
    ADD CONSTRAINT `userParticipations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
    ADD CONSTRAINT `userParticipations_ibfk_2` FOREIGN KEY (`ppCup_id`) REFERENCES `ppCups` (`id`),
    ADD CONSTRAINT `userParticipations_ibfk_3` FOREIGN KEY (`ppCupGroup_id`) REFERENCES `ppCupGroups` (`id`),
    ADD CONSTRAINT `userParticipations_ibfk_4` FOREIGN KEY (`ppLeague_id`) REFERENCES `ppLeagues` (`id`);
    
    --TODO - syntax not working
    ---ADD CONSTRAINT 'cup_or_league' CHECK ((`ppCup_id` IS NULL + `ppLeague_id` IS NOT NULL) OR (`ppCup_id` IS NOT NULL + `ppLeague_id` IS NULL));
    ---




--USERPLACEMENTS
-- added reference to PCtype - PLtype
--this table contains data which shows users best placement in various competitons.
--will be used as source of truth for user trophies
CREATE TABLE `userPlacements` (
    `id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `ppLeagueType_id` int(11) DEFAULT NULL,
    `ppLeague_id` int(11) DEFAULT NULL,
    `ppCupType_id` int(11) DEFAULT NULL,
    `ppCup_id` int(11) DEFAULT NULL,
    `placement` int(11) NOT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `userPlacements`
--
ALTER TABLE `userPlacements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_userPLTaccess` (`user_id`,`ppLeagueType_id`),
  ADD UNIQUE KEY `uq_userPCTaccess` (`user_id`,`ppCupType_id`),
  ADD KEY `ppLeagueType_id` (`ppLeagueType_id`),
  ADD KEY `ppLeague_id` (`ppLeague_id`),
  ADD KEY `ppCupType_id` (`ppCupType_id`),
  ADD KEY `ppCup_id` (`ppCup_id`);
  

--
-- AUTO_INCREMENT for table `userPlacements`
--
ALTER TABLE `userPlacements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

  --
-- Constraints for table `userPlacements`
--
ALTER TABLE `userPlacements`
  ADD CONSTRAINT `userPlacements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `userPlacements_ibfk_2` FOREIGN KEY (`ppLeagueType_id`) REFERENCES `ppLeagueTypes` (`id`),
  ADD CONSTRAINT `userPlacements_ibfk_3` FOREIGN KEY (`ppLeague_id`) REFERENCES `ppLeagues` (`id`),
  ADD CONSTRAINT `userPlacements_ibfk_4` FOREIGN KEY (`ppCupType_id`) REFERENCES `ppCupTypes` (`id`),
  ADD CONSTRAINT `userPlacements_ibfk_5` FOREIGN KEY (`ppCup_id`) REFERENCES `ppCups` (`id`);

COMMIT;