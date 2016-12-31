<?php
/// \file HonokaMiku_V2_Dctx.php
/// Version 2 decryption routines

namespace HonokaMiku;

/// \brief Base class of Version 2 decrypter
class V2_Dctx extends DecrypterContext
{
	/// \brief Initialize Version 2 decrypter context
	/// \param prefix String prepended on the filename before MD5 calculation
	/// \param header The first 4-bytes of the file
	/// \param filename File name that want to be decrypted
	public function __construct(string $prefix, string $header, string $filename)
	{
		$digest = md5($prefix . basename($filename), true);
		
		if(strcmp(substr($digest, 4, 4), $header))
			throw new \Exception('Header file doesn\'t match.');
		
		$this->init_key = ((ord($digest[0]) & 0x7F) << 24) |
						  (ord($digest[1]) << 16)          |
						  (ord($digest[2]) << 8)           |
						  ord($digest[3]);
		$this->update_key = $this->init_key;
		$this->xor_key = (($this->init_key >> 23) & 0xFF) |
						 (($this->init_key >> 7) & 0xFF00);
		$this->pos = 0;
		$this->version = 2;
	}
	
	public function decrypt_block(string $buffer): string
	{
		$buflen = strlen($buffer);
		
		if($buflen == 0) return;
		
		$fbuf = 0;
		$out_buffer = [];
		
		if($this->pos % 2 == 1)
		{
			$out_buffer[] = chr(ord($buffer[$fbuf++]) ^ ($this->xor_key >> 8));
			$buflen--;
			
			$this->pos++;
			$this->update();
		}
		
		for(
			$decrypt_size = intdiv($buflen, 2);
			$decrypt_size != 0;
			$decrypt_size--)
		{
			$out_buffer[] = chr(ord($buffer[$fbuf++]) ^ ($this->xor_key & 0xFF));
			$out_buffer[] = chr(ord($buffer[$fbuf++]) ^ ($this->xor_key >> 8));

			$this->update();
		}
		
		if ($fbuf != $buflen)
			$out_buffer[] = chr(ord($buffer[$fbuf++]) ^ ($this->xor_key & 0xFF));
		
		$this->pos += $buflen;
		
		return implode('', $out_buffer);
	}
	
	public function goto_offset(int $offset)
	{
		if($offset < 0)
			throw new \Exception('Position is negative.');
		
		$loop_times = 0;
		$reset_dctx = false;
		
		if($offset > $this->pos)
			$loop_times = $offset - $this->pos;
		elseif($offset == $pos)
			return;
		else
		{
			$loop_times = $offset;
			$reset_dctx = true;
		}

		if($reset_dctx)
		{
			$this->update_key = $this->init_key;
			$this->xor_key = (($this->init_key >> 23) & 0xFF) |
							 (($this->init_key >> 7) & 0xFF00);
		}
		
		if ($this->pos % 2 == 1 && reset_dctx == 0)
		{
			$loop_times--;
			update();
		}
		
		$loop_times = intdiv($loop_times, 2);
		
		for(; $loop_times != 0; $loop_times--)
			update();

		$this->pos = $offset;
	}
	
	protected function update()
	{
		$a = $this->update_key >> 16;
		$b = ((($a * 0x41A70000) & 0x7FFFFFFF) +
			 ($this->update_key & 0xFFFF) * 0x41A7) &
			 0xFFFFFFFF;
		$c = (($a * 0x41A7) >> 15) & 0xFFFFFFFF;
		$d = $c + $b - 0x7FFFFFFF;
		$b = $b > 0x7FFFFFFE ? $d : ($b + $c);

		$this->update_key = $b;
		$this->xor_key = (($b >> 23) & 0xFF) | (($b >> 7) & 0xFF00);
	}
	
	public function final_setup(string $a, string $b) {}	// NOOP
}
