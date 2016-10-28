-- MedFes event initialization
CREATE TABLE `event_player_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,		-- Player user ID
	total_points INTEGER NOT NULL,				-- Total event points
	last_selected_data INTEGER NOT NULL,		-- Last selected MedFes. Value of this is "((difficulty - 1) << 2) | live_count"
	last_live_difficulty_id TEXT				-- live_diffculty_id lists (comma separated) or NULL if MedFes is not queued
);
CREATE TABLE `event_song_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,		-- Player user ID
	high_score INTEGER NOT NULL,				-- Highest score
	live_difficulty_id INTEGER NOT NULL,		-- Live which has highest score
	live_difficulty_id2 INTEGER,				-- Same as above (second phase live)
	live_difficulty_id3 INTEGER,				-- Same as above (third phase live)
	deck_snapshot_id INTEGER NOT NULL,			-- Snapshot of used deck for this song
	score INTEGER NOT NULL,						-- Live score
	perfect INTEGER NOT NULL,					-- Perfect count
	great INTEGER NOT NULL,						-- Great count
	good INTEGER NOT NULL,						-- Good count
	bad INTEGER NOT NULL,						-- Bad count
	miss INTEGER NOT NULL						-- Miss count
);
