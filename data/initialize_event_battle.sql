-- Scorematch event initialization
CREATE TABLE `event_player_ranking_$event_id` (
	user_id INTEGER NOT NULL PRIMARY KEY,		-- Player user ID
	total_points INTEGER NOT NULL,				-- Total event points
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
