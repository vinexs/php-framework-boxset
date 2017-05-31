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

/** Provide ui constant for limit input type. */
class UI
{
    const TEXT = 1;
    const TEXTAREA = 2;
    const HTML = 3;
    const NUMBER = 4;
    const DROPDOWN = 5;
    const DATE_PICK = 6;
    const DATETIME_PICK = 7;
    const UPLOAD_IMG = 8;
    const UPLOAD_FILE = 9;
    const JSON = 10;
    const MULTILANG = 11;
}

/** Provide field type constant for limit data type. */
class FieldType
{
    // Primary key
    const PRIMARY_KEY = 0;
    // Number
    const NUMBER = 1;
    const DECIMAL = 2;
    // Date
    const DATE = 3;
    const DATETIME = 4;
    const TIMESTAMP = 5;
    // String
    const TEXT = 6;
    const ENUM = 7;
    const SET = 8;
}

class DBHandlerBase extends CmsAppBase
{
    /** Variable used to trace back the parent path. */
    public $plugin_location = '';

    /** This variable must be override. Please example for more information.
     *  $this->table['name'] should not include table prefix.
    */
    public $table = array(
        'name' => '',
        'field' => array(),
    );

    /** Used to assign parent path */
    function __construct()
    {
        $this->plugin_location = dirname(dirname(__FILE__)) . '/';
    }

    /** Divide user to different CRUD events.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     *  @return object|json|string Method response.
     */
    function run_handler($url)
    {
        if (!isset($url[1])) {
            if (empty($this->table['name']) or empty($this->table['field']) or $this->get_primary_key() == null) {
                $this->show_error(503);
            }
            return $this->load_list($url);
        }
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // GET function used to output UI
            switch ($url[1]) {
                case 'list':
                    return $this->load_list($url);
                case 'add':
                    return $this->add_record($url);
                case 'edit':
                    return $this->edit_record($url);
            }
        } else {
            // POST function used to add, edit & delete record.
            switch ($url[1]) {
                case 'get_list':
                    return $this->get_list_record($url);
                case 'get_options':
                    return $this->get_field_option($url);
                case 'get_default':
                    return $this->get_field_default_value($url);
                case 'save':
                    $this->json_transform_post();
                    return $this->process_save_record($url);
                case 'remove':
                    return $this->process_remove_record($url);
                case 'upload':
                    return $this->process_upload_file($url);
            }
        }
    }

    /** Search primary key from $this->table variable.
     *  @return string Primary key or null.
     */
    function get_primary_key()
    {
        foreach ($this->table['field'] as $field_name => $field_setting) {
            if ($field_setting['type'] == FieldType::PRIMARY_KEY) {
                return $field_name;
            }
        }
        return null;
    }

    /** Find table information from $this->setting['menu']. Used as title and subtitle of the page.
     * @param string $part Table name without prefix.
     *
     * @return array title and subtitle to descript table
     */
    function get_cate_info($part)
    {
        return array(
            'text' => $this->setting['menu'][$part]['text'],
            'desc' => $this->setting['menu'][$part]['desc'],
        );
    }

    /** Display table as list. Only field setting contain 'list_field' will be show.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return string HTML content listing table rows.
     */
    function load_list($url)
    {
        $part = $url[0];
        $info = $this->get_cate_info($part);
        $vars['cate_name'] = $info['text'];
        $vars['cate_desc'] = $info['desc'];
        $vars['part'] = $part;
        $vars['list_field'] = array();
        foreach ($this->table['field'] as $field_name => $field_setting) {
            if (isset($field_setting['listing']) and $field_setting['listing']) {
                $vars['list_field'][$field_name] = $field_setting;
            }
        }
        $vars['CONTAIN_VIEW'] = 'listing';
        return $this->load_view('cms_frame_layout', $vars);
    }

    /** Display form to add a row.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return string HTML content form of table's fields.
     */
    function add_record($url)
    {
        $part = $url[0];
        $info = $this->get_cate_info($part);
        $vars['cate_name'] = $info['text'];
        $vars['cate_desc'] = $info['desc'];
        $vars['part'] = $part;
        $vars['field'] = $this->table['field'];
        $vars['mode'] = 'add';
        $vars['primary_key'] = $this->get_primary_key();
        $vars['CONTAIN_VIEW'] = 'form';
        return $this->load_view('cms_frame_layout', $vars);
    }

    /** Display form to edit a row.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return string HTML content form of table's fields.
     */
    function edit_record($url)
    {
        if (!isset($url[2])) {
            return $this->show_error(404);
        }
        $part = $url[0];
        $primary_key_val = $url[2];
        $info = $this->get_cate_info($part);
        $vars['cate_name'] = $info['text'];
        $vars['cate_desc'] = $info['desc'];
        $vars['part'] = $part;
        $vars['field'] = $this->table['field'];
        $primary_key = $this->get_primary_key();
        $data_model = $this->load_model('DataModel', $this->setting['db_source']);
        $record = $data_model->load_record($this->table['name'], $primary_key, $primary_key_val);
        if ($record == false) {
            return $this->show_error(404);
        }
        $show_field = array_keys($this->table['field']);
        $show_field = array_merge($show_field, array('is_show', 'create_by', 'create_at', 'last_modified_by', 'last_modified_at'));
        foreach ($record as $field => $value) {
            if (!in_array($field, $show_field)) {
                unset($record[$field]);
            }
        }
        $vars['mode'] = 'edit';
        $vars['record'] = $record;
        $vars['primary_key'] = $primary_key;
        $vars['CONTAIN_VIEW'] = 'form';
        return $this->load_view('cms_frame_layout', $vars);
    }

    /** Output list record as json for ajax response.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return json Table row and pagination information.
     */
    function get_list_record($url)
    {
        $part = $url[0];
        $page = $this->post('page', 'int', 1);
        $keyword = $this->post('keyword', 'string', '');
        $filter = array();
        if (!empty($keyword)) {
            $sql_part = array();
            $sql_param = array();
            foreach ($this->table['field'] as $field_name => $field_setting) {
                if (isset($field_setting['searchable']) and $field_setting['searchable']) {
                    $sql_part[] = $field_name . ' LIKE ?';
                    $sql_param[] = '%' . $keyword . '%';
                }
            }
            $filter = array(implode(' OR ', $sql_part));
            $filter = array_merge($filter, $sql_param);
        }
        $data_model = $this->load_model('DataModel', $this->setting['db_source']);
        if (($total = $data_model->get_list($page, $this->table['name'], $filter, true)) == 0) {
            return $this->show_json(false);
        }
        if (($list_data = $data_model->get_list($page, $this->table['name'], $filter)) == false) {
            return $this->show_json(false, 'error');
        }
        $this->load_language();
        $list = array();
        $primary_key = strtoupper($this->get_primary_key());
        foreach ($list_data as $record) {
            $temp = array();
            foreach ($record as $field => $val) {
                if (isset($this->table['field'][$field]['listing']) and $this->table['field'][$field]['listing']) {
                    if (isset($this->table['field'][$field]['option'][$val])) {
                        $val = $this->lang($part . '_' . $field . '_' . $val);
                    }
                    $temp[strtoupper(str_replace('-', '_', $field))] = $val;
                }
            }
            if (empty($temp[$primary_key])) {
                return $this->show_json(false, array('error' => 'primary_key_error', 'code' => __LINE__));
            }
            $temp['PRIMARY'] = $temp[$primary_key];
            $list[] = $temp;
        }
        $this->load_plugin('Pagination');
        $p = new Pagination($page, $total / $this->setting['page_size'], $this->manifest['url_root'] . '/' . $this->activity_current . '/');
        return $this->show_json(true, array('list' => $list, 'pager' => $p->get_html(), 'total' => $total));
    }

    /** Output relative field content as json for ajax response.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return json Requested field content.
     */
    function get_field_option($url)
    {
        $fieldname = $this->post('fieldname', 'string', null);
        $page = $this->post('page', 'int', 1);
        if ($fieldname == null or !isset($this->table['field'][$fieldname]['data'])) {
            return $this->show_json(false, array('error' => 'invalid_param', 'code' => __LINE__));
        }
        $data = $this->table['field'][$fieldname]['data'];
        $data_model = $this->load_model('DataModel', $this->setting['db_source']);
        if (($list = $data_model->get_option($page, $data['table'], $data['display'], $data['value'])) == false) {
            return $this->show_json(false, array('error' => 'no_record_found', 'code' => __LINE__));
        }
        if (isset($data['dynamic_lang']) and $data['dynamic_lang']) {
            $this->load_language();
            $list_lang = array();
            foreach ($list as $display => $value) {
                $list_lang[$this->lang($display)] = $value;
            }
        }
        return $this->show_json(true, array(
            'list' => isset($list_lang) ? $list_lang : $list,
            'field_name' => $fieldname
        ));
    }

    /** Output default field option as json for ajax response.
     * @param array $url Handler to response server file, which load by php redirection.
     * @return json Requested field option.
     */
    function get_field_default_value($url)
    {
        $fieldname = $this->post('fieldname', 'string', null);
        $value = $this->post('value', 'string', null);
        if (empty($value)) {
            return $this->show_json(false, array('error' => 'invalid_param', 'code' => __LINE__));
        }
        $data = $this->table['field'][$fieldname]['data'];
        $data_model = $this->load_model('DataModel', $this->setting['db_source']);
        if (($value = $data_model->get_option(1, $data['table'], $data['display'], $data['value'], $value)) == false) {
            return $this->show_json(false, array('error' => 'no_record_found', 'code' => __LINE__));
        }
        return $this->show_json(true, array(
            'id' => current($value),
            'text' => current(array_keys($value)),
        ));
    }

    /** Handle save (add/edit) record request from user.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return json Response saved success status or failure and error message
     */
    function process_save_record($url)
    {
        $part = $url[0];
        $mode = $this->post('mode', 'string', null);
        if ($mode == null) {
            return $this->show_json(false, array('error' => 'invalid_request', 'code' => __LINE__));
        }
        $primary_key = null;
        $upload_field = array();
        $data = array();
        foreach ($this->table['field'] as $field_name => $field_setting) {
            if (!empty($_POST[$field_name])) {
                switch ($field_setting['type']) {
                    case FieldType::PRIMARY_KEY:
                        if ($mode == 'add') {
                            return $this->show_json(false, array('error' => 'invalid_param', 'code' => __LINE__));
                        }
                        $primary_key = $field_name;
                        $data[$field_name] = $_POST[$field_name];
                        break;
                    case FieldType::NUMBER:
                        if ((int)$_POST[$field_name] != $_POST[$field_name]) {
                            return $this->show_json(false, array('error' => 'invalid_format', 'fieldname' => $field_name, 'code' => __LINE__));
                        }
                        $data[$field_name] = (int)$_POST[$field_name];
                        break;
                    case FieldType::DECIMAL:
                        if ((float)$_POST[$field_name] != $_POST[$field_name]) {
                            return $this->show_json(false, array('error' => 'invalid_format', 'fieldname' => $field_name, 'code' => __LINE__));
                        }
                        $data[$field_name] = (float)$_POST[$field_name];
                        break;
                    case FieldType::DATE:
                    case FieldType::DATETIME:
                        if (($timestamp = strtotime($_POST[$field_name])) === false) {
                            return $this->show_json(false, array('error' => 'invalid_format', 'fieldname' => $field_name, 'code' => __LINE__));
                        }
                        $data[$field_name] = date('Y-m-d H:i:s', $timestamp);
                        break;
                    case FieldType::TIMESTAMP:
                        if (($timestamp = strtotime($_POST[$field_name])) === false) {
                            return $this->show_json(false, array('error' => 'invalid_format', 'fieldname' => $field_name, 'code' => __LINE__));
                        }
                        $data[$field_name] = $timestamp;
                        break;
                    case FieldType::TEXT:
                        $data[$field_name] = $_POST[$field_name];
                        break;
                    case FieldType::ENUM:
                        if (!is_string($_POST[$field_name])) {
                            return $this->show_json(false, array('error' => 'invalid_format', 'fieldname' => $field_name, 'code' => __LINE__));
                        }
                        $data[$field_name] = (string)$_POST[$field_name];
                        break;
                    case FieldType::SET:
                        if (is_array($_POST[$field_name])) {
                            $data[$field_name] = implode(',', $_POST[$field_name]);
                        } else {
                            $data[$field_name] = $_POST[$field_name];
                        }
                        break;
                }
                if (isset($field_setting['layout']) and in_array($field_setting['layout'], array(UI::UPLOAD_IMG, UI::UPLOAD_FILE))) {
                    $upload_field[] = $field_name;
                }
            } else if (isset($field_setting['required']) and $field_setting['required']) {
                return $this->show_json(false, array('error' => 'field_required', 'fieldname' => $field_name, 'code' => __LINE__));
            }
        }
        $data_model = $this->load_model('DataModel', $this->setting['db_source']);
        if ($mode == 'add') {
            $data['is_show'] = 1;
            $data['is_delete'] = 0;
            $data['create_by'] = $data['last_modified_by'] = $this->user['id'];
            $data['create_at'] = $data['last_modified_at'] = date('Y-m-d H:i:s');
            if (($insert_key_val = $data_model->add_record($this->table['name'], $data)) == false) {
                return $this->show_json(false, array('error' => 'unable_add_record', 'code' => __LINE__));
            }
            if (!empty($upload_field) and ($upload = $this->process_upload_file_to_rsc($part, $insert_key_val, $upload_field, $data)) == false) {
                return $this->show_json(false, array('error' => 'unable_upload_file', 'code' => __LINE__));
            }
            if (($after = $this->after_record_added($data)) == false) {
                return $this->show_json(false, array('error' => 'unable_exec_after_change', 'code' => __LINE__));
            }
            return $this->show_json(true, array('primary_key' => $insert_key_val));
        } else if ($mode == 'edit' and $primary_key != null) {
            $record = $this->load_record($this->table['name'], $primary_key, $_POST['primary_key_val']);
            if (empty($record)) {
                return $this->show_json(false, array('error' => 'record_alerady_not_existed', 'code' => __LINE__));
            }
            $data['last_modified_by'] = $this->user['id'];
            $data['last_modified_at'] = date('Y-m-d H:i:s');
            if (($updated = $data_model->edit_record($this->table['name'], $primary_key, $data[$primary_key], $data)) == false) {
                return $this->show_json(false, array('error' => 'unable_edit_record', 'code' => __LINE__));
            }
            if (!empty($upload_field) and ($upload = $this->process_upload_file_to_rsc($part, $data[$primary_key], $upload_field, $data)) == false) {
                return $this->show_json(false, array('error' => 'unable_upload_file', 'code' => __LINE__));
            }
            if (($after = $this->after_record_changed($record, $data)) == false) {
                return $this->show_json(false, array('error' => 'unable_exec_after_change', 'code' => __LINE__));
            }
            return $this->show_json(true);
        } else {
            return $this->show_json(false, array('error' => 'invalid_data', 'code' => __LINE__));
        }
    }

    /** Handle remove record request from user.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return json Response saved success status or failure and error message
     */
    function process_remove_record($url)
    {
        $primary_key = $this->get_primary_key();
        if (empty($_POST['primary_key_val'])) {
            return $this->show_json(false, array('error' => 'invalid_primary_val', 'code' => __LINE__));
        }
        $data_model = $this->load_model('DataModel', $this->setting['db_source']);
        $record = $this->load_record($this->table['name'], $primary_key, $_POST['primary_key_val']);
        if (empty($record)) {
            return $this->show_json(false, array('error' => 'record_alerady_not_existed', 'code' => __LINE__));
        }
        $result = $data_model->remove_record($this->table['name'], $this->user['id'], $primary_key, $_POST['primary_key_val']);
        if (!$result) {
            return $this->show_json(false, array('error' => 'unable_delete_record', 'code' => __LINE__));
        }
        if (($after = $this->after_record_removed($record)) == false) {
            return $this->show_json(false, array('error' => 'unable_exec_after_change', 'code' => __LINE__));
        }
        return $this->show_json(true);
    }

    /** Move temporary uploaded file to permanent storage
     * @param string $part         CMS part name (Table name without prefix)
     * @param string $record_key   Primary key value as folder name
     * @param string $upload_field Uploaded file field
     * @param array  $record       File related record
     *
     * @return bool Successful upload or failure
     */
    function process_upload_file_to_rsc($part, $record_key, $upload_field, $record)
    {
        foreach ($record as $fieldname => $value) {
            if (!in_array($fieldname, $upload_field)) {
                continue;
            }
            $temp_storage = $this->manifest['activity'][$this->activity_current]['folder_storage'] . $part . '_' . $fieldname . '/temp/';
            $storage = $this->manifest['activity'][$this->activity_current]['folder_storage'] . $part . '_' . $fieldname . '/' . $record_key . '/';
            if (!file_exists($storage)) {
                mkdir($storage, 0755, true);
            }
            if (!rename($temp_storage . $value, $storage . $value)) {
                return false;
            }
        }
        return true;
    }

    /** Handle file upload from hidden iframe.
     * @param array $url Handler to response server file, which load by php redirection.
     *
     * @return string HTML inline script command.
     */
    function process_upload_file($url)
    {
        $part = $url[0];
        $fieldname = $this->post('fieldname', 'string', null);
        if (empty($this->manifest['activity'][$this->activity_current]['folder_storage']) or $fieldname == null or empty($this->table['field'][$fieldname]['allow_ext'])) {
            $json = array('response' => 'ERROR', 'data' => array('error' => 'environment_setup_error', 'code' => __LINE__));
            echo '<html><body><script>window.parent.CMS.uploadDialog.response( ' . json_encode($json) . ' );</script></body></html>';
            exit;
        }
        if (empty($_FILES['file']['name'])) {
            $json = array('response' => 'ERROR', 'data' => array('error' => 'no_file_uploaded', 'code' => __LINE__));
            echo '<html><body><script>window.parent.CMS.uploadDialog.response( ' . json_encode($json) . ' );</script></body></html>';
            exit;
        }
        $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $allow_exts = !is_array($this->table['field'][$fieldname]['allow_ext']) ? explode(',', $this->table['field'][$fieldname]['allow_ext']) : $this->table['field'][$fieldname]['allow_ext'];
        if (!in_array($ext, $allow_exts)) {
            $json = array('response' => 'ERROR', 'data' => array('error' => 'invalid_file_extension', 'code' => __LINE__));
            echo '<html><body><script>window.parent.CMS.uploadDialog.response( ' . json_encode($json) . ' );</script></body></html>';
            exit;
        }
        $new_filename = $this->user['id'] . '_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
        $temp_storage = $this->manifest['activity'][$this->activity_current]['folder_storage'] . $part . '_' . $fieldname . '/temp/';
        if (!file_exists($temp_storage)) {
            mkdir($temp_storage, 0775, true);
        }
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $temp_storage . $new_filename)) {
            $json = array('response' => 'ERROR', 'data' => array('error' => 'permission_denied', 'code' => __LINE__));
            echo '<html><body><script>window.parent.CMS.uploadDialog.response( ' . json_encode($json) . ' );</script></body></html>';
            exit;
        }
        $json = array('response' => 'OK', 'data' => array('fieldname' => $fieldname, 'filename' => $new_filename));
        if (exif_imagetype($temp_storage . $new_filename) != false) {
            $json['data']['img_url'] = $part . '_' . $fieldname . '/temp/' . $new_filename;
        }
        echo '<html><body><script>window.parent.CMS.uploadDialog.response( ' . json_encode($json) . ' );</script></body></html>';
        exit;
    }

    /** Handle event after record added, design for developer to override with custom method.
     * @param array $record Record just added to database.
     *
     * @return bool Return true if after event normal, false to alert user.
     */
    function after_record_added($record)
    {
        return true;
    }

    /** Handle event after record edited, design for developer to override with custom method.
     * @param array $old_record Record before edit.
     * @param array $new_record Record just updated to database.
     *
     * @return bool Return true if after event normal, false to alert user.
     */
    function after_record_changed($old_record, $new_record)
    {
        return true;
    }

    /** Handle event after record removed, design for developer to override with custom method.
     * @param array $old_record Already remove record.
     *
     * @return bool Return true if after event normal, false to alert user.
     */
    function after_record_removed($old_record)
    {
        return true;
    }

}
