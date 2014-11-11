<?php
/*
 * render_array/test.php
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

require './render_array.php';

$expected = "";

$wierdList = array(
    '#tag' => "ul",
    '#contents' => array(
        array('#tag' => "li", '#contents' => "Yay"),
        "It's",
        array('#tag' => "button", 'class' => array("button", "really-big-button"), '#contents' => "working!")
    ),
);
$expected .= '<ul><li>Yay</li>It\'s<button class="button really-big-button">working!</button></ul>';

function wooooohooooo($array){
    return render(array('#contents' => "WooooOOOOoooHoooOOOooo! Render callbacks woohoo!"));
}

$callbackTest = $wierdList;
$callbackTest['#callback'] = "wooooohooooo";
$expected .= '<div>WooooOOOOoooHoooOOOooo! Render callbacks woohoo!</div>';

$quotes = array(
    '#tag' => "input",
    'type' => "text",
    'value' => "user input with \"quotes\""
);
$expected .= '<input type="text" value="user input with &quot;quotes&quot;" />';

$quotesMulti = array(
    '#contents' => NULL,
    'style' => array(
        "width: 100px;",
        "height: 100px;",
        "background: red;",
        "background-image: url(\"http://www.w3.org/html/logo/downloads/HTML5_Logo_128.png\");",
    ),
);
$expected .= '<div style="width: 100px; height: 100px; background: red; background-image: url(&quot;http://www.w3.org/html/logo/downloads/HTML5_Logo_128.png&quot;);" />';

$emptyTag = array('#contents' => "");
$expected .= '<div></div>';

$singleTag = array('#contents' => NULL);
$expected .= '<div />';

$output = "";
$output .= render($wierdList);
$output .= render($callbackTest);
$output .= render($quotes);
$output .= render($quotesMulti);
$output .= render($emptyTag);
$output .= render($singleTag);

if ($output == $expected)
    echo "Test passed";
else
    echo "<span style=\"color: red; font-weight: bold;\">Test failed</span>";

echo "<br />Here's the code:<hr />\n\n".$output;
