<?php

/*
 * Copyright 2017 Vin Wong @ vinexs.com	(MIT License)
 *
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. All advertising materials mentioning features or use of this software
 *    must display the following acknowledgement:
 *    This product includes software developed by the <organization>.
 * 4. Neither the name of the <organization> nor the
 *    names of its contributors may be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY <COPYRIGHT HOLDER> ''AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL <COPYRIGHT HOLDER> BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class Mcrypt
{
    const AES = 0;
    const ECB = 1;

    protected $iv = '';
    protected $key = '';
    protected $mode = 0;

    function __construct($key, $iv = null)
    {
        $this->key = $key;
        if ($iv == null) {
            $this->mode = Mcrypt::ECB;
        } else {
            $this->iv = $iv;
            $this->mode = Mcrypt::AES;
        }
    }

    function encrypt($input)
    {
        if (strlen($input) == 0) {
            return null;
        }
        switch ($this->mode) {
            case Mcrypt::AES:
                $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
                mcrypt_generic_init($td, $this->key, $this->iv);
                $encrypted = mcrypt_generic($td, $input);
                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);
                return bin2hex($encrypted);
                break;
            case Mcrypt::ECB:
                $block = mcrypt_get_block_size('des', 'ecb');
                $pad = $block - (strlen($input) % $block);
                $input .= str_repeat(chr($pad), $pad);
                return bin2hex(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $input, MCRYPT_MODE_ECB));
                break;
            default:
                return null;
        }
    }

    function decrypt($code)
    {
        if (strlen($code) == 0) {
            return null;
        }
        $code = $this->hex2bin($code);
        switch ($this->mode) {
            case Mcrypt::AES:
                $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
                mcrypt_generic_init($td, $this->key, $this->iv);
                $decrypted = mdecrypt_generic($td, $code);
                mcrypt_generic_deinit($td);
                mcrypt_module_close($td);
                return utf8_encode(trim($decrypted));
                break;
            case Mcrypt::ECB:
                $text = trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $code, MCRYPT_MODE_ECB));
                return substr($text, 0, -ord($text[strlen($text) - 1]));
                break;
            default:
                return null;
        }
    }

    private function hex2bin($hexdata)
    {
        $bindata = '';
        for ($i = 0; $i < strlen($hexdata); $i += 2) {
            $bindata .= chr(hexdec(substr($hexdata, $i, 2)));
        }
        return $bindata;
    }

}
