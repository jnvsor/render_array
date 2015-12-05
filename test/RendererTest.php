<?php
/*
 * RendererTest
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

use RenderArray\Renderer;

class RendererTest extends PHPUnit_Framework_TestCase {
    protected function assertRender($array, $output, $opts = null){
        $this->assertSame($output, Renderer::render($array, $opts));
    }

    public function testRender(){
        $this->assertRender(['>' => "text"], '<div>text</div>');
    }

    public function testAttributes(){
        $array = [
            '>tag' => "img",
            'string' => "Str\"ing",
            'int' => 42,
            'float' => 42.222,
            'bool' => false,
            'otherbool' => true,
            'null' => null,
            'array' => ["st\"uff", 4, ["more\"stuff", 6, [4]]],
        ];
        $output = '<img string="Str&quot;ing" int="42" float="42.222" otherbool array="st&quot;uff 4 more&quot;stuff 6 4" />';

        $this->assertRender($array, $output);
    }

    public function testInvalidAttributeValues(){
        foreach (['boolean' => true, 'boolean' => false, 'NULL' => null] as $typename => $value){
            $warning = false;
            try {
                Renderer::render(['>tag' => 'div', 'array' => [$value]]);
            }
            catch (\PHPUnit_Framework_Error $e){
                $this->assertSame("The value type '".$typename."' is not a string, numeric or array.", $e->getMessage());
                $this->assertSame(E_USER_WARNING, $e->getCode());
                $warning = true;
            }
            $this->assertTrue($warning, "Did not throw E_USER_WARNING when it should have. Variable: ".var_export($value, true));
        }
    }

    public function testNesting(){
        $array = [
            '>tag' => 'ul',
            '>' => [
                ['>tag' => 'li', '>' => "Yay"],
                "It's",
                ['>tag' => 'button', '>' => "working!"]
            ]
        ];
        $output = '<ul><li>Yay</li>It\'s<button>working!</button></ul>';
        $this->assertRender($array, $output);
    }

    public function testEmpty(){
        $this->assertRender([], '');
        $this->assertRender('', '');
        $this->assertRender(['>tag' => 'hr'], '<hr />');
        $this->assertRender(['>tag' => 'div', '>' => ""], '<div></div>');
    }

    public function objectCallback($array){
        $ret = ['>tag' => "quote", '>' => "Woot"];

        $ret['>cb'] = isset($array['>cb']) ? $array['>cb'] : null;
        return $ret;
    }

    public function testCallbacks(){
        $cb1 = function ($array){
            return ['>' => ["Callback Test passed!", $array]];
        };
        $cb2 = function ($array, $opts){
            $array['>'] = $opts[0];
            return $array;
        };

        $array = ['>tag' => 'span', '>' => "", '>cb' => []];
        $this->assertRender($array, '<span></span>');

        $array['>cb'] = [$cb1];
        $this->assertRender($array, '<div>Callback Test passed!<span></span></div>');
        $this->assertRender($array, '<div>Callback Test passed!<span></span></div>', ['options']);

        $array['>cb'] = [$cb2];
        $this->assertRender($array, '<span />');
        $this->assertRender($array, '<span>test-string</span>', 'test-string');
        $this->assertRender($array, '<span>test-string</span>', ['test-string']);

        $array['>cb'] = [$cb1, $cb2];
        $this->assertRender($array, '<div>Callback Test passed!<span>test-string</span></div>', 'test-string');

        $array['>cb'] = array_reverse($array['>cb']);
        $this->assertRender($array, '<div>Callback Test passed!<span>test-string</span></div>', 'test-string');

        array_unshift($array['>cb'], function (){
            return "All future callbacks will be deleted!";
        });
        $this->assertRender($array, 'All future callbacks will be deleted!', 'test-string');

        $array['>cb'] = [['RendererTest', 'objectCallback']];
        $this->assertRender($array, '<quote>Woot</quote>');

        $array['>cb'][] = function ($array, $opts){
            return ['>tag' => "code", '>' => [$opts[0], $array, $opts[0]]];
        };
        $this->assertRender($array, '<code>Weet<quote>Woot</quote>Weet</code>', "Weet");
    }

    public function testWeights(){
        $array = [
            ['>tag' => "100", '>pos' => 100],
            "text1",
            ['>tag' => "1", '>pos' => 1],
            ['>tag' => "0.5", '>pos' => 0.5],
            ['>tag' => "0", '>pos' => 0],
            "text3",
            ['>tag' => "none"],
            ['>tag' => "-100", '>pos' => -100],
            "text2",
            ['>tag' => "-1", '>pos' => -1],
            ['>tag' => "-0.5", '>pos' => -0.5],
            ['>tag' => "-0", '>pos' => -0],
        ];
        $this->assertRender($array, '<-100 /><-1 /><-0.5 />text1<0 />text3<none />text2<-0 /><0.5 /><1 /><100 />');
    }

    public function testIsRenderArray(){
        $array = ["string", ['>' => "woot"]];
        $this->assertSame(false, Renderer::is_render_array($array));
        $this->assertRender($array, 'string<div>woot</div>');
        $array = ['>tag' => "img"];
        $this->assertSame(true, Renderer::is_render_array($array));
        $this->assertRender($array, '<img />');
    }

    public function testIsRenderArrayRecursive(){
        $array = [
            [
                '>' => [
                    ['>tag' => "li", '>' => "Yay"],
                    "It's",
                    ['>tag' => "button", '>' => "working!"]
                ]
            ],
            [
                [
                    ['>tag' => "li", '>' => "Arrays"],
                    "Within",
                    ['>tag' => "button", '>' => "Arrays"]
                ],
                [
                    '>pos' => -1,
                    ['>tag' => "li", '>' => "Within"],
                    "Arrays",
                    ['>tag' => "button", '>' => "Within..."]
                ]
            ],
            [
                '>' => ['>tag' => "li", '>' => "Arrays!"]
            ],
        ];
        $this->assertRender($array, '<div><li>Yay</li>It\'s<button>working!</button></div><li>Within</li>Arrays<button>Within...</button><li>Arrays</li>Within<button>Arrays</button><div><li>Arrays!</li></div>');
    }

    public function testRaw(){
        $array = [
            [
                '>tag' => "img",
                '>cb' => [function ($array){
                    return ['>' => ["Callback Test passed!", $array]];
                }],
                '>pos' => 10,
                '>raw' => "text"
            ],
            "more text",
            [
                '>pos' => -10,
                '>raw' => "there is ",
            ]
        ];
        $this->assertRender($array, 'there is more text<div>Callback Test passed!text</div>');
    }

    public function testEscape(){
        $array = "String with \"quotes\" in it";
        $this->assertRender($array, 'String with &quot;quotes&quot; in it');
        $array = [
            '>tag' => 'img',
            'src' => "http://someurl.sometld/somefolder/somescript?somevar=someval&somebool"
        ];
        $this->assertRender($array, '<img src="http://someurl.sometld/somefolder/somescript?somevar=someval&amp;somebool" />');
    }
}
