<?php
/*
 * RenderArray\Renderer
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

class Renderer {
    protected static function process_callbacks($array, $opts){
        if (is_array($array['>cb'])){
            while (!empty($array['>cb'])){
                $callback = array_shift($array['>cb']);
                if (is_callable($callback))
                    $array = call_user_func_array($callback, array($array, $opts));
                else
                    trigger_error("The type '".gettype($callback)."' is not a callable.", E_USER_WARNING);
            }
        }
        else
            trigger_error("The callback type '".gettype($array['>cb'])."' is not an array.", E_USER_WARNING);

        if (is_array($array))
            unset($array['>cb']);
        return $array;
    }

    protected static function render_attributes($array){
        $ret = '';

        foreach ($array as $attr => $val){
            if (substr($attr, 0, 1) == '>')
                continue;
            else if ($val === TRUE)
                $ret .= ' '.$attr;
            else if (is_string($val) || is_numeric($val) || is_array($val)){
                $values = self::render_value($val);
                if (count($values))
                    $ret .= ' '.$attr.'="'.htmlspecialchars(implode(' ', $values)).'"';
            }
            else if ($val !== FALSE && $val !== NULL)
                trigger_error("The attribute type '".gettype($val)."' is not a bool, string, numeric or array.", E_USER_WARNING);
        }

        return $ret;
    }

    protected static function render_value($value){
        $value = (array) $value;
        $ret = array();

        foreach ($value as $item){
            if (is_string($item) || is_numeric($item))
                $ret[] = trim($item);
            else if (is_array($item))
                $ret = array_merge($ret, self::render_value($item));
            else
                trigger_error("The value type '".gettype($item)."' is not a string, numeric or array.", E_USER_WARNING);
        }

        return $ret;
    }

    protected static function render_contents($contents, $opts){
        unset($contents['>pos']);
        self::stable_uasort($contents, array('\\RenderArray\\Renderer', 'weight_cmp'));
        $ret = '';
        foreach ($contents as $element)
            $ret .= self::render($element, $opts);
        return $ret;
    }

    protected static function weight_cmp($a, $b){
        $aWeight = (isset($a['>pos']) && is_numeric($a['>pos'])) ? $a['>pos'] : 0;
        $bWeight = (isset($b['>pos']) && is_numeric($b['>pos'])) ? $b['>pos'] : 0;
        return $aWeight - $bWeight;
    }

    final protected static function stable_uasort(&$array, $cmp_function = 'strcmp'){
        if (count($array) < 2)
            return;

        $array = array_values($array);
        $halfway = count($array) / 2;
        $array1 = array_slice($array, 0, $halfway);
        $array2 = array_slice($array, $halfway);
        self::stable_uasort($array1, $cmp_function);
        self::stable_uasort($array2, $cmp_function);

        if (call_user_func($cmp_function, end($array1), reset($array2)) <= 0){
            $array = array_merge($array1, $array2);
            return;
        }

        $array = array();
        $ptr1 = $ptr2 = 0;
        while ($ptr1 < count($array1) && $ptr2 < count($array2)){
            if (call_user_func($cmp_function, $array1[$ptr1], $array2[$ptr2]) <= 0)
                $array[] = $array1[$ptr1++];
            else
                $array[] = $array2[$ptr2++];
        }

        while ($ptr1 < count($array1))
            $array[] = $array1[$ptr1++];
        while ($ptr2 < count($array2))
            $array[] = $array2[$ptr2++];

        return;
    }

    public static function is_render_array($array){
        /* If neither >tag nor > nor >cb nor >raw are set this would render
         * to '<div />' which is invalid HTML - thus we can infer that this is not a
         * render array. */
        return (
            is_array($array) &&
            (
                isset($array['>tag']) ||
                isset($array['>']) ||
                isset($array['>cb']) ||
                isset($array['>raw'])
            )
        );
    }

    public static function render($array, $opts = NULL){
        if ($array === null)
            return '';

        if (is_string($array))
            return htmlspecialchars($array);

        if (!is_array($array)){
            trigger_error("This element type '".gettype($array)."' is not a renderable type.", E_USER_WARNING);
            return "";
        }

        if (isset($opts))
            $opts = (array) $opts;

        if (!self::is_render_array($array))
            return self::render_contents($array, $opts);

        if (!empty($array['>cb']))
            return self::render(self::process_callbacks($array, $opts), $opts);

        if (isset($array['>raw']))
            return $array['>raw'];

        $tag = (isset($array['>tag']) && trim($array['>tag']) !== "") ? trim($array['>tag']) : "div";
        $ret = '<'.$tag.self::render_attributes($array);

        if (!isset($array['>']))
            $ret .= ' />';
        else
            $ret .= '>'.self::render($array['>'], $opts).'</'.$tag.'>';

        return $ret;
    }
}
