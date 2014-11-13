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
$simple = array('#contents' => "text");
$t->test($simple, '<div>text</div>');

/* Nesting test */
$nested = array(
    '#tag' => "ul",
    '#contents' => array(
        array('#tag' => "li", '#contents' => "Yay"),
        "It's",
        array('#tag' => "button", '#contents' => "working!")
    ),
);
$t->test($nested, '<ul><li>Yay</li>It\'s<button>working!</button></ul>');

/* Empty tag test */
$emptyTag = array('#contents' => "");
$t->test($emptyTag, '<div></div>');

/* Single tag test */
$singleTag = array('#tag' => "hr");
$t->test($singleTag, '<hr />');

/* Callback test */
function callbackTest($array){
    return render(array('#contents' => "Callback Test passed!"));
}
$callbackTest = $nested;
$callbackTest['#callback'] = "callbackTest";
$t->test($callbackTest, '<div>Callback Test passed!</div>', array("options!"));

/* Callback options test */
function opts_test($array, $opts){
    return render(array('#contents' => $opts[0]));
}
$optsTest = $nested;
$optsTest['#callback'] = "opts_test";
$t->test($optsTest, '<div>test-string</div>', "test-string");

/* Multiple callback options test */
function multi_opts_test($array, $opts){
    return render(array('#contents' => $opts[1]));
}
$multi_opts_test = $nested;
$multi_opts_test['#callback'] = "multi_opts_test";
$t->test($multi_opts_test, '<div>other-test-string</div>', array("test-string", "other-test-string"));

/* Quotes escaping test */
$quotes = array(
    '#tag' => "input",
    'type' => "text",
    'value' => "user input with \"quotes\""
);
$t->test($quotes, '<input type="text" value="user input with &quot;quotes&quot;" />');

/* Weights test */
$weights = array(
    '#contents' => array(
        array('#tag' => "span", '#weight' => 100, '#contents' => ""),
        array('#tag' => "strong", '#weight' => -1, '#contents' => ""),
        array('#tag' => "em", '#contents' => ""),
        array('#tag' => "u", '#weight' => 0.5, '#contents' => ""),
    )
);
$t->test($weights, '<div><strong></strong><em></em><u></u><span></span></div>');

$t->summary();
