ALTER TABLE userParticipations RENAME COLUMN placement TO position;
ALTER TABLE userParticipations RENAME COLUMN placed_at TO updated_at;
ALTER TABLE userParticipations ADD COLUMN score INT AFTER updated_at;
ALTER TABLE userParticipations ADD COLUMN finished tinyint AFTER updated_at;


ALTER TABLE ppLeagues RENAME COLUMN users_count TO user_count;
ALTER TABLE ppLeagues ADD COLUMN round_count INT AFTER user_count;


ALTER TABLE leagues RENAME COLUMN league_level TO country_level;
ALTER TABLE leagues RENAME COLUMN league_name TO name;
ALTER TABLE leagues RENAME COLUMN league_tag TO tag;
