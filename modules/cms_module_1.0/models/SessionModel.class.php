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

class SessionModel extends BaseModel
{

    function verify_user($login_id, $password)
    {
        $opt = array(
            'select' => '`id`',
            'table' => $this->setting['table_prefix'] . 'user',
            'where' => array(
                'is_show' => 1,
                'is_delete' => 0,
                'login_id' => $login_id,
                'role' => 'admin',
                array('BINARY `password` = PASSWORD(?)', $password),
            ),
            'limit' => 1,
        );
        $result = $this->select($opt);
        return empty($result) ? false : $result[0]['id'];
    }

    function get_user_by_id($user_id)
    {
        $opt = array(
            'select' => '`id`, `login_id`, `last_login`',
            'table' => $this->setting['table_prefix'] . 'user',
            'where' => array(
                'id' => $user_id,
                'is_show' => 1,
                'is_delete' => 0,
                'role' => 'admin',
            ),
            'limit' => 1,
        );
        $result = $this->select($opt);
        return empty($result) ? false : $result[0];
    }

}
