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

class test_render {
    private $tests = 0;
    private $passed = 0;
    private $failed = 0;

    public function test($array, $expect, $opts = NULL){
        if ($opts)
            $output = render($array, $opts);
        else
            $output = render($array);

        $this->tests++;
        if ($output == $expect){
            $this->passed++;
            echo "<p>Test ".$this->tests." passed. Got:</p>\n\n";
            echo "<code>".htmlspecialchars($output)."</code>\n";
            echo "<hr style=\"border: 1px solid #0F0;\"/>\n\n";
        }
        else{
            $this->failed++;
            echo "<p style=\"color: red; font-weight: bold;\">Test ".$this->tests." failed. Expected:</p>\n\n";
            echo "<code>".htmlspecialchars($expect)."</code>\n";
            echo "<p>Got:</p>\n\n";
            echo "<code>".htmlspecialchars($output)."</code>\n";
            echo "<hr style=\"border: 1px solid #F00;\"/>\n\n";
        }
    }

    public function summary(){
        echo "<p>".$this->tests." tests run. ".$this->failed." failures.</p>";
    }
}

$t = new test_render;


/* Standard text test */
$simple = array('#in' => "text");
$t->test($simple, '<div>text</div>');

/* Nesting test */
$nested = array(
    '#tag' => "ul",
    '#in' => array(
        array('#tag' => "li", '#in' => "Yay"),
        "It's",
        array('#tag' => "button", '#in' => "working!")
    ),
);
$t->test($nested, '<ul><li>Yay</li>It\'s<button>working!</button></ul>');

/* Empty tag test */
$emptyTag = array('#in' => "");
$t->test($emptyTag, '<div></div>');

/* Single tag test */
$singleTag = array('#tag' => "hr");
$t->test($singleTag, '<hr />');

/* Callback test */
function callback_test($array){
    return array('#in' => array("Callback Test passed!", $array));
}
$callbackTest = $singleTag;
$callbackTest['#callback'] = "callback_test";
$t->test($callbackTest, '<div>Callback Test passed!<hr /></div>', array("options!"));
$t->test($callbackTest, '<div>Callback Test passed!<hr /></div>');

/* Callback options test */
function opts_test($array, $opts){
    return array('#in' => array($opts[0], $array));
}
$optsTest = $singleTag;
$optsTest['#callback'] = "opts_test";
$t->test($optsTest, '<div>test-string<hr /></div>', "test-string");

/* Multiple callback test */
$multi_callback_test = $singleTag;
$multi_callback_test['#callback'] = array("callback_test", "opts_test");
$t->test($multi_callback_test, '<div>test-string<div>Callback Test passed!<hr /></div></div>', "test-string");
$multi_callback_test['#callback'] = array("opts_test", "callback_test");
$t->test($multi_callback_test, '<div>Callback Test passed!<div>test-string<hr /></div></div>', "test-string");

/* Quotes escaping test */
$quotes = array(
    '#tag' => "input",
    'type' => "text",
    'value' => "user input with \"quotes\""
);
$t->test($quotes, '<input type="text" value="user input with &quot;quotes&quot;" />');

/* Weights test */
$weights = array(
    '#in' => array(
        array('#tag' => "span", '#weight' => 100, '#in' => ""),
        array('#tag' => "strong", '#weight' => -1, '#in' => ""),
        array('#tag' => "em", '#in' => ""),
        array('#tag' => "u", '#weight' => 0.5, '#in' => ""),
    )
);
$t->test($weights, '<div><strong></strong><em></em><u></u><span></span></div>');

/* Correctly distinguish array from element test */
$array = array("string", array('#in' => "woot"));
$element = array('#tag' => "img");
$t->test($array, 'string<div>woot</div>');
$t->test($element, '<img />');

$t->summary();
