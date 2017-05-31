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

class CmsAppBase extends Index
{
    public $user = null;

    // ==============  Layout Handler  ==============

    /** Index page of website, show while [/index] or [/] is required. */
    function handler_index()
    {
        if (empty($this->setting['db_source'])) {
            die('Setting: Missing variable "db_source".');
        }
        $vars = array();
        if (!$this->check_login()) {
            return $this->load_view('cms_login_layout', $vars);
        }
        $vars['CONTAIN_VIEW'] = 'index';
        return $this->load_view('cms_frame_layout', $vars);
    }

    /** Check visitor is logged in or not. */
    function check_login()
    {
        if ($this->user != null) {
            return true;
        }
        if (!isset($_COOKIE[$this->manifest['session_token'] . '_CMS'])) {
            return false;
        }
        $session = $this->load_controller('CmsSession');
        $this->user = $session->recover_session_by_token($_COOKIE[$this->manifest['session_token'] . '_CMS']);
        if ($this->user == false) {
            $session->remove_session_recover_cookie();
            return false;
        }
        return true;
    }

    /** Output navigation menu as an Ajax component. */
    function handler_nav($url)
    {
        if (!$this->check_login()) {
            exit;
        }
        $vars = array(
            'login_id' => $this->user['login_id'],
        );
        if (empty($this->setting['menu'])) {
            return $this->load_view('navigation', $vars);
        }
        foreach ($this->setting['menu'] as $url => $item) {
            $vars['menu'][$url] = array(
                'text' => $item['text'],
                'icon_class' => $item['icon_class'],
            );
        }
        return $this->load_view('navigation', $vars);
    }

    // ==============  Default Handler  ==============

    /** Show dynamic CRUD page */
    function handler_default($url)
    {
        if (!$this->check_login()) {
            $this->redirect();
        }
        if (empty($this->setting['menu']) or !isset($this->setting['menu'][$url[0]])) {
            $this->show_error(404);
        }
        $class_name = $this->setting['menu'][$url[0]]['class_file'];
        $property_name = strtolower($class_name);
        if (!class_exists('DBHandlerBase')) {
            $this->load_controller('DBHandlerBase');
        }
        $dbapp = $this->load_controller($class_name);
        $dbapp->run_handler($url);
    }

    /** Allow developer to custom error response. */
    function show_error($error, $line = null)
    {
        parent::show_error($error, $line);
    }

    /** For manage account session, such as create user, change password, login and logout. */
    function handler_session($url)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' or !isset($url[0])) {
            return $this->show_error(403);
        }
        $session = $this->load_controller('CmsSession');
        return $session->{'handler_process_' . $url[0]}($url);
    }

    //  ==============  Handle Error  ==============

    /** For spider to read robots.txt. */
    function handler_robots_txt()
    {
        return $this->load_file(ASSETS_FOLDER . 'robots.txt');
    }

    //  ==============  Internal function  ==============

    /** For browser to read favicon.ico unless layout do not contain one. */
    function handler_favicon_ico()
    {
        return $this->load_file(ASSETS_FOLDER . 'favicon.ico');
    }

    //  ==============  Session & Permission  ==============

    function json_transform_post()
    {
        if (!isset($_POST['json'])) {
            return;
        }
        $json = json_decode($_POST['json'], true);
        if (is_array($json)) {
            $_POST = array_merge($_POST, $json);
        }
        unset($_POST['json']);
    }

    //  ==============  Layout variable  ==============

    /** Add activity base variable to view. */
    function load_default_vars()
    {
        parent::load_default_vars();
        $this->vars['URL_REPOS'] = '//www.vinexs.com/repos/';
        $this->vars['URL_RSC'] = $this->manifest['url_root'] . '/assets/' . $this->manifest['activity_current'] . '/1.0/';
        $this->load_language();
    }

}
