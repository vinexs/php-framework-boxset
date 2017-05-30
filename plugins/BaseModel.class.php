<?php

/*
 * Copyright 2017 Vin Wong @ vinexs.com
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
 *
 * Version: 1.0.18
 * Last Update: 2017-05-30
 *
 */

class BaseModel
{
    public $db = null;
    public $sql = '';
    public $setting = array(
        'table_prefix' => 'cms_',
        'page_size' => 30,
    );
    public $errorInfo = array();

    public function select($opt)
    {
        if (!isset($opt['table'])) {
            return false;
        }
        $param = array();
        $sql = "SELECT " . (!empty($opt['select']) ? $opt['select'] : '') . " FROM " . $opt['table'];
        if (!empty($opt['where'])) {
            $where = $this->condition_parser($opt['where']);
            $param = array_merge($param, $where['param']);
            $sql .= " WHERE (" . implode(') AND (', $where['cond']) . ")";
        }
        if (!empty($opt['order_by'])) {
            $sql .= " ORDER BY " . $opt['order_by'];
        }
        if (!empty($opt['group_by'])) {
            $sql .= " GROUP BY " . $opt['group_by'];
        }
        if (!empty($opt['having'])) {
            $sql .= " HAVING " . $opt['having'];
        }
        if (!empty($opt['limit'])) {
            $sql .= " LIMIT " . $opt['limit'];
        }

        return $this->query($sql, $param);
    }

    public function count($opt, $primary_key = 'id')
    {
        if (!isset($opt['table'])) {
            return false;
        }
        $param = array();
        $sql = "SELECT COUNT(`". $primary_key ."`) AS `cnt` FROM " . $opt['table'];
        if (!empty($opt['where'])) {
            $where = $this->condition_parser($opt['where']);
            $param = array_merge($param, $where['param']);
            $sql .= " WHERE (" . implode(') AND (', $where['cond']) . ")";
        }
        if (!empty($opt['group_by'])) {
            $sql .= " GROUP BY " . $opt['group_by'];
        }
        if (!empty($opt['having'])) {
            $sql .= " HAVING " . $opt['having'];
        }
        if (!empty($opt['limit'])) {
            $sql .= " LIMIT " . $opt['limit'];
        }
        $result = $this->query($sql, $param);

        return empty($result[0]['cnt']) ? 0 : (int) $result[0]['cnt'];
    }

    public function condition_parser($condition)
    {
        $cond = array();
        $param = array();
        foreach ($condition as $key => $val) {
            if (is_int($key)) {
                if (is_array($val)) {
                    $cond[] = array_shift($val);
                    if (!empty($val)) {
                        $param = array_merge($param, $val);
                    }
                } else {
                    $cond[] = $val;
                }
            } else {
                if (is_array($val)) {
                    $var_pref = array_shift($val);
                    $cond[] = $key . " = " . $var_pref;
                    if (!empty($val)) {
                        $param = array_merge($param, $val);
                    }
                } else {
                    $cond[] = $key . " = ?";
                    $param[] = $val;
                }
            }
        }
        return array(
            'cond' => $cond,
            'param' => $param,
        );
    }

    public function query($sql, $param)
    {
        $this->sql = $sql;
        $query = $this->db->prepare($sql);
        if (!$query->execute($param)) {
            $this->errorInfo = $query->errorInfo();
            return false;
        }
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert($opt)
    {
        if (!isset($opt['table']) or empty($opt['row'])) {
            return false;
        }

        $sql = "INSERT INTO " . $opt['table'];
        $fields = array();
        $value = array();
        $param = array();
        foreach ($opt['row'] as $field => $data) {
            if (is_int($field)) {
                if (is_array($data) and isset($data[1])
                    and preg_match('/([\w\W]+)\s*?\=\s*?([\w\W]+)/', $data[0], $matches)) {
                    $fields[] = trim($matches[1]);
                    $value[] = trim($matches[2]);
                    $param[] = $data[1];
                } else {
                    continue; // Igrone invalid format
                }
            } else {
                $fields[] = $field;
                $value[] = '?';
                $param[] = $data;
            }
        }
        $sql .= " (" . implode(',', $fields) . ") VALUES (" . implode(',', $value) . ")";
        
        $query = $this->db->prepare($sql);
        if (!$query->execute($param)) {
            $this->errorInfo = $query->errorInfo();
            return false;
        }
        return $this->db->lastInsertId();
    }

    public function update($opt)
    {
        if (!isset($opt['table']) or empty($opt['set']) or empty($opt['where'])) {
            return false;
        }
        $param = array();
        $set = $this->condition_parser($opt['set']);
        $param = array_merge($param, $set['param']);
        $where = $this->condition_parser($opt['where']);
        $param = array_merge($param, $where['param']);
        $sql = "UPDATE " . $opt['table'] . " SET " . implode(', ', $set['cond']) . " WHERE (" . implode(') AND (', $where['cond']) . ")";
        if (!empty($opt['limit'])) {
            $sql .= " LIMIT " . $opt['limit'];
        }

        return $this->execute($sql, $param);
    }

    public function execute($sql, $param)
    {
        $this->sql = $sql;
        $query = $this->db->prepare($sql);
        if (!$query->execute($param)) {
            $this->errorInfo = $query->errorInfo();
            return false;
        }
        return $query->rowCount();
    }

    public function delete($opt)
    {
        if (!isset($opt['table']) or empty($opt['where'])) {
            return false;
        }

        $sql = "DELETE FROM " . $opt['table'];
        $where = $this->condition_parser($opt['where']);
        $sql .= " WHERE (" . implode(',', $where['cond']) . ")";

        return $this->execute($sql, $where['param']);
    }

    public function become_key_value_pair($rows, $key, $value = null)
    {
        $result = array();
        if ($value == null) {
            foreach ($rows as $row) {
                $result[$row[$key]] = $row;
            }
            return $result;
        }
        foreach ($rows as $row) {
            $result[$row[$key]] = $row[$value];
        }
        return $result;
    }
}
