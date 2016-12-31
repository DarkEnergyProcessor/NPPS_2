<?php
/// \file HonokaMiku_V3_Dctx.php
/// Version 3 decryption routines

namespace HonokaMiku;

class V3_Dctx extends DecrypterContext
{
	/// \breif Value to check if the decrypter context is already finalized
	protected $is_finalized;
	/// \brief Contains pointer to key table used for decryption
	protected $key_tables;
	/// \brief Contains the base name sum for context finalization
	protected $base_name_sum;
	
	/// \brief Initialize Version 3 decrypter context
	/// \param base_sum_name The base value to calculate the name sum
	/// \param key_tables Array of ints used to pick correct decryption init
	///                   value
	/// \param prefix String prepended on the filename before MD5 calculation
	/// \param header The first 4-bytes of the file
	/// \param filename File name that want to be decrypted
	public function __construct(
		int $base_name_sum,
		array $key_tables,
		string $prefix,
		string $header,
		string $filename
	)
	{
		$flip_digest = [];
		$digest = md5($prefix . basename($filename), true);
		
		for($i = 4; $i < 7; $i++)
			$flip_digest[] = chr((~ord($digest[$i])) & 0xFF);
		
		if(strcmp(implode('', $flip_digest), substr($header, 0, 3)))
			throw new \Exception('Header file doesn\'t match.');
		
		$this->key_tables = $key_tables;
		$this->base_name_sum = $base_name_sum;
		$this->is_finalized = false;
		$this->pos = 0;
		$this->version = 3;
	}
	
	public function decrypt_block(string $buffer): string
	{
		if($this->is_finalized)
		{
			$buflen = strlen($buffer);
			$out_buf = [];
			
			if($buflen == 0) return;
			
			$this->pos += $buflen;

			for($i = 0; $i < $buflen; $i++)
			{
				$out_buf[] = chr(ord($buffer[$i]) ^ $this->xor_key);
				$this->xor_key = ($this->update_key = ($this->update_key * 214013 + 2531011) & 0xFFFFFFFF) >> 24;
			}

			return implode('', $out_buf);
		}

		throw new \Exception('Decrypter is not fully initialized.');
	}
	
	public function goto_offset(int $offset)
	{
		if($this->is_finalized == false)
			throw new \Exception('Decrypter is not fully initialized.');
		if($offset < 0)
			throw new \Exception('Position is negative.');
		
		$loop_times = 0;
		$reset_dctx = false;
		
		if($offset > $this->pos)
			$loop_times = $offset - $this->pos;
		elseif($offset == $this->pos)
			return;
		else
		{
			$loop_times = $offset;
			$reset_dctx = true;
		}
		
		if ($reset_dctx)
			$this->xor_key = ($this->update_key = $this->init_key) >> 24;

		for(; $loop_times != 0; $loop_times--)
				$this->xor_key = ($this->update_key = ($this->update_key * 214013 + 2531011) & 0xFFFFFFFF) >> 24;

		$this->pos = $offset;
	}
	
	protected function update()
	{
		// NOOP
		// For v3, doing update in decrypt_block and goto_offset is way faster
		// because there's no function call overhead
	}
	
	public function final_setup(
		string $filename,
		string $block_rest
	)
	{
		if($this->is_finalized == false)
		{
			$name_sum = $this->base_name_sum;
			$expected_sum = ord($block_rest[7]) | (ord($block_rest[6]) << 8);
			
			foreach(str_split(basename($filename)) as $x)
				$name_sum += ord($x);
			
			if($name_sum == $expected_sum)
			{
				$this->init_key = $this->key_tables[$name_sum & 0x3F];
				$this->update_key = $this->init_key;
				$this->xor_key = $this->init_key >> 24;
				$this->pos = 0;
				$this->is_finalized = true;
				
				return;
			}
			
			throw new \Exception('Header file doesn\'t match.');
		}
	}
}
