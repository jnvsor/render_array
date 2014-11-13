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

function _render_attribute_values($values){
    $ret = "";

    if (is_string($values)){
        $ret .= str_replace("\"", "&quot;", $values);
    }

    /* Loop through sub-attributes
     * (EG looping through classes in the class attribute) */
    else if (is_array($values)){
        foreach ($values as $item){
            if(is_string($item))
                $ret .= str_replace("\"", "&quot;", $item)." ";
        }
        $ret = rtrim($ret);
    }

    return $ret;
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
    }

    return $ret;
}

function render($array, $opts = NULL){
    if (isset($opts) && !is_array($opts))
        $opts = array($opts);

    if (!empty($array['#callback'])){
        $callback = $array['#callback'];
        unset($array['#callback']);
        return call_user_func_array($callback, array($array, $opts));
    }

    if (is_string($array))
        return $array;

    $tag = empty($array['#tag']) ? "div" : $array['#tag'];
    $ret = "<".$tag;
    $ret .= _render_attributes($array);

    if (!isset($array['#contents'])){
        $ret .= " />";
    }
    else {
        $ret .= ">";
        $ret .= _render_contents($array['#contents']);
        $ret .= "</".$tag.">";
    }

    return $ret;
}
