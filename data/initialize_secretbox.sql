-- NPPS Secretbox initialization

CREATE TABLE `muse_secretbox` (											-- µ's box (Special Box)
	secretbox_id INTEGER PRIMARY KEY AUTOINCREMENT,						-- ID Box (number)
	name TEXT NOT NULL DEFAULT 'µ''s Box',								-- Box name
	description TEXT NOT NULL DEFAULT 'µ''s Box',						-- Description
	title_asset TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',	-- Asset address for title
	banner TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',		-- Banner ("String")
	banner_se TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',		-- Selected banner ("String")
	banner_top TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',		-- Image secretbox page (aka top) ("String")
	start_time INTEGER DEFAULT 0,										-- Unix timestamp when this secretbox starts
	end_time INTEGER DEFAULT 2147483647,								-- Unix timestamp when this secretbox ends
	r_rate REAL NOT NULL,												-- Rate for R cards (number). 0 means disable
	sr_rate REAL NOT NULL,												-- Rate for SR cards (number). 0 means disable
	ur_rate REAL NOT NULL,												-- Rate for UR cards (number). 0 means disable
	ssr_rate REAL NOT NULL,												-- Rate for SSR cards (number). 0 means disable
	r_cards TEXT NOT NULL,												-- Card list (R) Structure: 1,2,3
	sr_cards TEXT NOT NULL,												-- Card list (SR) Structure: 1,2,3
	ur_cards TEXT NOT NULL,												-- Card list (UR) Structure: 1,2,3
	ssr_cards TEXT NOT NULL												-- Card list (SSR) Structure: 1,2,3
);

CREATE TABLE `aqua_secretbox` (											-- Aqours's box (Special Box)
	secretbox_id INTEGER PRIMARY KEY AUTOINCREMENT,						-- ID Box (number)
	name TEXT NOT NULL DEFAULT 'Aqua Box',								-- Box name
	description TEXT NOT NULL DEFAULT 'Aqua Box',						-- Description
	title_asset TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',	-- Asset address for title
	banner TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',		-- Banner ("String")
	banner_se TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',		-- Selected banner ("String")
	banner_top TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',		-- Image secretbox page (aka top) ("String")
	start_time INTEGER DEFAULT 0,										-- Unix timestamp when this secretbox starts
	end_time INTEGER DEFAULT 2147483647,								-- Unix timestamp when this secretbox ends
	r_rate REAL NOT NULL,												-- Rate for R cards (number). 0 means disable
	sr_rate REAL NOT NULL,												-- Rate for SR cards (number). 0 means disable
	ur_rate REAL NOT NULL,												-- Rate for UR cards (number). 0 means disable
	ssr_rate REAL NOT NULL,												-- Rate for SSR cards (number). 0 means disable
	r_cards TEXT NOT NULL,												-- Card list (R) Structure: 1,2,3
	sr_cards TEXT NOT NULL,												-- Card list (SR) Structure: 1,2,3
	ur_cards TEXT NOT NULL,												-- Card list (UR) Structure: 1,2,3
	ssr_cards TEXT NOT NULL												-- Card list (SSR) Structure: 1,2,3
);

CREATE TABLE `muse_blue_secretbox` (									-- µ's box (Blue Ticket/Voucher)
	secretbox_id INTEGER PRIMARY KEY AUTOINCREMENT,						-- ID Box (number)
	name TEXT NOT NULL DEFAULT 'Blue Box',								-- Box name
	description TEXT NOT NULL DEFAULT 'Aqua Box',						-- Description
	title_asset TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',	-- Asset address for title
	banner_top TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',		-- Image secretbox page (aka top) ("String")
	r_rate REAL NOT NULL,												-- Rate for R cards (number). 0 means disable
	sr_rate REAL NOT NULL,												-- Rate for SR cards (number). 0 means disable
	ur_rate REAL NOT NULL,												-- Rate for UR cards (number). 0 means disable
	ssr_rate REAL NOT NULL,												-- Rate for SSR cards (number). 0 means disable
	r_cards TEXT NOT NULL,												-- Card list (R) Structure: 1,2,3
	sr_cards TEXT NOT NULL,												-- Card list (SR) Structure: 1,2,3
	ur_cards TEXT NOT NULL,												-- Card list (UR) Structure: 1,2,3
	ssr_cards TEXT NOT NULL												-- Card list (SSR) Structure: 1,2,3
);

CREATE TABLE `aqua_blue_secretbox` (									-- Aqours's box (Blue Ticket/Voucher)
	secretbox_id INTEGER PRIMARY KEY AUTOINCREMENT,						-- ID Box (number)
	name TEXT NOT NULL DEFAULT 'Blue Box',								-- Box name
	description TEXT NOT NULL DEFAULT 'Aqua Box',						-- Description
	title_asset TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',	-- Asset address for title
	banner_top TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',		-- Image secretbox page (aka top) ("String")
	r_rate REAL NOT NULL,												-- Rate for R cards (number). 0 means disable
	sr_rate REAL NOT NULL,												-- Rate for SR cards (number). 0 means disable
	ur_rate REAL NOT NULL,												-- Rate for UR cards (number). 0 means disable
	ssr_rate REAL NOT NULL,												-- Rate for SSR cards (number). 0 means disable
	r_cards TEXT NOT NULL,												-- Card list (R) Structure: 1,2,3
	sr_cards TEXT NOT NULL,												-- Card list (SR) Structure: 1,2,3
	ur_cards TEXT NOT NULL,												-- Card list (UR) Structure: 1,2,3
	ssr_cards TEXT NOT NULL												-- Card list (SSR) Structure: 1,2,3
);
