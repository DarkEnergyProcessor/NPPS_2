<?php
/*
 * Null-Pointer Private Server
 * Constants which related to SIF
 */

/// \file include.const.php

/// Smile attribute
define('NPPS_ATTRIBUTE_SMILE', 1);
/// Pure attribute
define('NPPS_ATTRIBUTE_PURE', 2);
/// Cool attribute
define('NPPS_ATTRIBUTE_COOL', 3);

/// Song requires LP
define('NPPS_CAPITAL_TYPE_ENERGY', 1);
/// Song requires Token
define('NPPS_CAPITAL_TYPE_TOKEN', 2);

/// Live show is never cleared ("NEW" mark)
define('NPPS_LIVE_NEW', 1);
/// Live show already cleared at least once
define('NPPS_LIVE_EVER_CLEAR', 2);

/// Easy difficulty
define('NPPS_SONG_DIFFICULTY_EASY', 1);
/// Normal difficuty
define('NPPS_SONG_DIFFICULTY_NORMAL', 2);
/// Hard difficulty
define('NPPS_SONG_DIFFICULTY_HARD', 3);
/// Expert difficulty
define('NPPS_SONG_DIFFICULTY_EXPERT', 4);
/// Random + Expert difficulty
define('NPPS_SONG_DIFFICULTY_EXR', 5);
/// Master difficulty
define('NPPS_SONG_DIFFICULTY_MASTER', 6);

/// Event banner
define('NPPS_BANNER_EVENT', 0);
/// Scouting banner
define('NPPS_BANNER_SECRETBOX', 1);
/// WebView banner
define('NPPS_BANNER_WEBVIEW', 2);

/// µ's limited secretbox bit
define('NPPS_SECRETBOX_SPECIAL_MUSE', 16777216);
/// Aqours limited secretbox bit
define('NPPS_SECRETBOX_SPECIAL_AQUA', 33554432);
/// µ's scouting coupon secretbox bit
define('NPPS_SECRETBOX_COUPON_MUSE', 67108864);
/// Aqours scouting coupon secretbox bit
define('NPPS_SECRETBOX_COUPON_AQUA', 134217728);
