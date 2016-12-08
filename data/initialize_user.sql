-- This file contain user initialization

CREATE TABLE `friend_$user_id` (
	from_user_id INTEGER NOT NULL PRIMARY KEY,			-- The friend (request) user ID
	is_approved INTEGER NOT NULL DEFAULT 0,				-- Is the friend request is approved?
)

CREATE TABLE `present_$user_id` (
	incentive_idx INTEGER PRIMARY KEY AUTO_INCREMENT,	-- The item position
	add_type INTEGER NOT NULL,							-- The item type ID
	item_id INTEGER,									-- The card internal ID (can be other ID) or NULL.
	amount INTEGER NOT NULL,							-- Amount of the item
	message TEXT NOT NULL,								-- Additional message like: "Event achievement reward"
	insert_date INTEGER NOT NULL,						-- When this item is received?
	expire INTEGER DEFAULT NULL,						-- Unix timestamp when the item expire or NULL for no expiration
	collected INTEGER DEFAULT NULL						-- Unix timestamp for when the item was collected or NULL for not collected
);

CREATE TABLE `achievement_$user_id` (
	achievement_id INTEGER NOT NULL PRIMARY KEY,	-- The achievement id
	start_time INTEGER NOT NULL,					-- Unix timestamp when this achievement added
	end_time INTEGER,								-- Unix timestamp when this achievement end
	new_flag INTEGER NOT NULL DEFAULT 1,			-- Is new?
	count INTEGER DEFAULT 0,						-- Internal counter.
	complete_flag INTEGER NOT NULL DEFAULT 0,		-- Is complete?
	reward TEXT NOT NULL							-- Reward in format: <add_type>:<amount>[:<item_id>], ...
);

CREATE TABLE `item_$user_id` (
	item_id INTEGER PRIMARY KEY,		-- The item ID
	amount INTEGER NOT NULL DEFAULT 0	-- The item amount
);

CREATE TABLE `unit_$user_id` (
	unit_owning_user_id INTEGER PRIMARY KEY AUTO_INCREMENT,	-- The unit owning user ID
	unit_id INTEGER NOT NULL,								-- The card internal ID
	exp INTEGER NOT NULL DEFAULT 0,							-- Current EXP
	next_exp INTEGER NOT NULL,								-- Next EXP before level up
	level INTEGER NOT NULL DEFAULT 1,						-- Card level
	max_level INTEGER NOT NULL,								-- Card max level
	rank INTEGER DEFAULT 1,									-- Card rank
	max_rank INTEGER DEFAULT 2,								-- Card max rank
	display_rank INTEGER DEFAULT 1,							-- Card display. 2 = Show idolized, 1 = Show unidolized
	unit_skill_level INTEGER NOT NULL DEFAULT 1,			-- Skill level
	unit_skill_exp INTEGER NOT NULL DEFAULT 0,				-- Skill level EXP. The next level EXP is automatically calcuated
	max_hp INTEGER NOT NULL,								-- Card max HP
	love INTEGER NOT NULL DEFAULT 0,						-- Card bond
	max_love INTEGER NOT NULL,								-- Card max bond
	unit_removable_skill_list TEXT,							-- Used SIS IDs for this unit, comma separated.
	unit_removable_skill_capacity INTEGER NOT NULL,			-- SIS unlocked slot count
	is_rank_max BOOL NOT NULL DEFAULT 0,					-- Is card already idolized?
	is_love_max BOOL NOT NULL DEFAULT 0,					-- Is card already max bonded?
	is_level_max BOOL NOT NULL DEFAULT 0,					-- Is card already max level?
	is_skill_level_max BOOL NOT NULL DEFAULT 0,				-- Is card skill level in max?
	is_removable_skill_capacity_max BOOL NOT NULL DEFAULT 0,-- Is card SIS capacity is at max?
	favorite_flag BOOL NOT NULL DEFAULT 0,					-- Flagged as favourite?
	insert_date INTEGER NOT NULL							-- Unix timestamp when this card added
);

CREATE TABLE `unit_support_$user_id` (
	unit_id INTEGER PRIMARY KEY,			-- Supporting unit_id
	amount INTEGER NOT NULL DEFAULT 0		-- Amount of support unit
);

CREATE TABLE `sis_$user_id` (
	unit_removable_skill_id INTEGER PRIMARY KEY,	-- SIS ID
	total_amount INTEGER NOT NULL DEFAULT 0,		-- Total amount of this SIS
	equipped_amount INTEGER NOT NULL DEFAULT 0		-- How many units uses this SIS?
);

CREATE TABLE `deck_$user_id` (
	deck_num INTEGER NOT NULL PRIMARY KEY,	-- Deck number
	deck_name VARCHAR(10) NOT NULL,			-- Deck name
	deck_members TEXT NOT NULL				-- Deck list. In format: <unit_id>:<unit_id>. Unit id is unit_owning_user_id field in `unit_$user_id` table or 0 if no unit is specificed.
);

CREATE TABLE `sticker_$user_id` (
	sticker_id INTEGER NOT NULL PRIMARY KEY,	-- The sticker ID
	amount_bought INTEGER NOT NULL DEFAULT 0	-- How much it already bought.
);

CREATE TABLE `login_bonus_$user_id` (
	login_bonus_id INTEGER NOT NULL PRIMARY KEY,	-- The login bonus ID. ID 0 is reserved for monthly login bonus.
	counter INTEGER NOT NULL DEFAULT 0				-- The login bonus counter.
);

CREATE TABLE `album_$user_id` (
	unit_id INTEGER NOT NULL PRIMARY KEY,			-- The unit ID
	flags TINYINT NOT NULL DEFAULT 0,				-- Flags bit: 0 = ever have?; 1 = ever idolized?; 2 = ever max bond?; 3 = ever max level?; 4 = ever all max?
	total_love INTEGER NOT NULL DEFAULT 0			-- Max total bond. To follow JP v4.0 behaviour.
);

-- Insert empty deck list. v4.0.x supports 9 decks.
INSERT INTO `deck_$user_id` VALUES (1, 'Team A', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (2, 'Team B', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (3, 'Team C', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (4, 'Team D', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (5, 'Team E', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (6, 'Team F', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (7, 'Team G', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (8, 'Team H', '0:0:0:0:0:0:0:0:0');
INSERT INTO `deck_$user_id` VALUES (9, 'Team I', '0:0:0:0:0:0:0:0:0');

-- Update users
UPDATE `users` SET
	invite_code = '$invite_code',
	friend_table = 'friend_$user_id',
	present_table = 'present_$user_id',
	achievement_table = 'achievement_$user_id',
	item_table = 'item_$user_id',
	unit_table = 'unit_$user_id',
	unit_support_table = 'unit_support_$user_id',
	sis_table = 'sis_$user_id',
	deck_table = 'deck_$user_id',
	sticker_table = 'sticker_$user_id',
	login_bonus_table = 'login_bonus_$user_id',
	album_table = 'album_$user_id',
	unlocked_title = '1',
	unlocked_background = '1'
WHERE user_id = $user_id;
