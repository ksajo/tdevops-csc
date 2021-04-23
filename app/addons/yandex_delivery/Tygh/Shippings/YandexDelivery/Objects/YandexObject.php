<?php

namespace Tygh\Shippings\YandexDelivery\Objects;

class YandexObject
{
    public $_prefix = "";
    public $_fields = array();
    public $_critical = array();
    public $_critical_empty = array();
    public $_validation = array();
    public $_validation_wrong = array();
    public $_last_error = "";
    public $_error_data = array();

    public function __construct()
    {
        $count_params = func_num_args();
        foreach ($this->_fields as $id => $field) {
            $this->{$field} = $count_params > $id ? func_get_arg($id) : '';
        }
    }
    
    public function fixField($name, $value)
    {
        return is_string($value) ? trim($value) : $value;
    }
    
    public function fixFields()
    {
        foreach ((array) $this as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }

            $this->{$key} = $this->fixField($key, $value);
        }
    }

    public function validate()
    {
        $this->_critical_empty = array();
        $this->_validation_wrong = array();
        
        foreach ($this->_critical as $critical) {
            if (!isset($this->{$critical}) or $this->{$critical} === "") {
                $this->_critical_empty[] = $critical;
            }
        }

        foreach ($this->_validation as $validation => $regexp) {
            if (!isset($this->{$validation}) or ($this->{$validation} != "" and !preg_match($regexp, $this->{$validation}))) {
                $this->_validation_wrong[] = $validation;
            }
        }
        
        if (count($this->_critical_empty) > 0) {
            $this->_last_error = YD_ERROR_VALIDATION_EMPTY;
            $this->_error_data = $this->_critical_empty;

            return false;
        } elseif (count($this->_validation_wrong) > 0) {
            $this->_last_error = YD_ERROR_VALIDATION;
            $this->_error_data = $this->_validation_wrong;

            return false;
        }
        
        $this->_last_error = YD_ERROR_SUCCESS;
        $this->_error_data = array();

        return true;
    }

    public function appendToArray(&$arr, $replace = false, $order = null)
    {
        $arr_result = $arr;
        $this->fixFields();
        
        if (!$this->validate($order)) {
            return false;
        }

        foreach ((array) $this as $key => $value) {
            if ($key[0] == '_') {
                continue;
            }

            if (empty($value)) {
                continue;
            }

            $arr_result[$this->_prefix . $key] = $value;
        }

        if ($replace) {
            $arr = $arr_result;
        }

        return $arr_result;
    }

}
