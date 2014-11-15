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


function _process_callbacks($array, $opts){
    $callback = $array['#callback'];

    if (is_string($callback)){
        unset($array['#callback']);
        $array = call_user_func_array($callback, array($array, $opts));
    }
    else if (is_array($callback)){
        foreach ($callback as $key => $func){
            $array['#callback'] = $callback[$key];
            $array = _process_callbacks($array, $opts);
        }
    }
    else {
        trigger_error("The callback is not a string or an array.", E_USER_WARNING);
        return "";
    }

    unset($array['#callback']);
    return $array;
}

function _render_attribute_values($values){
    if (is_string($values)){
        return htmlspecialchars($values);
    }

    /* Loop through sub-attributes
     * (EG looping through classes in the class attribute) */
    else if (is_array($values)){
        $ret = "";
        foreach ($values as $item){
            if(is_string($item))
                $ret .= htmlspecialchars($item)." ";
        }
        return rtrim($ret);
    }
}

function _render_attributes($array){
    $ret = "";

    foreach ($array as $attr => $attrVal){
        if (substr($attr, 0, 1) == "#" ||
            !(is_string($attrVal) || is_array($attrVal)))
            continue;

        if (empty($attrVal)){
            $ret .= " ".$attr;
            continue;
        }

        $ret .= " ".$attr."=\"";
        $ret .= _render_attribute_values($attrVal);
        $ret .= "\"";
    }

    return $ret;
}

function _weight_cmp($a, $b){
    $aWeight = (isset($a['#weight']) && is_numeric($a['#weight'])) ? $a['#weight'] : 0;
    $bWeight = (isset($b['#weight']) && is_numeric($b['#weight'])) ? $b['#weight'] : 0;
    return ($aWeight < $bWeight) ? -1 : 1;
}

function _render_contents($contents){
    if (is_string($contents)){
        return $contents;
    }
    else if(is_array($contents)){
        uasort($contents, "_weight_cmp");
        $ret = "";
        foreach ($contents as $id => $element)
            $ret .= render($element);
        return $ret;
    }
    else {
        trigger_error("This element is not a renderable type.", E_USER_WARNING);
        return "";
    }
}

function render($array, $opts = NULL){
    if (!empty($array['#callback'])){
        if (isset($opts) && !is_array($opts))
            $opts = array($opts);

        return render(_process_callbacks($array, $opts));
    }

    if (is_string($array))
        return $array;

    if (!is_array($array)){
        trigger_error("This element is not a renderable type.", E_USER_WARNING);
        return "";
    }

    /* If neither #tag nor #in nor #callback are set this would evaluate to
     * '<div />' which is invalid HTML - thus we can infer that this is an
     * array of elements after this check */
    if (!isset($array['#tag']) &&
        !isset($array['#in']) &&
        !isset($array['#callback']))
        return _render_contents($array);

    $tag = empty($array['#tag']) ? "div" : $array['#tag'];
    $ret = "<".$tag;
    $ret .= _render_attributes($array);

    if (!isset($array['#in'])){
        $ret .= " />";
    }
    else {
        $ret .= ">";
        $ret .= _render_contents($array['#in']);
        $ret .= "</".$tag.">";
    }

    return $ret;
}
