# The render array

This is a recursive descent parser that turns PHP arrays into HTML. The primary
benefit of this is that you can create a structure and alter it's contents
afterwards without having to pick apart HTML.

It also makes it easy to keep frontend code DRY, as demonstrated by the
accompanied Form class which generates form element render arrays.

## Render array format

### Renderable types
*Renderables* come in 3 formats: *Strings*, *render arrays*, and *arrays of renderables*.

*Strings* are output with `htmlspecialchars` so you can drop anything you want in
there without worrying about some form of injection.

*Render arrays* are turned into HTML by the renderer.

### Special fields

There are 5 special fields in render arrays:

* `>tag`: The HTML tag to output. Default `'div'`
* `>raw`: A raw string to output (For if you need to do something unexpected.
  For example: rendering a js file inside a script tag)
  
  **Warning:** Text inside a `>raw` field will *not* be escaped with `htmlspecialchars`!
* `>cb`: An array of callables to modify the array at render time
* `>`: A renderable to be placed inside the tag. If this field is unset the tag
  will be self-closing

The absense of these 4 fields will result in an array being treated as an *array
of renderables*.

The final special field applies to both *render arrays* and *arrays of renderables*:

* `>pos`: The position of this array in it's parent container. This can be
  imagined as "Weight" in that a larger value appears lower in final output.
  Default `0`

Fields of a *render array* which don't begin with a `'>'` character will be
treated as attributes of the tag.

### Attributes

*String* and *numeric* attribute values are output with `htmlspecialchars` so you can drop
anything you want in there without worrying about some form of injection.

```php
$output = Renderer::render([
    '>tag' => 'input',
    'type' => 'text',
    'value' => "I put \"quotes\" into your input to break your system!",
]);
$output === '<input type="text" value="I put &quot;quotes&quot; into your input to break your system" />';
```

*Array* attribute values are flattened and imploded with a space.

```php
$output = Renderer::render([
    '>tag' => 'input',
    'type' => 'text',
    'class' => [
        'wow',
        'such-class',
        'very-array',
    ],
]);
$output === '<input type="text" class="wow such-class very-array" />';
```

*Boolean true* attribute values are output as standard HTML boolean attributes:

```php
$output = Renderer::render([
    '>tag' => 'input',
    'type' => 'checkbox',
    'checked' => true,
]);
$output === '<input type="checkbox" checked />';
```

*Null* and *boolean false* attribute values are ignored.

## The Form class

Forms are one of the places render arrays shine, due to the repetetive nature of
form elements and the automatic escaping of strings and attribute values brought
by the renderer.

The form class optionally adds labels to form elements, and handles common
operations such as populating selectboxes from arrays.

If you suppress or ignore undefined index notices on the first page load, you
can even pass initial form values directly from your `$_REQUEST` and they will
be handled correctly after submit:

```php
<form method="post">
<?php

include 'vendor/autoload.php';

use \RenderArray\Renderer;
use \RenderArray\Form;

echo Renderer::render([
    @Form::text('test', $_POST['test'], "Fill me in"),
    Form::submit()
]);
?>
</form>
```

## More info

For more examples, see the Form class and the tests.
