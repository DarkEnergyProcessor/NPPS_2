<?php
/// \file HonokaMiku_JP_Dctx.php
/// SIF JP decryption class

namespace HonokaMiku;

const JP_PREFIX = 'Hello';
const JP_BASENAMESUM = 500;
const JP_KEYTABLES = [
	1210253353	,1736710334	,1030507233	,1924017366	,
	1603299666	,1844516425	,1102797553	,32188137	,
	782633907	,356258523	,957120135	,10030910	,
	811467044	,1226589197	,1303858438	,1423840583	,
	756169139	,1304954701	,1723556931	,648430219	,
	1560506399	,1987934810	,305677577	,505363237	,
	450129501	,1811702731	,2146795414	,842747461	,
	638394899	,51014537	,198914076	,120739502	,
	1973027104	,586031952	,1484278592	,1560111926	,
	441007634	,1006001970	,2038250142	,232546121	,
	827280557	,1307729428	,775964996	,483398502	,
	1724135019	,2125939248	,742088754	,1411519905	,
	136462070	,1084053905	,2039157473	,1943671327	,
	650795184	,151139993	,1467120569	,1883837341	,
	1249929516	,382015614	,1020618905	,1082135529	,
	870997426	,1221338057	,1623152467	,1020681319
];

/// Japanese SIF decrypter context (Version 2)
class JP2_Dctx extends V2_Dctx
{
	/// \brief Initialize SIF JP decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(JP_PREFIX, $header, $filename);
	}
	
	/// \brief Creates SIF JP decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV2(JP_PREFIX, $filename);
	}
}

/// Japanese SIF decrypter context (Version 3)
class JP3_Dctx extends V3_Dctx
{
	/// \brief Initialize SIF JP decrypter context
	/// \param header The first 4-bytes contents of the file
	/// \param filename File name that want to be decrypted. This affects the
	///                 key calculation.
	/// \exception Exception The header does not match and this decrypter
	///                      context can't decrypt it.
	public function __construct(string $header, string $filename)
	{
		parent::__construct(
			JP_BASENAMESUM,
			JP_KEYTABLES,
			JP_PREFIX,
			$header, $filename
		);
	}
	
	/// \brief Creates SIF JP decrypter context specialized for encryption.
	/// \param filename File name that want to be encrypted. This affects the
	///                 key calculation.
	/// \returns Array where index 0 is the object and index 1 is the
	///          4-byte header
	static public function encrypt_setup(string $filename): array
	{
		return EncryptSetupV3(
			JP_BASENAMESUM,
			JP_KEYTABLES,
			JP_PREFIX,
			$filename
		);
	}
}
