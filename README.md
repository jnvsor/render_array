#The render array
Originally a drupal idea, this is a very slimmed down version. I'm annoyed at
not being able to alter HTML very late in the pipeline, presumeably because
drupal has spoiled me with it's easily altered datatypes.

This is a cheap an' nasty implementation of the aforementioned render
array concept.

###Usage
Elements of a render array where the key begins with a `>` character will be
skipped on rendering. These can be used for render functions to do fancy
stuff with.

Anything that doesn't begin with a `>` character is parsed as an HTML tag
attribute. The default render function parses 4 special elements.

These special elements are:

* `>tag`: Which tag to use to render this element (Default `div`)
* `>`: The children of this element. Either a string or an array of renderable
    objects. When this is empty, the tag will be closed like so:

    ```php
    $array['>'] = NULL;
    $array['>tag'] = "img";
    ```

    Will become

    ```html
    <img />
    ```

    If you want a single render array as the sub item, still remember to
    enclose it in an array like so:

    ```php
    $array['>'] = array($subItem);
    ```

* `>cb`: An optional rendering override hook. `render()` will call this
    function if it is found.
* `>pos`: Elements with a 'heavier' position will be rendered later.
* `>raw`: Ignore '>tag', '>', and attributes. After ordering and callbacks,
    return this value directly.

All other values are parsed as arguments like so:

```php
$array['placeholder'] = "woot";
$array['type'] = "text";
$array['tag'] = "input";
```

Forgetting the `>` in `>tag` leads to this:

```html
<div placeholder="woot" type="text" tag="input" />
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
As mentioned before, by assigning the `>cb` value to a render array it will call
those functions before rendering. Additionally, extra parameters can be passed
to `render()` which will be passed on to the callback like so:

```php
function wierdCallback($array, $opts){
    if (empty($opts['use_default']) || !isset($opts['replacement']))
        return render($array);
    else
        return $opts['replacement'];
}

$array = array(
    '>' => "This text",
    '>cb' => "wierdCallback",
);

echo render($array, array('use_default' => FALSE, 'replacement' => "That text"));
echo render($array, array('use_default' => TRUE, 'replacement' => "That text"));
```

Will result in:

```html
That text<div>This text</div>
```

Note that if you change the hierarchy of the array in a callback the callbacks
for the current element will not move with it. In other words with a callback
like so:

```php
function cb($array){
    return array('>' => $array);
}
```

The callback will have moved one layer deeper. This leads to confusion regarding
multiple callbacks.

```php
function cb($array){
    return array('>tag' => "span", '>' => $array);
}
function cb2($array){
    return array('>tag' => "code", '>' => $array);
}
render(array('>' => "Contents", '>cb' => array("cb", "cb2")));
```

You would expect this code to wrap the `<div>` first in a `<span>`, and then in
a `<code>`:

```html
<code><span><div>Contents</div></span></code>
```

The callbacks remain on the element that has been wrapped, meaning that first
the div is wrapped in a span, and then the div is wrapped in a code. This
results in this output:

```html
<span><code><div>Contents</div></code></span>
```

This behaviour stops things breaking when you alter the hierarchy, but if you
want to override it, simply move the callback into your own callback:

```php
function cb($array){
    $ret = array('>tag' => "span");
    if (isset($array['>cb'])){
        $ret['>cb'] = $array['>cb'];
        unset($array['>cb']);
    }
    $ret['>'] = $array;
    return $ret;
}
```
