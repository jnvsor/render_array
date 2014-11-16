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
    /* If neither #tag nor #in nor #callback are set this would render to
     * '<div />' which is invalid HTML - thus we can infer that this is not a
     * render array. */
    return (is_array($array) &&
        (isset($array['#tag']) || isset($array['#in']) || isset($array['#callback'])));
}

function _process_callbacks($array, $opts){
    $callback = $array['#callback'];

    if (is_callable($callback)){
        unset($array['#callback']);
        return call_user_func_array($callback, array($array, $opts));
    }
    else if (is_array($callback)){
        foreach ($callback as $key => $func){
            $array['#callback'] = $callback[$key];
            $array = _process_callbacks($array, $opts);
        }
        unset($array['#callback']);
        return $array;
    }
    else {
        trigger_error("The callback is not a callable or an array.", E_USER_WARNING);
        return "";
    }
}

function _render_attributes($array){
    $ret = "";
    foreach ($array as $attr => $attrVal){
        if (substr($attr, 0, 1) == "#")
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

function _render_contents($contents){
    if (is_string($contents)){
        return $contents;
    }
    else if(is_array($contents)){
        uasort($contents, "_weight_cmp");
        $ret = "";
        foreach ($contents as $element)
            $ret .= render($element);
        return $ret;
    }
    else {
        trigger_error("The #in type '".gettype($contents)."' is not a string or an array.", E_USER_WARNING);
        return "";
    }
}

function _weight_cmp($a, $b){
    $aWeight = (isset($a['#weight']) && is_numeric($a['#weight'])) ? $a['#weight'] : 0;
    $bWeight = (isset($b['#weight']) && is_numeric($b['#weight'])) ? $b['#weight'] : 0;
    return ($aWeight < $bWeight) ? -1 : 1;
}

function render($array, $opts = NULL){
    if (!is_string($array) && !is_array($array)){
        trigger_error("This element type '".gettype($array)."' is not a renderable type.", E_USER_WARNING);
        return "";
    }

    if (is_string($array))
        return $array;

    if (!is_render_array($array))
        return _render_contents($array);

    if (!empty($array['#callback'])){
        if (isset($opts) && !is_array($opts))
            $opts = array($opts);

        return render(_process_callbacks($array, $opts));
    }

    $tag = empty($array['#tag']) ? "div" : $array['#tag'];
    $ret = "<".$tag;
    $ret .= _render_attributes($array);

    if (!isset($array['#in'])){
        $ret .= " />";
    }
    else {
        $ret .= ">";
        $ret .= render($array['#in']);
        $ret .= "</".$tag.">";
    }

    return $ret;
}
