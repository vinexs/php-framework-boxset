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

class CmsSession extends CmsAppBase
{
    public $error = null;
    public $plugin_location = '';

    /**
     * Valid $token and retrieve user data.
     */
    function recover_session_by_token($token)
    {
        $this->load_plugin('Mcrypt');
        $mcrypt = new Mcrypt($this->manifest['session_encrypt']);
        $data = explode('.', $mcrypt->decrypt(base64_decode($token)));
        if (!isset($data[2])) {
            return false;
        }
        $user_id = $data[1];
        $create_at = $data[2];
        return $this->retrieve_user_data($user_id);
    }

    /**
     * Load member model and return user data by $user_id.
     */
    function retrieve_user_data($user_id)
    {
        $session_model = $this->load_model('SessionModel', $this->setting['db_source']);
        if (($userdata = $session_model->get_user_by_id($user_id)) == false) {
            $this->error = 'userdata_not_found';
            return false;
        }
        return $userdata;
    }

    /**
     * Process user submited variable and query user_id from database.
     */
    function handler_process_login()
    {
        $login_id = $this->post('login_id', 'string');
        $password = $this->post('password', 'string');
        if (empty($login_id) || empty($password)) {
            return $this->show_json(false, 'invalid_param');
        }
        $session_model = $this->load_model('SessionModel', $this->setting['db_source']);
        if (($user_id = $session_model->verify_user($login_id, $password)) == false) {
            return $this->show_json(false, 'user_not_found');
        }
        $session_only = !(isset($_POST['keep_login']) and $_POST['keep_login'] == true);
        $this->create_session_recover_cookie($user_id, $session_only);
        return $this->show_json(true, 'login_success');
    }

    /**
     * Set a cookie which contain encrypted user id.
     */
    function create_session_recover_cookie($user_id, $session_only = true)
    {
        $this->load_plugin('Mcrypt');
        $mcrypt = new Mcrypt($this->manifest['session_encrypt']);
        $code = base64_encode($mcrypt->encrypt(rand(100, 999) . '.' . $user_id . '.' . date('Y-m-d H:i:s')));
        setcookie($this->manifest['session_token'] . '_CMS', $code, ($session_only ? 0 : time() + 315360000), $this->manifest['url_root'], $_SERVER['SERVER_NAME'], false);
    }

    /**
     * Remove cookie to process logout.
     */
    function handler_process_logout()
    {
        $this->remove_session_recover_cookie();
        return $this->show_json(true, 'logout_success');
    }

    /**
     * Remove the cookie which contain encrypted user id for logout.
     */
    function remove_session_recover_cookie()
    {
        setcookie($this->manifest['session_token'] . '_CMS', '', time() - 315360000, $this->manifest['url_root'], $_SERVER['SERVER_NAME'], false);
    }

}
