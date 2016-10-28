-- ChaFes event initialization
CREATE TABLE `event_player_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,		-- Player user ID
	total_points INTEGER NOT NULL,				-- Total event points
	live_difficulty_id_list TEXT,				-- ChaFes live_difficulty_id list or NULL if not started
	exp_count INTEGER,							-- Total accumulated EXP
	gold_count INTEGER,							-- Total accumulated G
	event_point_count INTEGER,					-- Total accumulated event points
	prize_bronze INTEGER,						-- Bronze prize count
	prize_silver INTEGER,						-- Silver prize count
	prize_gold INTEGER							-- Gold prize count
);
CREATE TABLE `event_song_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,			-- Player user ID
	high_score INTEGER NOT NULL,					-- Highest score
	live_difficulty_id INTEGER NOT NULL,			-- Live which has highest score
	deck_snapshot_id INTEGER NOT NULL,				-- Snapshot of used deck for this song
	score INTEGER NOT NULL,							-- Live score
	perfect INTEGER NOT NULL,						-- Perfect count
	great INTEGER NOT NULL,							-- Great count
	good INTEGER NOT NULL,							-- Good count
	bad INTEGER NOT NULL,							-- Bad count
	miss INTEGER NOT NULL							-- Miss count
);
