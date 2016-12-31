<?php
/// \file HonokaMiku_EN_Dctx.php
/// SIF WW/EN decryption class

namespace HonokaMiku;

const EN_PREFIX = 'BFd3EnkcKa';
const EN_BASENAMESUM = 844;
const EN_KEYTABLES = [
	2861607190	,3623207331	,3775582911	,3285432773,
	2211141973	,3078448744	,464780620	,714479011,
	439907422	,421011207	,2997499268,630739911,
	1488792645	,1334839443	,3136567329,796841981,
	2604917769	,4035806207	,693592067	,1142167757,
	1158290436	,568289681	,3621754479,3645263650,
	4125133444	,3226430103	,3090611485,1144327221,
	879762852	,2932733487	,1916506591,2754493440,
	1489123288	,3555253860	,2353824933,1682542640,
	635743937	,3455367432	,532501229	,4106615561,
	2081902950	,143042908	,2637612210	,1140910436,
	3402665631	,334620177	,1874530657	,863688911,
	1651916050	,1216533340	,2730854202	,1488870464,
	2778406960	,3973978011	,1602100650	,2877224961,
	1406289939	,1442089725	,2196364928	,2599396125,
	2963448367	,3316646782	,322755307	,3531653795
];

/// International SIF decrypter context (Version 2)
class EN2_Dctx extends V2_Dctx
{
	/// \brief Initialize SIF EN decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(EN_PREFIX, $header, $filename);
	}
	
	/// \brief Creates SIF EN decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV2(EN_PREFIX, $filename);
	}
}

/// International SIF decrypter context (Version 3)
class EN3_Dctx extends V3_Dctx
{
	/// \brief Initialize SIF EN decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(
			EN_BASENAMESUM,
			EN_KEYTABLES,
			EN_PREFIX,
			$header, $filename
		);
	}
	
	/// \brief Creates SIF EN decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV3(
			EN_BASENAMESUM,
			EN_KEYTABLES,
			EN_PREFIX,
			$filename
		);
	}
}
