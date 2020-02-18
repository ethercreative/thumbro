# Thumbro
Craft CMS image transformations powered by Thumbor

## Configuration

Config options should be added to your `thumbro.php` file in your config 
folder.

When setting up Thumbor, we recommend using the 
`thumbor.loaders.file_loader_http_fallback` loader to account for files with and 
without public urls (see
[Image loader](https://thumbor.readthedocs.io/en/latest/image_loader.html)).

### domain [string]

The domain (including port) where Thumbor can be accessed.

### securityKey [string]

The security key defined in your Thumbor config file. See 
[Security](https://thumbor.readthedocs.io/en/latest/security.html).

### autoFocalPoint [bool]

*Default: `true`*

Will automatically set the focal point of the image to the one defined in Craft.
(See [Position](#position-stringarray))

### autoCompress [bool]

*Default: `true`*

Will automatically compress the resulting image.

### imageUrlModifier [callable|null]

*Default: `null`*

Gives you a change to modify the public URL of the image, before it is passed to 
Thumbor. This is especially useful when working in Docker:

```php
return [
    'imageUrlModifier' => function ($url) {
        // Replace the site's URL with the internal docker container name (web)
        return str_replace(getenv('DEFAULT_SITE_URL'), 'web', $url);
    },
];
```

### thumborUrlModifier [callable|null]

*Default: `null`*

Gives you a change to modify the URL thumbor returns when the plugin tries to 
access the file contents in the `picture` method. Works the same as 
`imageUrlModifier`.

## Usage

### `img(Asset $asset, array $transforms, array $config = [])`

Transform a single image to one or more sizes. The config array will be merged 
into every transform.

```twig
{% set img = craft.assets.one() %}
{% set transformedImage = craft.thumbro.img(img, {
    width: 100,
    height: 100,
}, {
    position: img.getFocalPoint(),
}) %}
```

```twig
{% set img = craft.assets.one() %}
{% set transformedImage = craft.thumbro.img(img, [
    { width: 100, height: 100, format: 'webp' },
    { width: 100, height: 100, format: 'jpg' },
]) %}
```

### `picture(Asset $asset, array $transform, array $config = [])`

Will output the image in a `picture` tag for dynamic lazy loading with a low 
quality placeholder. The function signature matches that of the `img` method, 
with the exception that `$transform` only accepts a single transform object, not 
an array of multiple objects.

This function will inject the necessary JavaScript to dynamically load the 
image. You can disable this by passing the `noJs` option to the `$config` and 
setting it to `true`.

```twig
{{ craft.thumbro.picture(img, { width: 1000, height: 1000 }, { aboveFold: true }) }}
```

#### Config Options

In addition to the regular transform config options, `picture` has two unique 
options: 

**`noJs`** [bool]

Will disable the automatic injection of the lazy loading JavaScript.

**`aboveFold`** [bool]

Will convert the lazy loading to eager loading when true, meaning the image will
instantly load in most cases.

#### CSS

You will need to include some CSS to ensure the picture tag appears correctly.
Below is what we recommend you use:

```css
picture {
    position: relative;
    z-index: 1;
    display: block;
    width: 100%;

    font-size: 0;
}

picture > img {
    position: absolute;
    top: 0;
    left: 0;

    width: 100%;
    height: auto;
    vertical-align: middle;
}

picture > img:first-child:last-child {
    position: relative;
}

picture > img:first-child {
    z-index: -1;
    height: 100%;
    object-fit: cover;
}

picture > img:last-child {
    z-index: 0;
    transition: opacity 0.15s ease;
}
```

## Transform Options

### format [string]

*Default: `null`*  
*Allowed values: `null`, `'jpg'`, `'png'`, `'gif'`, `'webp'`*

Format of the created image. If unset (default) it will be the same format as 
the source image.

### trim [bool|string]

*Default: `false`*

Removing surrounding space in images can be done using the trim option.

Unless specified trim assumes the top-left pixel color and no tolerance (more 
on tolerance below).

If you need to specify the orientation from where to get the pixel color, just 
set the value to `top-left` for the top-left pixel color or `bottom-right` for 
the bottom-right pixel color.

Trim also supports color tolerance. The euclidian distance between the colors of 
the reference pixel and the surrounding pixels is used. If the distance is 
within the tolerance they’ll get trimmed. For a RGB image the tolerance would 
be within the range 0-442.

### mode [string]

*Default: `crop`*  
*Allowed values: `crop`, `fit`, `stretch`*

**`crop`:** Crops the image to the given size, scaling the image to fill as much 
as possible of the size.  
**`fit`:** Scales the image to fit within the given size while maintaining the 
aspect ratio of the original image.  
**`stretch`:** Scales the image to the given size, stretching it if the aspect 
ratio is different from the original.

### width [int|string]

Width of the image, in pixels. 

If you omit this value or set it to 0, Thumbor will determine that dimension as 
to be proportional to the original image.

Set to `orig` to use the size of the original image. i.e. If the original image
has a width of 400px, the new image will also have that width.

A negative value will flip the image. Setting `'-0'` (in quotes) will flip the 
image without resizing it.

### height [int|string]

Height of the image, in pixels. 

If you omit this value or set it to 0, Thumbor will determine that dimension as 
to be proportional to the original image.

Set to `orig` to use the size of the original image. i.e. If the original image
has a height of 400px, the new image will also have that height.

A negative value will flip the image. Setting `'-0'` (in quotes) will flip the 
image without resizing it.

### ratio [int|float]

An aspect ratio (width/height) that is used to calculate the missing size, if
width or height is not provided.

```twig
{ ratio: 16/9 }
```

### effects [array]

A keyed array of effects to perform on the image, where the key is the name of 
the effect and the value contains the arguments for the effect. See 
[Effects](#effects) below.

### position [string|array]

The position around which to crop. Can be a string containing % locations 
`20% 65%`, or named locations `middle-right`, or an array of x/y decimal 
locations `['x' => 0.2, 'y' => 0.65]`.

### smart [bool]

Will use Thumbor's smart focal point detection when cropping the image.

### upscale [bool]

*Default: `false`*

Will upscale the image to fit the given size if true.

## Effects

### autojpg [bool]

Will convert non-transparent PNG images to JPG when `true`.

### backgroundColor [string]

Sets the background layer to the specified color. This is specifically useful 
when converting transparent images (PNG) to JPEG.

The value should be the color name (like in HTML) or hexadecimal rgb expression 
without the “#” character (see [Web colors](https://en.wikipedia.org/wiki/Web_colors)
for example). If color is `auto`, a color will be smartly chosen (based on the 
image pixels) to be the filling color.

### blur [int|array]

Applies a gaussian blur to the image.

Accepts a single integer as the `radius` of the blur, or an array matching 
`[radius, sigma]`.

- `radius` is used in the gaussian function to generate a matrix, maximum value
is 150. The bigger the radius more blurred will be the image.
- `sigma` is optional and defaults to the same value as the `radius`. Sigma used
in the gaussian function.

### brightness [int]

Increases or decreases the image brightness.

Accepts an integer from -100 to 100. The amount (in %) to change the image 
brightness. Positive numbers make the image brighter and negative numbers make 
the image darker.

### contrast [int]

Increases or decreases the image contrast.

Accepts an integer from -100 to 100. The amount (in %) to change the image 
contrast. Positive numbers increase contrast and negative numbers decrease 
contrast.

### convolution [array]

Runs a convolution matrix (or kernel) on the image. See 
[Kernel (image processing)](https://en.wikipedia.org/wiki/Kernel_(image_processing)) 
for details on the process. Edge pixels are always extended outside the image 
area.

Accepts an array of arguments `[matrix_items, number_of_columns, should_normalize]`.

- `matrix_items` Semicolon separated matrix items
- `number_of_columns` Number of columns in the matrix
- `should_normalize` Whether or not we should divide each matrix item by the sum
of all items

Example:

```
-1 -1 -1
-1  8 -1
-1 -1 -1
```

```twig
{{ craft.thumbro.img(img, {
    effects: {
        convolution: ['-1;-1;-1;-1;8;-1;-1;-1;-1', 3, false],
    },
}) }}
```

### equalize [bool]

Equalizes the color distribution in the image.

### fill [string|array]

Will return an image sized exactly as requested wherever is its ratio by filling 
with chosen color the missing parts. Only works when `mode` is set to `fit`.

Accepts a string `color` or an array `[color, fill_transparent]`.

- `color` Accepts:
  - The color name (like in HTML) or hexadecimal RGB expression without 
  the “#” character (see [Web colors](https://en.wikipedia.org/wiki/Web_colors) 
  for example).
  - If color is “transparent” and the image format, supports transparency the 
  filling color is transparent.
  - If color is “auto”, a color is smartly chosen (based on the image pixels) as
   the filling color.
  - If color is “blur”, the missing parts are filled with blurred original image. 
- `fill_transparent` Specify whether transparent areas of the image should be 
filled or not. Accepted values are either true, false, 1 or 0. This argument is 
optional and the default value is false.

### grayscale [bool]

Changes the image to grayscale.

### maxBytes [int]

Automatically degrades the quality of the image until the image is under the 
specified amount of bytes.

### noise [int]

Adds noise to the image. Accepts a value between 0 - 100 as the amount (in %) of 
noise to add to the image.

### proportion [float]

Applies proportion to height and width passed for cropping. Accepts a float 
between 0 - 1 as the percentage of the proportion (i.e. 0.5 would scale the 
image down to 50% after cropping).

### quality [int]

Set the overall quality of a JPEG image (does nothing for PNGs or GIFs). Expects
a value between 0 - 100 as the quality level (in %).

### rgb [array]

Will change the amount of color in each of the three channels.

Accepts an array of RGB values `[r, g, b]` ranging from -100 to 100.

### rotate [int]

Rotate the given image according to the angle value passed. Should be set to an 
angle between 0 - 359. Numbers greater or equal than 360 will be transformed to 
a equivalent angle between 0 and 359.

### sharpen [array]

Enhances apparent sharpness of the image. It’s heavily based on Marco Rossini’s 
excellent Wavelet sharpen GIMP plugin.

Accepts an array with the following values:
`[sharpen_amount, sharpen_radius, luminance_only]`

- `sharpen_amount` - Sharpen amount. Typical values are between 0.0 and 10.0.
- `sharpen_radius` - Sharpen radius. Typical values are between 0.0 and 2.0.
- `luminance_only` - Sharpen only luminance channel. Values can be `true` or `false`
