<?php
/*
 * Null Pointer Private Server
 * Deck alteration
 */

/// \file include.deck.php

/* Returns 0 if not in deck, 1 if in deck (but not main), 2 if in main deck */
function deck_card_in_deck(int $user_id, int $unit_id): int
{
	global $DATABASE;
	
	$data = npps_query("SELECT deck_table, main_deck FROM `users` WHERE user_id = $user_id")[0];
	
	foreach(npps_query("SELECT deck_num, deck_members FROM `{$data['deck_table']}`") as $deck)
	{
		foreach(explode(':', $deck['deck_members']) as $member)
		{
			if($member == $unit_id)
				return $deck['deck_num'] == $data['deck_members'] ? 2 : 1;
		}
	}
	
	return 0;
}

/* position 0 = leftmost; position 8 = rightmost; */
function deck_alter(int $user_id, int $deck_num, array $unit_list): bool
{
	global $DATABASE;
	
	$deck_table = npps_query("SELECT deck_table FROM `users` WHERE user_id = $user_id")[0]['deck_table'];
	$current = explode(':', npps_query("SELECT deck_members FROM `$deck_table` WHERE deck_num = $deck_num")[0]['deck_members']);
	
	foreach($unit_list as $k => $v)
	{
		if($v)
			$current[$k] = $v;
	}
	
	return npps_query("UPDATE `$deck_table` SET deck_members = ? WHERE deck_num = $deck_num", 's', implode(':', $current));
}

/* Calculate smile, pure, and cool (array in that order) */
function deck_calculate_stats_value(array $base, array $bond, int $user_leader_skill, int $guest_leader_skill = 0): array
{
	$lsk = [$user_leader_skill, $guest_leader_skill];
	$add = [0.0, 0.0, 0.0];
	
	foreach($lsk as $skill)
	{
		if($skill == 0)
			continue;
		
		if($skill >= 1 && $skill <= 9)
		{
			/* Non-cross attribute */
			$i = intdiv($skill - 1, 3);
			$add[$i] += (float)$base[$i] * 0.03 * ((($skill - 1) % 3) + 1);
		}
		else
		{
			switch($skill)
			{
				case 31:
				{
					/* Smile Angel */
					$add[0] += (float)$base[1] * 0.12;
					break;
				}
				case 32:
				{
					/* Smile Empress */
					$add[0] += (float)$base[2] * 0.12;
					break;
				}
				case 33:
				{
					/* Pure Princess */
					$add[1] += (float)$base[0] * 0.12;
					break;
				}
				case 34:
				{
					/* Pure Empress */
					$add[1] += (float)$base[2] * 0.12;
					break;
				}
				case 35:
				{
					/* Cool Princess */
					$add[2] += (float)$base[0] * 0.12;
					break;
				}
				case 36:
				{
					/* Cool Angel */
					$add[2] += (float)$base[1] * 0.12;
					break;
				}
				default:
				{
					break;
				}
			}
		}
	}
	
	$out = [];
	
	for($i = 0; $i < 3; $i++)
		$out[$i] = $base[$i] + $bond[$i] + (int)round($add[$i]);
	
	return $out;
}
