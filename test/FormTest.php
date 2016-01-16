<?php
/*
 * FormTest
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
use RenderArray\Form;

class FormTest extends PHPUnit_Framework_TestCase {
    protected function assertRender($array, $output, $opts = null){
        $this->assertSame($output, Renderer::render($array, $opts));
    }

    public function testWrap(){
        $array = Form::text('testname', null, "Label");
        $this->assertSame([
            'class' => ['input', 'input-type-text'],
            '>' => [
                'label' => [
                    '>tag' => 'label',
                    '>pos' => -10,
                    'for' => null,
                    '>' => [
                        'text' => "Label",
                        'input' => [
                            '>tag' => 'input',
                            'type' => 'text',
                            'id' => null,
                            'name' => 'testname',
                            'value' => null,
                            '>pos' => 10,
                        ]
                    ],
                ]
            ]
        ], $array);
        $this->assertRender(
            $array,
            '<div class="input input-type-text">'
            .'<label>Label<input type="text" name="testname" /></label>'
            .'</div>'
        );

        $array = Form::text('testname', null, "Label", 'testname_input');
        $this->assertSame([
            'class' => ['input', 'input-id-testname_input', 'input-type-text'],
            '>' => [
                'label' => [
                    '>tag' => 'label',
                    '>pos' => -10,
                    'for' => 'testname_input',
                    '>' => ['text' => "Label"],
                ],
                'input' => [
                    '>tag' => 'input',
                    'type' => 'text',
                    'id' => 'testname_input',
                    'name' => 'testname',
                    'value' => null,
                    '>pos' => 10,
                ]
            ]
        ], $array);
        $this->assertRender(
            $array,
            '<div class="input input-id-testname_input input-type-text">'
            .'<label for="testname_input">Label</label>'
            .'<input type="text" id="testname_input" name="testname" />'
            .'</div>'
        );

        $array = Form::text('testname', null, null, true);
        $this->assertSame([
            'class' => ['input', 'input-id-testname', 'input-type-text'],
            '>' => [
                'label' => null,
                'input' => [
                    '>tag' => 'input',
                    'type' => 'text',
                    'id' => 'testname',
                    'name' => 'testname',
                    'value' => null,
                    '>pos' => 10,
                ],
            ]
            
        ], $array);
        $this->assertRender(
            $array,
            '<div class="input input-id-testname input-type-text">'
            .'<input type="text" id="testname" name="testname" />'
            .'</div>'
        );
    }

    /**
     * @depends testWrap
     */
    public function testUnwrap(){
        $array = Form::unwrap(Form::text('testname', 'testvalue'));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'text',
            'id' => null,
            'name' => 'testname',
            'value' => 'testvalue',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="text" name="testname" value="testvalue" />');

        $array = Form::unwrap(Form::text('testname', 'testvalue', "Label"));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'text',
            'id' => null,
            'name' => 'testname',
            'value' => 'testvalue',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="text" name="testname" value="testvalue" />');

        $array = Form::unwrap(Form::text('testname', 'testvalue', "Label", true));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'text',
            'id' => 'testname',
            'name' => 'testname',
            'value' => 'testvalue',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="text" id="testname" name="testname" value="testvalue" />');

        $array = Form::hidden('testname', 'testvalue');
        $this->assertSame(Form::unwrap($array), $array);
    }

    public function testHidden(){
        $array = Form::hidden('testname', 'testvalue');
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'hidden',
            'name' => 'testname',
            'value' => 'testvalue',
        ], $array);
        $this->assertRender($array, '<input type="hidden" name="testname" value="testvalue" />');
    }

    /**
     * @depends testUnwrap
     */
    public function testText(){
        $array = Form::unwrap(Form::text('testname', 'testvalue'));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'text',
            'id' => null,
            'name' => 'testname',
            'value' => 'testvalue',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="text" name="testname" value="testvalue" />');

        $array = Form::unwrap(Form::text('testname', null, null, null, [
            'placeholder' => "Placeholder",
            'disabled' => true,
        ]));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'text',
            'id' => null,
            'name' => 'testname',
            'value' => null,
            '>pos' => 10,
            'placeholder' => "Placeholder",
            'disabled' => true,
        ], $array);
        $this->assertRender($array, '<input type="text" name="testname" placeholder="Placeholder" disabled />');
    }

    /**
     * @depends testUnwrap
     */
    public function testPassword(){
        $array = Form::password('testname');
        $this->assertContains('input-type-password', $array['class']);

        $array = Form::unwrap($array);
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'password',
            'id' => null,
            'name' => 'testname',
            'autocomplete' => 'off',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="password" name="testname" autocomplete="off" />');

        $array = Form::unwrap(Form::password('testname', null, true));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'password',
            'id' => 'testname',
            'name' => 'testname',
            'autocomplete' => 'off',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="password" id="testname" name="testname" autocomplete="off" />');

        $array = Form::unwrap(Form::password('testname', null, 'testid'));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'password',
            'id' => 'testid',
            'name' => 'testname',
            'autocomplete' => 'off',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="password" id="testid" name="testname" autocomplete="off" />');
    }

    /**
     * @depends testUnwrap
     */
    public function testTextarea(){
        $array = Form::textarea('testname');
        $this->assertContains('input-type-textarea', $array['class']);

        $array = Form::unwrap($array);
        $this->assertSame([
            '>tag' => 'textarea',
            'id' => null,
            'name' => 'testname',
            '>' => '',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<textarea name="testname"></textarea>');

        $array = Form::unwrap(Form::textarea('testname', "Value", null, true));
        $this->assertSame([
            '>tag' => 'textarea',
            'id' => 'testname',
            'name' => 'testname',
            '>' => "Value",
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<textarea id="testname" name="testname">Value</textarea>');

        $array = Form::unwrap(Form::textarea('testname', null, null, 'testid', [
            'placeholder' => "Placeholder"
        ]));
        $this->assertSame([
            '>tag' => 'textarea',
            'id' => 'testid',
            'name' => 'testname',
            '>' => '',
            '>pos' => 10,
            'placeholder' => "Placeholder",
        ], $array);
        $this->assertRender($array, '<textarea id="testid" name="testname" placeholder="Placeholder"></textarea>');
    }

    /**
     * @depends testUnwrap
     */
    public function testSelect(){
        $array = Form::select(
            'testname[]',
            [
                'a' => "Option A",
                'b' => "Option B",
                'Group!' => [
                    'c' => "Option C!",
                    'd' => "Option D!",
                    'Another group!' => [
                        'invalid' => "Nested optgroups are invalid!",
                        'but' => "But the spec says they might not be some day!",
                    ],
                    'eek' => "Ouch",
                ],
            ],
            ['b', 'eek'],
            null,
            'testid',
            ['multiple' => true]
        );

        $this->assertContains('input-type-select', $array['class']);

        $array = Form::unwrap($array);
        $this->assertSame([
            '>tag' => 'select',
            'id' => 'testid',
            'name' => 'testname[]',
            '>' => [
                [
                    '>tag' => 'option',
                    'value' => 'a',
                    'selected' => false,
                    '>' => 'Option A',
                ],
                [
                    '>tag' => 'option',
                    'value' => 'b',
                    'selected' => true,
                    '>' => 'Option B',
                ],
                [
                    '>tag' => 'optgroup',
                    'label' => 'Group!',
                    '>' => [
                        [
                            '>tag' => 'option',
                            'value' => 'c',
                            'selected' => false,
                            '>' => 'Option C!',
                        ],
                        [
                            '>tag' => 'option',
                            'value' => 'd',
                            'selected' => false,
                            '>' => 'Option D!',
                        ],
                        [
                            '>tag' => 'optgroup',
                            'label' => 'Another group!',
                            '>' => [
                                [
                                    '>tag' => 'option',
                                    'value' => 'invalid',
                                    'selected' => false,
                                    '>' => 'Nested optgroups are invalid!',
                                ],
                                [
                                    '>tag' => 'option',
                                    'value' => 'but',
                                    'selected' => false,
                                    '>' => 'But the spec says they might not be some day!',
                                ],
                            ],
                        ],
                        [
                        '>tag' => 'option',
                        'value' => 'eek',
                        'selected' => true,
                        '>' => 'Ouch',
                        ],
                    ],
                ],
            ],
            'autocomplete' => 'off',
            '>pos' => 10,
            'multiple' => true,
        ]
        , $array);
        $this->assertRender(
            $array,
            '<select id="testid" name="testname[]" autocomplete="off" multiple>'
            .'<option value="a">Option A</option>'
            .'<option value="b" selected>Option B</option>'
            .'<optgroup label="Group!">'
                .'<option value="c">Option C!</option>'
                .'<option value="d">Option D!</option>'
                .'<optgroup label="Another group!">'
                    .'<option value="invalid">Nested optgroups are invalid!</option>'
                    .'<option value="but">But the spec says they might not be some day!</option>'
                .'</optgroup>'
                .'<option value="eek" selected>Ouch</option>'
            .'</optgroup>'
            .'</select>'
        );
    }

    /**
     * @depends testUnwrap
     */
    public function testCheckbox(){
        $array = Form::checkbox('testname', 1, true, "Click me!", 'testid', ['indeterminate' => true]);
        $this->assertContains('input-type-checkbox', $array['class']);

        $this->assertSame([
            '>tag' => 'input',
            'type' => 'checkbox',
            'id' => 'testid',
            'name' => 'testname',
            'value' => 1,
            'checked' => true,
            '>pos' => -20,
            'indeterminate' => true,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-id-testid input-type-checkbox">'
            .'<input type="checkbox" id="testid" name="testname" value="1" checked indeterminate />'
            .'<label for="testid">Click me!</label>'
            .'</div>'
        );

        $array = Form::checkbox('testname', 1, false, "Click me!");
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'checkbox',
            'id' => null,
            'name' => 'testname',
            'value' => 1,
            'checked' => false,
            '>pos' => -20,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-type-checkbox">'
            .'<label><input type="checkbox" name="testname" value="1" />Click me!</label>'
            .'</div>'
        );

        $array = Form::checkbox('testname', 1, false, "Click me!", null, ['>pos' => 10]);
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'checkbox',
            'id' => null,
            'name' => 'testname',
            'value' => 1,
            'checked' => false,
            '>pos' => 10,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-type-checkbox">'
            .'<label>Click me!<input type="checkbox" name="testname" value="1" /></label>'
            .'</div>'
        );
    }

    /**
     * @depends testUnwrap
     */
    public function testCheckbool(){
        $array = Form::checkbool('testname', true, "Click me!", 'testid', ['indeterminate' => true]);
        $this->assertContains('input-type-checkbox', $array['class']);

        $this->assertSame([
            '>tag' => 'input',
            'type' => 'checkbox',
            'id' => 'testid',
            'name' => 'testname',
            'value' => 1,
            'checked' => true,
            '>pos' => -20,
            'indeterminate' => true,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-id-testid input-type-checkbox">'
            .'<input type="hidden" name="testname" value="0" />'
            .'<input type="checkbox" id="testid" name="testname" value="1" checked indeterminate />'
            .'<label for="testid">Click me!</label>'
            .'</div>'
        );

        $array = Form::checkbool('testname', false, "Click me!");
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'checkbox',
            'id' => null,
            'name' => 'testname',
            'value' => 1,
            'checked' => false,
            '>pos' => -20,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-type-checkbox">'
            .'<input type="hidden" name="testname" value="0" />'
            .'<label><input type="checkbox" name="testname" value="1" />Click me!</label>'
            .'</div>'
        );
    }

    /**
     * @depends testUnwrap
     */
    public function testRadio(){
        $array = Form::radio('testname', 'option1', true, "Click me!", 'testid');
        $this->assertContains('input-type-radio', $array['class']);

        $this->assertSame([
            '>tag' => 'input',
            'type' => 'radio',
            'id' => 'testid',
            'name' => 'testname',
            'value' => 'option1',
            'checked' => true,
            '>pos' => -20,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-id-testid input-type-radio">'
            .'<input type="radio" id="testid" name="testname" value="option1" checked />'
            .'<label for="testid">Click me!</label>'
            .'</div>'
        );

        $array = Form::radio('testname', 'option2', false, "Click me also!");
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'radio',
            'id' => null,
            'name' => 'testname',
            'value' => 'option2',
            'checked' => false,
            '>pos' => -20,
        ], Form::unwrap($array));
        $this->assertRender(
            $array,
            '<div class="input input-type-radio">'
            .'<label><input type="radio" name="testname" value="option2" />Click me also!</label>'
            .'</div>'
        );
    }

    /**
     * @depends testUnwrap
     */
    public function testFile(){
        $array = Form::file('testname', null, '.jpg,.png', "Click me!", 'testid');
        $this->assertContains('input-type-file', $array['class']);

        $array = Form::unwrap($array);
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'file',
            'id' => 'testid',
            'name' => 'testname',
            'value' => null,
            'accept' => '.jpg,.png',
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="file" id="testid" name="testname" accept=".jpg,.png" />');

        $array = Form::unwrap(Form::file('testname'));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'file',
            'id' => null,
            'name' => 'testname',
            'value' => null,
            'accept' => null,
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="file" name="testname" />');
    }

    /**
     * @depends testUnwrap
     */
    public function testSubmit(){
        $array = Form::submit();
        $this->assertContains('input-type-submit', $array['class']);

        $array = Form::unwrap($array);
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'submit',
            'id' => null,
            'name' => null,
            'value' => null,
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="submit" />');

        $array = Form::unwrap(Form::submit(null, "Submit me!"));
        $this->assertSame([
            '>tag' => 'input',
            'type' => 'submit',
            'id' => null,
            'name' => null,
            'value' => "Submit me!",
            '>pos' => 10,
        ], $array);
        $this->assertRender($array, '<input type="submit" value="Submit me!" />');

        $array = Form::submit('name', "Submit me!", "Click this button to submit", true);
        $this->assertRender(
            $array,
            '<div class="input input-id-name input-type-submit">'
            .'<label for="name">Click this button to submit</label>'
            .'<input type="submit" id="name" name="name" value="Submit me!" />'
            .'</div>'
        );
    }

    /**
     * @depends testText
     */
    public function testFieldset(){
        $array = Form::fieldset();
        $this->assertSame([
            '>tag' => 'fieldset',
            '>' => [null],
            'id' => null,
        ], $array);
        $this->assertRender($array, '<fieldset></fieldset>');

        $contents = Form::text('t', 'v', "L");
        $array = Form::fieldset($contents, "Label", 'fancy-fieldset');
        $this->assertSame([
            '>tag' => 'fieldset',
            '>' => [
                [
                    '>tag' => 'legend',
                    '>' => "Label",
                ],
                [$contents],
            ],
            'id' => 'fancy-fieldset',
        ], $array);

        $this->assertRender(
            $array,
            '<fieldset id="fancy-fieldset"><legend>Label</legend>'
            .Renderer::render($contents)
            .'</fieldset>'
        );
    }
}
