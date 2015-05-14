<?php
/*
 * render_array/render_array.php
 * 
 * Copyright 2014 Jonathan Vollebregt <jnvsor@gmail.com>
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


function is_render_array($array){
    /* If neither >tag nor > nor >cb nor >raw are set this would render
     * to '<div />' which is invalid HTML - thus we can infer that this is not a
     * render array. */
    return (is_array($array) &&
        (isset($array['>tag']) || isset($array['>']) || isset($array['>cb']) || isset($array['>raw'])));
}

function _process_callbacks($array, $opts){
    if (is_callable($array['>cb'])){
        $callback = $array['>cb'];
        unset($array['>cb']);
        return call_user_func_array($callback, array($array, $opts));
    }
    else if (is_array($array['>cb'])){
        while (!empty($array['>cb'])){
            $callback = array_shift($array['>cb']);
            if (is_callable($callback))
                $array = call_user_func_array($callback, array($array, $opts));
        }

        if (is_array($array))
            unset($array['>cb']);

        return $array;
    }
    else {
        trigger_error("The callback type '".gettype($array['>cb'])."' is not a callable or an array.", E_USER_WARNING);
        return "";
    }
}

function _render_attributes($array){
    $ret = "";
    foreach ($array as $attr => $attrVal){
        if (substr($attr, 0, 1) == '>')
            continue;

        if ($attrVal === TRUE){
            $ret .= " ".$attr;
            continue;
        }

        if (is_string($attrVal) || is_numeric($attrVal)){
            $ret .= " ".$attr."=\"";
            $ret .= htmlspecialchars((string) $attrVal);
            $ret .= "\"";
            continue;
        }

        if (is_array($attrVal)){
            $ret .= " ".$attr."=\"";
            $ret .= htmlspecialchars(_render_value($attrVal));
            $ret .= "\"";
            continue;
        }
    }
    return $ret;
}

function _render_value($value){
    $ret = "";
    foreach ($value as $item){
        if (is_string($item) || is_numeric($item)){
            $ret .= $item." ";
            continue;
        }

        if (is_array($item)){
            $ret .= _render_value($item)." ";
            continue;
        }
    }
    return rtrim($ret);
}

function _render_contents($contents, $opts){
    if (is_string($contents)){
        return $contents;
    }
    else if(is_array($contents)){
        stable_uasort($contents, "_weight_cmp");
        $ret = "";
        foreach ($contents as $element)
            $ret .= render($element, $opts);
        return $ret;
    }
    else {
        trigger_error("The child type '".gettype($contents)."' is not a string or an array.", E_USER_WARNING);
        return "";
    }
}

function _weight_cmp($a, $b){
    $aWeight = (isset($a['>pos']) && is_numeric($a['>pos'])) ? $a['>pos'] : 0;
    $bWeight = (isset($b['>pos']) && is_numeric($b['>pos'])) ? $b['>pos'] : 0;
    return $aWeight - $bWeight;
}

function stable_uasort(&$array, $cmp_function = 'strcmp') {
    if (count($array) < 2)
        return;

    $halfway = count($array) / 2;
    $array1 = array_slice($array, 0, $halfway);
    $array2 = array_slice($array, $halfway);
    stable_uasort($array1, $cmp_function);
    stable_uasort($array2, $cmp_function);

    if (call_user_func($cmp_function, end($array1), reset($array2)) <= 0) {
        $array = array_merge($array1, $array2);
        return;
    }

    $array = array();
    $ptr1 = $ptr2 = 0;
    while ($ptr1 < count($array1) && $ptr2 < count($array2)) {
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

function render($array, $opts = NULL){
    if (!is_string($array) && !is_array($array)){
        trigger_error("This element type '".gettype($array)."' is not a renderable type.", E_USER_WARNING);
        return "";
    }

    if (isset($opts) && !is_array($opts))
        $opts = array($opts);

    if (is_string($array))
        return $array;

    if (!is_render_array($array))
        return _render_contents($array, $opts);

    if (!empty($array['>cb']))
        return render(_process_callbacks($array, $opts), $opts);

    if (isset($array['>raw']))
        return $array['>raw'];

    $tag = (isset($array['>tag']) && $array['>tag'] != "") ? $array['>tag'] : "div";
    $ret = "<".$tag;
    $ret .= _render_attributes($array);

    if (!isset($array['>'])){
        $ret .= " />";
    }
    else {
        $ret .= ">";
        $ret .= render($array['>'], $opts);
        $ret .= "</".$tag.">";
    }

    return $ret;
}
