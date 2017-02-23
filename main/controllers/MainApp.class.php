<?php

/*
 * Copyright 2015 Vin Wong @ vinexs.com
 *
 * All rights reserved.
 */

class MainApp extends Index
{
    public $user = null;

    function __construct()
    {
    }

    // ==============  Custom Handler  ==============

    function handler_index()
    {
        $this->redirect('css_sample');
    }

    function handler_css_sample()
    {
        $vars['TITLE'] = 'CSS Sample | Vinexs Framework';
        $vars['CONTAIN_VIEW'] = 'element_samples';
        $this->load_view('frame_layout', $vars);
    }

    function handler_default($url)
    {
        $this->show_error(404, __LINE__);
    }

    /** Allow developer to custom error response. */
    function show_error($error, $line = null)
    {
        parent::show_error($error, $line);
    }

    // Add handler here ...

    // ==============  Default Handler  ==============

    /** For manage account session, such as create user, change password, login and logout. */
    function handler_session($url)
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST' or !isset($url[0])) {
            return $this->show_error(403);
        }
        $session = $this->load_controller('Session');
        return $session->{'handler_process_' . $url[0]}($url);
    }

    /** For spider to read robots.txt. */
    function handler_robots_txt()
    {
        return $this->load_file(ASSETS_FOLDER . 'robots.txt');
    }

    //  ==============  Handle Error  ==============

    /** For browser to read favicon.ico unless layout do not contain one. */
    function handler_favicon_ico()
    {
        return $this->load_file(ASSETS_FOLDER . 'favicon.ico');
    }

    //  ==============  Session & Permission  ==============

    /** Check visitor is logged in or not. */
    function check_login()
    {
        if ($this->user != null) {
            return true;
        }
        if (!isset($_COOKIE[$this->manifest['session_token']])) {
            return false;
        }
        $session = $this->load_controller('Session');
        $this->user = $session->recover_session_by_token($_COOKIE[$this->manifest['session_token']]);
        if ($this->user == false) {
            $session->remove_session_recover_cookie();
            return false;
        }
        return true;
    }

    //  ==============  Layout variable  ==============

    /** Add activity base variable to view. */
    function load_default_vars()
    {
        parent::load_default_vars();
        $this->vars['URL_REPOS'] = '//www.vinexs.com/repos/';
        $this->vars['URL_RSC'] = $this->vars['URL_ASSETS'] . $this->manifest['activity_current'] . '/';
    }

}
