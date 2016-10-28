-- Token event initialization
CREATE TABLE `event_player_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,		-- Player user ID
	total_points INTEGER NOT NULL,				-- Total event points
	current_token INTEGER NOT NULL				-- Current token
);
CREATE TABLE `event_song_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,					-- Player user ID
	high_score INTEGER NOT NULL								-- Highest score
);
