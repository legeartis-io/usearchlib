<?php

namespace Legeartis\UnifiedSearch\responseObjects;

use Exception;

class Base
{
    /**
     * @throws Exception
     */
    public function __construct(array $data, array $fields)
    {
        foreach ($fields as $field => $type) {
            if (array_key_exists($field, $data)) {
                $value = $data[$field];
                unset($data[$field]);
                if (substr($type, -2) == '[]') {
                    if (is_array($value)) {
                        $type = substr($type, 0, -2);
                        $this->$field = [];
                        if (substr($type, -2) == '[]') {
                            $type = substr($type, 0, -2);
                            foreach ($value as $subkey => $subvalue) {
                                if (is_array($subvalue)) {
                                    foreach ($subvalue as $subsubkey => $subsubvalue) {
                                        $this->$field[$subkey][$subsubkey] = $this->_getValue($type, $subsubvalue);
                                    }
                                } else {
                                    throw new Exception('Unable to make ' . $type . ' to field ' . $field . ' for type ' . gettype($subvalue));
                                }
                            }
                        } else {
                            foreach ($value as $subkey => $subvalue) {
                                $this->$field[$subkey] = $this->_getValue($type, $subvalue);
                            }
                        }
                    } else {
                        throw new Exception('Unable to make ' . $type . ' to field ' . $field . ' for type ' . gettype($value));
                    }
                } else {
                    $this->$field = $this->_getValue($type, $value);
                }
            }
        }

//        if (count($data)) {
//            throw new Exception('Not all fields are mapped to ' . get_class($this) . ': ' . print_r($data, true));
//        }
    }

    /**
     * @param $type
     * @param $value
     * @return mixed
     * @throws Exception
     */
    protected function _getValue($type, $value, $field)
    {
        switch ($type) {
            case 'string':
                return (string)$value;
            case 'bool':
                return (bool)$value;
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'array':
                return (array)$value;
            case 'DateTime':
                $value = $value / 1000;
                if ($value) {
                    $timestamp = new \DateTime(null);
                    $timestamp->setTimestamp($value);
                    return $timestamp;
                } else {
                    return null;
                }
            default:
                if (class_exists('Legeartis\\UnifiedSearch\\responseObjects\\' . $type)) {
                    $type = 'Legeartis\\UnifiedSearch\\responseObjects\\' . $type;
                    return new $type($value);
                } else {
                    throw new Exception('Unable to map ' . $type . ' to class ' . get_class($this) . ' field '.$field);
                }
        }
    }
}
