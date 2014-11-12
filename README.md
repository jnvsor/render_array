#The render array
Originally a drupal idea, this is a very slimmed down version. I'm annoyed at
not being able to alter HTML very late in the pipeline, presumeably because
drupal has spoiled me with it's easily altered datatypes.

This is a cheap an' nasty implementation of the aforementioned render
array concept.

###Usage
Elements of a render array where the key begins with a `#` character will be
skipped on rendering. These can be used for render functions to do fancy
stuff with.

Anything that doesn't begin with a `#` character is parsed as an HTML tag
attribute. The default render function parses 3 special elements.

These special elements are:

* `#tag`: Which tag to use to render this element (Default `div`)
* `#contents`: Either a string or an array of renderable objects.  
    When this is empty, the tag will be closed like so:

    ```php
    $array['#contents'] = NULL;
    $array['#tag'] = "img";
    ```

    Will become

    ```html
    <img />
    ```

    If you want a single render array as the sub item, still remember to
    enclose it in an array like so:

    ```php
    $array['#contents'] = array($subItem);
    ```

* `#callback`: An optional rendering override hook. `render()` will call this
    function if it is found.

All other values are parsed as arguments like so:

```php
$array['placeholder'] = "woot";
$array['contents'] = "hellYeah";
```

Forgetting the # in contents leads to this:

```html
<div placeholder="woot" contents="hellYeah" />
```

Additionally, arguments that contain an array will have their contents split
by spaces before being added to the argument like so:

```php
$array['class'] = array("wow", "such-class", "very-array");
```

Will become...

```html
<div class="wow such-class very-array" />
```

####Callbacks
As mentioned before, by assigning the `#callback` value to a render array it
will be rendered by that function instead. Additionally, extra parameters can be
passed to `render()` which will be passed on to the callback like so:

```php
function wierdCallback($array, $opts){
    return $opts['use_default'] ? $string : $opts['replacement'];
}

$array = array(
    '#contents' => "This text",
    '#callback' => wierdCallback,
    );

echo render($array, array('use_default' => FALSE, 'replacement' => "That text"));
echo render($array, array('use_default' => TRUE, 'replacement' => "That text"));
```

Will result in:

```html
<div>That text</div><div>This text</div>
```
