<?php
/// \file HonokaMiku_CN_Dctx.php
/// SIF CN decryption class

namespace HonokaMiku;

const CN_PREFIX = 'iLbs0LpvJrXm3zjdhAr4';
const CN_BASENAMESUM = 1847;
const CN_KEYTABLES = [
	0x1b695658, 0x0a43a213, 0x0ead0863, 0x1400056d,
	0xd470461d, 0xb6152300, 0xfbe054bc, 0x9ac9f112,
	0x23d3cab6, 0xcd8fe028, 0x6905bd74, 0x01a3a612, 
	0x6e96a579, 0x333d7ad1, 0xb6688bff, 0x29160495, 
	0xd7743bcf, 0x8ede97bb, 0xcacb7e8d, 0x24d81c23, 
	0xdbfc6947, 0xb07521c8, 0xf506e2ae, 0x3f48df2f, 
	0x52beb172, 0x695935e8, 0x13e2a0a9, 0xe2edf409, 
	0x96cba5c1, 0xdbb1e890, 0x4c2af968, 0x17fd17c6, 
	0x1b9af5a8, 0x97c0bc25, 0x8413c879, 0xd9b13fe1, 
	0x4066a948, 0x9662023a, 0x74a4feee, 0x1f24b4f6, 
	0x637688c8, 0x7a7ccf70, 0x91042eec, 0x57edd02c, 
	0x666da2dd, 0x92839de9, 0x43baa9ed, 0x024a8e2c, 
	0xd4ee7b72, 0x34c18b72, 0x13b275c4, 0xed506a6e, 
	0xbc1c29b9, 0xfa66a220, 0xc2364de3, 0x767e52b2, 
	0xe2d32439, 0xe6f0cef5, 0xd18c8687, 0x14bba295, 
	0xcd84d15b, 0xa0290f82, 0xd3e95afc, 0x9c6a97b4
];

/// \brief Simplified Chinese SIF decrypter context (Version 2)
class CN2_Dctx extends V2_Dctx
{
	/// \brief Initialize SIF CN decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(CN_PREFIX, $header, $filename);
	}
	
	/// \brief Creates SIF CN decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV2(CN_PREFIX, $filename);
	}
}

/// \brief Simplified Chinese SIF decrypter context (Version 3)
class CN3_Dctx extends V3_Dctx
{
	/// \brief Initialize SIF CN decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(
			CN_BASENAMESUM,
			CN_KEYTABLES,
			CN_PREFIX,
			$header, $filename
		);
	}
	
	/// \brief Creates SIF CN decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV3(
			CN_BASENAMESUM,
			CN_KEYTABLES,
			CN_PREFIX,
			$filename
		);
	}
}
