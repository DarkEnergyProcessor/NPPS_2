CREATE TABLE `muse_box` (                 -- μ's box (Special Box)
        `is_open`	INTEGER NOT NULL,     -- Boolean value for enable/disable box (0=off, 1 or greater = on) 
	`id`	INTEGER NOT NULL,             -- ID Box (number)
	`banner`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',         -- Banner ("String")
	`banner_se`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',         -- Selected banner ("String")
	`top`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',             -- Image secretbox page (aka top) ("String")
	`r_rate`	INTEGER NOT NULL,         -- Rate for R cards (number)
	`sr_rate`	INTEGER NOT NULL,         -- Rate for SR cards (number)
	`ur_rate`	INTEGER NOT NULL,         -- Rate for UR cards (number)
	`ssr_rate`	INTEGER NOT NULL,         -- Rate for SSR cards (number)
	`r_cards`	INTEGER NOT NULL,         -- Card list (R) Structure: 1,2,3
	`sr_cards`	INTEGER NOT NULL,         -- Card list (SR) Structure: 1,2,3
	`ur_cards`	INTEGER NOT NULL,         -- Card list (UR) Structure: 1,2,3
	`ssr_cards`	INTEGER NOT NULL,         -- Card list (SSR) Structure: 1,2,3
	`name`	TEXT NOT NULL DEFAULT 'Box',             -- Name box 
	`description`	TEXT NOT NULL DEFAULT 'Box',     -- Description
	`title_asset`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',     -- Asset address for title
        PRIMARY KEY(`id`)
);

CREATE TABLE `akua_box` (                 -- Aqours's box (Special Box)
        `is_open`	INTEGER NOT NULL,     -- Boolean value for enable/disable box (0=off, 1 or greater = on) 
	`id`	INTEGER NOT NULL,             -- ID Box (number)
	`banner`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',         -- Banner ("String")
	`banner_se`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/icon/',         -- Selected banner ("String")
	`top`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',             -- Image secretbox page (aka top) ("String")
	`r_rate`	INTEGER NOT NULL,         -- Rate for R cards (number)
	`sr_rate`	INTEGER NOT NULL,         -- Rate for SR cards (number)
	`ur_rate`	INTEGER NOT NULL,         -- Rate for UR cards (number)
	`ssr_rate`	INTEGER NOT NULL,         -- Rate for SSR cards (number)
	`r_cards`	INTEGER NOT NULL,         -- Card list (R) Structure: 1,2,3
	`sr_cards`	INTEGER NOT NULL,         -- Card list (SR) Structure: 1,2,3
	`ur_cards`	INTEGER NOT NULL,         -- Card list (UR) Structure: 1,2,3
	`ssr_cards`	INTEGER NOT NULL,         -- Card list (SSR) Structure: 1,2,3
	`name`	TEXT NOT NULL DEFAULT 'Box',             -- Name box 
	`description`	TEXT NOT NULL DEFAULT 'Box',     -- Description
	`title_asset`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',     -- Asset address for title
        PRIMARY KEY(`id`)
);

CREATE TABLE `muse_blue_box` (            -- μ's box (Blue Ticket/Voucher)
        `is_open`	INTEGER NOT NULL,     -- Boolean value for enable/disable box (0=off, 1 or greater = on) 
	`id`	INTEGER NOT NULL,             -- ID Box (number)
	`top`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',             -- Image secretbox page (aka top) ("String")
	`r_rate`	INTEGER NOT NULL,         -- Rate for R cards (number)
	`sr_rate`	INTEGER NOT NULL,         -- Rate for SR cards (number)
	`ur_rate`	INTEGER NOT NULL,         -- Rate for UR cards (number)
	`ssr_rate`	INTEGER NOT NULL,         -- Rate for SSR cards (number)
	`r_cards`	INTEGER NOT NULL,         -- Card list (R) Structure: 1,2,3
	`sr_cards`	INTEGER NOT NULL,         -- Card list (SR) Structure: 1,2,3
	`ur_cards`	INTEGER NOT NULL,         -- Card list (UR) Structure: 1,2,3
	`ssr_cards`	INTEGER NOT NULL,         -- Card list (SSR) Structure: 1,2,3
	`name`	TEXT NOT NULL DEFAULT 'Box',             -- Name box 
	`description`	TEXT NOT NULL DEFAULT 'Box',     -- Description
	`title_asset`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',     -- Asset address for title
        PRIMARY KEY(`id`)
);

CREATE TABLE `akua_blue_box` (            -- Aqours's box (Blue Ticket/Voucher)
        `is_open`	INTEGER NOT NULL,     -- Boolean value for enable/disable box (0=off, 1 or greater = on) 
	`id`	INTEGER NOT NULL,             -- ID Box (number)
	`top`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/top/',             -- Image secretbox page (aka top) ("String")
	`r_rate`	INTEGER NOT NULL,         -- Rate for R cards (number)
	`sr_rate`	INTEGER NOT NULL,         -- Rate for SR cards (number)
	`ur_rate`	INTEGER NOT NULL,         -- Rate for UR cards (number)
	`ssr_rate`	INTEGER NOT NULL,         -- Rate for SSR cards (number)
	`r_cards`	INTEGER NOT NULL,         -- Card list (R) Structure: 1,2,3
	`sr_cards`	INTEGER NOT NULL,         -- Card list (SR) Structure: 1,2,3
	`ur_cards`	INTEGER NOT NULL,         -- Card list (UR) Structure: 1,2,3
	`ssr_cards`	INTEGER NOT NULL,         -- Card list (SSR) Structure: 1,2,3
	`name`	TEXT NOT NULL DEFAULT 'Box',             -- Name box 
	`description`	TEXT NOT NULL DEFAULT 'Box',     -- Description
	`title_asset`	TEXT NOT NULL DEFAULT 'assets/image/secretbox/title/',     -- Asset address for title
        PRIMARY KEY(`id`)
);
