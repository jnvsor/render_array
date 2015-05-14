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
$simple = array('>' => "text");
$t->test($simple, '<div>text</div>');

/* Attributes test */
$attributes = array(
    '>tag' => "img",
    'string' => "Str\"ing",
    'int' => 42,
    'float' => 42.222,
    'bool' => FALSE,
    'otherbool' => TRUE,
    'null' => NULL,
    'array' => array("st\"uff", 4, FALSE, TRUE, array("more\"stuff", 6, FALSE, TRUE, array(4))),
);
$t->test($attributes, '<img string="Str&quot;ing" int="42" float="42.222" otherbool array="st&quot;uff 4 more&quot;stuff 6 4" />');

/* Nesting test */
$nested = array(
    '>tag' => "ul",
    '>' => array(
        array('>tag' => "li", '>' => "Yay"),
        "It's",
        array('>tag' => "button", '>' => "working!")
    ),
);
$t->test($nested, '<ul><li>Yay</li>It\'s<button>working!</button></ul>');

/* Empty tag test */
$emptyTag = array('>' => "");
$t->test($emptyTag, '<div></div>');

/* Single tag test */
$singleTag = array('>tag' => "hr");
$t->test($singleTag, '<hr />');

/* Callback test */
function callback_test($array){
    return array('>' => array("Callback Test passed!", $array));
}
$callbackTest = array('>tag' => "span", '>' => "");
$callbackTest['>cb'] = "callback_test";
$t->test($callbackTest, '<div>Callback Test passed!<span></span></div>', array("options!"));
$t->test($callbackTest, '<div>Callback Test passed!<span></span></div>');

/* Callback options test */
function opts_test($array, $opts){
    $array['>'] = $opts[0];
    return $array;
}
$optsTest = array('>tag' => "span", '>' => "");
$optsTest['>cb'] = "opts_test";
$t->test($optsTest, '<span>test-string</span>', "test-string");

/* Multiple callback test */
$multi_callback_test = array('>tag' => "span", '>' => "");
$multi_callback_test['>cb'] = array("callback_test", "opts_test");
$t->test($multi_callback_test, '<div>Callback Test passed!<span>test-string</span></div>', "test-string");
$multi_callback_test['>cb'] = array("opts_test", "callback_test");
$t->test($multi_callback_test, '<div>Callback Test passed!<span>test-string</span></div>', "test-string");

/* Object callback test */
class testCallback {
    public function call($array){
        $ret = array('>tag' => "quote", '>' => "Woot");

        $ret['>cb'] = isset($array['>cb']) ? $array['>cb'] : NULL;
        return $ret;
    }
}
$c = new testCallback;
$obj_callback_test = $singleTag;
$obj_callback_test['>cb'] = array($c, "call");
$t->test($obj_callback_test, '<quote>Woot</quote>');

/* Multiple object callback test */
class testMultiCallback {
    public function call($array){
        return array('>tag' => "code", '>' => $array);
    }
}
$mc = new testMultiCallback;
$obj_callback_test = $singleTag;
$obj_callback_test['>cb'] = array(array($c, "call"), array($mc, "call"));
$t->test($obj_callback_test, '<code><quote>Woot</quote></code>');

/* Weights test */
$weights = array(
    '>' => array(
        array('>tag' => "100", '>pos' => 100),
        "text1",
        array('>tag' => "1", '>pos' => 1),
        array('>tag' => "0.5", '>pos' => 0.5),
        array('>tag' => "0", '>pos' => 0),
        "text3",
        array('>tag' => "none"),
        array('>tag' => "-100", '>pos' => -100),
        "text2",
        array('>tag' => "-1", '>pos' => -1),
        array('>tag' => "-0.5", '>pos' => -0.5),
        array('>tag' => "-0", '>pos' => -0),
    )
);
$t->test($weights, '<div><-100 /><-1 /><-0.5 />text1<0 />text3<none />text2<-0 /><0.5 /><1 /><100 /></div>');

/* Correctly distinguish array from element test */
$array = array("string", array('>' => "woot"));
$element = array('>tag' => "img");
$t->test($array, 'string<div>woot</div>');
$t->test($element, '<img />');

/* Correctly distinguish contents array from element test */
$childTypeTest = array(
    array(
        '>' => array(
            array('>tag' => "li", '>' => "Yay"),
            "It's",
            array('>tag' => "button", '>' => "working!")
        )
    ),
    array(
        '>' => array(
            array(
                array('>tag' => "li", '>' => "Arrays"),
                "Within",
                array('>tag' => "button", '>' => "Arrays")
            ),
            array(
                array('>tag' => "li", '>' => "Within"),
                "Arrays",
                array('>tag' => "button", '>' => "Within...")
            )
        )
    ),
    array(
        '>' => array('>tag' => "li", '>' => "Arrays!")
    ),
);
$t->test($childTypeTest, '<div><li>Yay</li>It\'s<button>working!</button></div><div><li>Arrays</li>Within<button>Arrays</button><li>Within</li>Arrays<button>Within...</button></div><div><li>Arrays!</li></div>');

/* >raw test */
$rawtest = array(
    array(
        '>tag' => "img",
        '>cb' => "callback_test",
        '>pos' => 10,
        '>raw' => "text"
    ),
    "more text",
    array(
        '>pos' => -10,
        '>raw' => "there is ",
    ),
);
$t->test($rawtest, 'there is more text<div>Callback Test passed!text</div>');

$t->summary();
