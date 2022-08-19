ALTER TABLE userParticipations RENAME COLUMN placement TO position;
ALTER TABLE userParticipations RENAME COLUMN placed_at TO updated_at;
ALTER TABLE userParticipations ADD COLUMN score INT AFTER updated_at;
ALTER TABLE userParticipations ADD COLUMN finished tinyint AFTER updated_at;

ALTER TABLE ppLeagues RENAME COLUMN users_count TO user_count;
ALTER TABLE ppLeagues ADD COLUMN round_count INT AFTER user_count;

ALTER TABLE leagues RENAME COLUMN league_level TO country_level;
ALTER TABLE leagues RENAME COLUMN league_name TO name;
ALTER TABLE leagues RENAME COLUMN league_tag TO tag;
ALTER TABLE leagues ADD COLUMN ls_suffix varchar(255) AFTER id;


ALTER TABLE ppCupGroups ADD COLUMN round_count INT AFTER rounds;
ALTER TABLE ppCupGroups ADD COLUMN user_count INT AFTER round_count;

ALTER TABLE teams DROP COLUMN last_five;
ALTER TABLE teams ADD COLUMN ls_id INT AFTER id;
ALTER TABLE teams RENAME COLUMN team_name TO name;
ALTER TABLE teams DROP COLUMN team_tag;

ALTER TABLE matches DROP COLUMN league_name;
ALTER TABLE matches DROP COLUMN external_api_name;
ALTER TABLE matches DROP COLUMN hometeam_name;
ALTER TABLE matches DROP COLUMN awayteam_name;
-- makes the column below NULLABLE, dropping the 222 thingy
ALTER TABLE matches MODIFY COLUMN score_home int;
ALTER TABLE matches MODIFY COLUMN score_away int;
ALTER TABLE matches RENAME COLUMN external_api_matchid TO ls_id;
ALTER TABLE matches RENAME COLUMN hometeam_id TO home_id;
ALTER TABLE matches RENAME COLUMN awayteam_id TO away_id;
ALTER TABLE matches MODIFY id INT AUTO_INCREMENT;


-- makes the column below NULLABLE, dropping the 222 thingy
ALTER TABLE guesses MODIFY COLUMN guess_home int;
ALTER TABLE guesses MODIFY COLUMN guess_away int;





