#The render array
Originally a drupal idea, this is a very slimmed down version. I'm annoyed at
not being able to alter HTML very late in the pipeline, presumeably because
drupal has spoiled me with it's easily altered datatypes.

This is a cheap an' nasty implementation of the aforementioned render
array concept.

Elements of a render array where the key begins with a `#` character will be
skipped on rendering. These can be used for render functions to do fancy
stuff with.

Anything that doesn't begin with a `#` character is parsed as an HTML tag
attribute. The default render function parses 3 special elements.

These special elements are:

* `#tag`: Which tag to use to render this element (Default `div`)
* `#contents`: Either a string or an array of renderable objects.  
    When this is empty, the tag will be closed like so:

        $array['#contents'] = NULL;
        $array['#tag'] = "img";
        
    Will become

        <img />
    If you want a single render array as the sub item, still remember to
    enclose it in an array like so:

        $array['contents'] = array($subItem);
* `#callback`: An optional rendering override hook. `render()` will call this
    function if it is found.

All other values are parsed as arguments like so:

    $array['placeholder'] = "woot";
    $array['contents'] = "hellYeah";
    
Forgetting the # in contents leads to this:

    <div placeholder="woot" contents="hellYeah" />

Additionally, arguments that contain an array will have their contents split
by spaces before being added to the argument like so:

     $array['class'] = array("wow", "such-class", "very-array");

Will become...

     <div class="wow such-class very-array" />
