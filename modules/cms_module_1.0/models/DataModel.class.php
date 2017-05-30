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

class DataModel extends BaseModel
{
    function get_list($page, $table_name, $filter = array(), $total = false)
    {
        $opt = array(
            'select' => '*',
            'table' => $this->setting['table_prefix'] . $table_name,
            'where' => array(
                'is_delete' => 0,
            ),
            'order_by' => '`id` DESC',
            'limit' => ($this->setting['page_size'] * $page) - $this->setting['page_size'] . ',' . $this->setting['page_size'],
        );
        if (!empty($filter)) {
            $opt['where'] = array_merge($opt['where'], $filter);
        }
        if ($total) {
            unset($opt['order_by'], $opt['limit']);
            $opt['select'] = 'COUNT(*) AS total_cnt';
            $cnt = $this->select($opt);
            return empty($cnt) ? 0 : $cnt[0]['total_cnt'];
        }
        $record = $this->select($opt);
        return empty($record) ? false : $record;
    }

    function get_option($page, $table_name, $display, $value, $target_value = null)
    {
        $opt = array(
            'select' => $display . ', ' . $value,
            'table' => $this->setting['table_prefix'] . $table_name,
            'order_by' => $display . ' ASC',
            'limit' => ($this->setting['page_size'] * $page) - $this->setting['page_size'] . ',' . $this->setting['page_size'],
        );
        if ($target_value != null) {
            $opt['where'][$value] = $target_value;
        }
        $result = $this->select($opt);
        if (empty($result)) {
            return false;
        }
        $data = array();
        foreach ($result as $r) {
            $data[$r[$display]] = $r[$value];
        }
        return $data;
    }

    function load_record($table_name, $primary_key, $primary_key_val)
    {
        $opt = array(
            'select' => '*',
            'table' => $this->setting['table_prefix'] . $table_name,
            'where' => array(
                $primary_key => $primary_key_val,
                'is_show' => 1,
                'is_delete' => 0,
            ),
            'limit' => 1,
        );
        $record_set = $this->select($opt);
        return empty($record_set) ? false : $record_set[0];
    }

    function add_record($table_name, $record)
    {
        $insert_data = array(
            'table' => $this->setting['table_prefix'] . $table_name,
            'row' => $record,
        );
        $id = $this->insert($insert_data);
        return empty($id) ? false : $id;
    }

    function edit_record($table_name, $primary_key, $primary_key_val, $record)
    {
        unset($record[$primary_key]);
        $update_data = array(
            'table' => $this->setting['table_prefix'] . $table_name,
            'set' => $record,
            'where' => array(
                $primary_key => $primary_key_val,
            )
        );
        return $this->update($update_data);
    }

    function remove_record($table_name, $user_id, $primary_key, $primary_key_val)
    {
        $update_data = array(
            'table' => $this->setting['table_prefix'] . $table_name,
            'set' => array(
                'is_show' => 0,
                'is_delete' => 1,
                'last_modified_by' => $user_id,
                'last_modified_at' => date('Y-m-d H:i:s'),
            ),
            'where' => array(
                $primary_key => $primary_key_val,
            )
        );
        return $this->update($update_data);
    }
}
