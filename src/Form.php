<?php
/*
 * RenderArray\Form
 * 
 * Copyright 2015 Jonathan Vollebregt <jnvsor@gmail.com>
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 * 
 * 
 */

namespace RenderArray;

class Form {
    protected static function &wrap(&$input, $labelText = null, $id = null){
        $label = null;
        $output = [
            'class' => ['input']
        ];

        $input['>pos'] = 10;

        if ($labelText)
            $label = [
                '>tag' => 'label',
                '>pos' => -10,
                'for' => $id,
                '>' => ['text' => $labelText],
            ];

        if ($id){
            $output['class'][] = 'input-id-'.$id;
            $output['>'] = [
                'label' => $label,
                'input' => &$input,
            ];
        }
        else if ($label){
            $label['>']['input'] = &$input;
            $output['>'] = ['label' => $label];
        }
        else
            $output['>'] = ['input' => &$input];

        return $output;
    }

    public static function &unwrap(&$wrapped){
        if (isset($wrapped['>tag']) && $wrapped['>tag'] === 'input' && isset($wrapped['type']) && $wrapped['type'] === 'hidden')
            return $wrapped;
        else if (isset($wrapped['>']['input']))
            return $wrapped['>']['input'];
        else if (isset($wrapped['>']['label']['>']['input']))
            return $wrapped['>']['label']['>']['input'];
        else
            return null;
    }

    public static function text($name, $value = null, $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'input',
            'type' => 'text',
            'id' => $id,
            'name' => $name,
            'value' => $value,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-text';

        $input = array_replace($input, $config);

        return $output;
    }

    public static function password($name, $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'input',
            'type' => 'password',
            'id' => $id,
            'name' => $name,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-password';

        $input = array_replace($input, $config);

        return $output;
    }

    public static function textarea($name, $value = '', $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $value = (string) $value;

        $input = [
            '>tag' => 'textarea',
            'id' => $id,
            'name' => $name,
            '>' => $value,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-textarea';

        $input = array_replace($input, $config);

        return $output;
    }

    protected static function options($options, $selected){
        $output = [];

        foreach ($options as $key => $val){
            if (is_array($val))
                $output[] = [
                    '>tag' => 'optgroup',
                    'label' => $key,
                    '>' => self::options($val, $selected),
                ];
            else
                $output[] = [
                    '>tag' => 'option',
                    'value' => $key,
                    'selected' => in_array($key, $selected, true),
                    '>' => (string) $val,
                ];
        }

        return $output;
    }

    public static function select($name, $options = [], $selected = [], $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'select',
            'id' => $id,
            'name' => $name,
            '>' => self::options($options, $selected),
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-select';

        $input = array_replace($input, $config);

        return $output;
    }

    public static function checkbox($name, $value, $checked = false, $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'input',
            'type' => 'checkbox',
            'id' => $id,
            'name' => $name,
            'value' => $value,
            'checked' => $checked,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-checkbox';

        $input = array_replace($input, ['>pos' => -20], $config);

        return $output;
    }

    /**
     * checkbool
     * 
     * The difference between checkbool and checkbox is that checkbox is used
     * for multiple values - similar to a multiple select box -while checkbool
     * is a single value that can be either on or off.
     * 
     * To implement this the checkbool is prefixed with a hidden input setting
     * the value to 0 so if it isn't checked you'll get a 0 in your $_REQUEST
     * instead of simply not getting the value at all.
     */
    public static function checkbool($name, $checked = false, $labelText = null, $id = null, $config = []){
        $output = self::checkbox($name, 1, $checked, $labelText, $id, $config);
        $output['>'][] = self::hidden($name, 0, ['>pos' => -30]);
        return $output;
    }

    public static function radio($name, $value, $checked = false, $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'input',
            'type' => 'radio',
            'id' => $id,
            'name' => $name,
            'value' => $value,
            'checked' => $checked,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-radio';

        $input = array_replace($input, ['>pos' => -20], $config);

        return $output;
    }

    public static function file($name, $value = null, $types = null, $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'input',
            'type' => 'file',
            'id' => $id,
            'name' => $name,
            'value' => $value,
            'accept' => $types,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-file';

        $input = array_replace($input, $config);

        return $output;
    }

    public static function hidden($name, $value, $config = []){
        return array_replace([
            '>tag' => 'input',
            'type' => 'hidden',
            'name' => $name,
            'value' => $value,
        ], $config);
    }

    public static function submit($name = null, $value = null, $labelText = null, $id = null, $config = []){
        if ($id === true)
            $id = $name;

        $input = [
            '>tag' => 'input',
            'type' => 'submit',
            'id' => $id,
            'name' => $name,
            'value' => $value,
        ];

        $output = self::wrap($input, $labelText, $id);
        $output['class'][] = 'input-type-submit';

        $input = array_replace($input, $config);

        return $output;
    }
}
