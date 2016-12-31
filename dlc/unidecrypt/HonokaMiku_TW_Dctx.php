<?php
/// \file HonokaMiku_TW_Dctx.php
/// SIF TW decryption class

namespace HonokaMiku;

const TW_PREFIX = 'M2o2B7i3M6o6N88';
const TW_BASENAMESUM = 1051;
const TW_KEYTABLES = [
	0xA925E518, 0x5AB9C4A4, 0x01950558, 0xACFF7182,
	0xE8183331, 0x9D1B6963, 0x0B8E9D15, 0x96DAD0BB,
	0x0F941E35, 0xC968E363, 0x2058A6AA, 0x7176BB02,
	0x4A4B2403, 0xED7A4E23, 0x3BB41EE6, 0x71634C06,
	0x7E0DD1DA, 0x343325C9, 0xE97B42F6, 0xF68F3C8F,
	0x1587DED8, 0x09935F9B, 0x3273309B, 0xEFBC3178,
	0x94C01BDD, 0x40CEA3BB, 0xD5785C8A, 0x0EC1B98E,
	0xC8D2D2B6, 0xEF7D77B1, 0x71814AAF, 0x2E838EAB,
	0x6B187F58, 0xA9BC924E, 0x6EAB5BA6, 0x738F6D2F,
	0xC1B49AA4, 0xAB6A5D53, 0xF958F728, 0x5A0CDB5B,
	0xB8133931, 0x923336C3, 0xB5A41DE0, 0x5F819B33,
	0x1F3A76AF, 0x56FB7A7C, 0x64AE7167, 0xF39C00F2,
	0x8F6F61C4, 0x6A79B9B9, 0x5B0AB1A6, 0xB7F07A0A,
	0x223035FF, 0x1AA8664C, 0x553EDB16, 0x379230C6,
	0xA2AEEB8A, 0xF647D0EA, 0xA91CB2F6, 0xBB70F817,
	0x94D63581, 0x49A7FAD6, 0x7BEDDD15, 0xC6913CED,
];

/// Taiwanese SIF decrypter context (Version 2)
class TW2_Dctx extends V2_Dctx
{
	/// \brief Initialize SIF TW decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(TW_PREFIX, $header, $filename);
	}
	
	/// \brief Creates SIF TW decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV2(TW_PREFIX, $filename);
	}
}

/// Taiwanese SIF decrypter context (Version 3)
class TW3_Dctx extends V3_Dctx
{
	/// \brief Initialize SIF TW decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(
			TW_BASENAMESUM,
			TW_KEYTABLES,
			TW_PREFIX,
			$header, $filename
		);
	}
	
	/// \brief Creates SIF TW decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV3(
			TW_BASENAMESUM,
			TW_KEYTABLES,
			TW_PREFIX,
			$filename
		);
	}
}
