<?php
/// \file HonokaMiku.php
/// Universal LL!SIF decrypter

namespace HonokaMiku;

/// The decrypter context abstract class. All decrypter inherit this class.
abstract class DecrypterContext
{
	/// \brief Key used at pos 0. Used when the decrypter needs to jump to
	///        specific position
	public $init_key;
	/// \brief Current key at DecrypterContext::$pos
	public $update_key;
	/// \breif Variable to track current position. Needed to allow jump to
	///        specific-position
	public $pos;
	/// \brief Values to use when decrypting data
	public $xor_key;
	/// \brief Decrypter version. Possible values depends on the class:
	///        - HonokaMiku::V1_Dctx - 1
	///        - HonokaMiku::V2_Dctx - 2
	///        - HonokaMiku::V3_Dctx - 3
	public $version;
	
	/// \brief Decrypt SIF-encrypted string in `buffer`
	/// \param buffer Data to be decrypted
	/// \exception Exception The current decrypter context is not currently
	///                      finalized (Version 3 only)
	abstract public function decrypt_block(string $buffer): string;
	/// \brief Recalculate decrypter context to decrypt at specific position.
	/// \param offset Absolute position (starts at 0)
	/// \exception Exception The current decrypter context is notcurrently
	///                      finalized (Version 3 only) or if `offset` is
	///                      negative
	abstract public function goto_offset(int $offset);
	/// \brief Recalculate decrypter context to decrypt at specific position.
	/// \param offset Position relative to current HonokaMiku::DecrypterContext::pos
	/// \exception Exception The current decrypter context is not currently
	///                      finalized (Version 3 only)
	public function goto_offset_relative(int $offset)
	{
		if($offset == 0) return;

		$x = $this->pos + $offset;
		
		if($x < 0)
			throw new Exception("Position is negative.");

		$this->goto_offset(x);
	}
	/// \brief Finalize decrypter context (Version 3 only). Does nothing in
	///        other decryption version.
	/// \param filename File name that want to be decrypted. This affects the
	///        key calculation.
	/// \param block_rest The next 12-bytes header of Version 3 encrypted file.
	abstract public function final_setup(string $filename, string $block_rest);
	/// \brief The key update function. Used to update the key. Used internally
	///        and protected
	abstract protected function update();
}

// Base decrypter
//require(__DIR__ . '/HonokaMiku_V1_Dctx.php');
require(__DIR__ . '/HonokaMiku_V2_Dctx.php');
require(__DIR__ . '/HonokaMiku_V3_Dctx.php');

/// \brief Creates new instance of V2_Dctx for encryption
/// \param prefix String prepended on the filename before MD5 calculation
/// \param filename File name that want to be encrypted
/// \returns Array where index 0 is the object and index 1 is the
///          4-byte header
function EncryptSetupV2(string $prefix, string $filename): array
{
	$digest = md5($prefix . basename($filename), true);
	$header = substr($digest, 4, 4);
	
	return [new V2_Dctx($prefix, $header, $filename), $header];
}

/// \brief Creates new instance of V3_Dctx for encryption
/// \param base_sum_name The base value to calculate the name sum
/// \param key_tables Array of ints used to pick correct decryption init
///                   value
/// \param prefix String prepended on the filename before MD5 calculation
/// \param filename File name that want to be encrypted
/// \returns Array where index 0 is the object and index 1 is the
///          16-byte header
function EncryptSetupV3(
	int $base_sum_name,
	array $key_tables,
	string $prefix,
	string $filename
): array
{
	$bname = basename($filename);
	$flip_digest = [];
	$digest = md5($prefix . $bname, true);
	
	for($i = 4; $i < 7; $i++)
		$flip_digest[] = chr((~ord($digest[$i])) & 0xFF);
	
	$flip_digest[] = "\x0C";
	$block_rest = ["\x00\x00\x00\x00\x00\x00"];
	$name_sum = $base_sum_name;
	
	foreach(str_split($bname) as $x)
		$name_sum += ord($x);
	
	$block_rest[] = chr(($name_sum >> 8) & 0xFF);
	$block_rest[] = chr($name_sum & 0xFF);
	$block_rest[] = "\x00\x00\x00\x00";
	
	$header = [implode('', $flip_digest), implode('', $block_rest)];
	
	$obj = new V3_Dctx($base_sum_name, $key_tables, $prefix, $header[0], $filename);
	$obj->final_setup($filename, $header[1]);
	
	return [$obj, implode('', $header)];
}

// Game decrypter (V2 & V3)
require(__DIR__ . '/HonokaMiku_EN_Dctx.php');
require(__DIR__ . '/HonokaMiku_JP_Dctx.php');
require(__DIR__ . '/HonokaMiku_TW_Dctx.php');
require(__DIR__ . '/HonokaMiku_CN_Dctx.php');


/// \brief Creates decrypter context based from the given headers. Auto detect
/// \param filename File name that want to be decrypted. This affects the key
///                 calculation.
/// \param header The first 4-bytes contents of the file
/// \returns DecrypterContext or NULL if no suitable decryption method is
///                           available.
function FindSuitable(string $filename, string $header)
{
	static $list = [
		'HonokaMiku\\EN2_Dctx', 'HonokaMiku\\JP2_Dctx',
		'HonokaMiku\\TW2_Dctx', 'HonokaMiku\\CN2_Dctx',
		'HonokaMiku\\EN3_Dctx', 'HonokaMiku\\JP3_Dctx',
		'HonokaMiku\\TW3_Dctx', 'HonokaMiku\\CN3_Dctx'
	];
	$dctx = NULL;
	
	foreach($list as $dctx_name)
	{
		try
		{
			$dctx = new $dctx_name($header, $filename);
			break;
		}
		catch(\Exception $e) {}
	}
	
	return $dctx;
}
