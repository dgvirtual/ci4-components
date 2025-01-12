# Components for Codeigniter 4

![Build Status](https://github.com/dgvirtual/codeigniter4-components/actions/workflows/phpunit.yml/badge.svg)
![Coverage](https://codecov.io/gh/dgvirtual/codeigniter4-components/branch/develop/graph/badge.svg)

PHP library _Components for Codeigniter 4_ allows you to create custom HTML
elements to use within your views. They allow to encapsulate html and css
classes/styles into reusable website building blocks with the content and
attributes of your choosing. They are written in regular PHP/CSS/HTML. Such
custom HTML elements can include other HTML elements (custom or default).

Custom component tag always starts with `x-` (like
`<x-green-button>Click Me!</x-green-button>`).

To illustrate, with Components you write this in your view:

```php
<x-green-button onclick="alert('I was clicked!')">
   <?= $clickMeLabel ?>
</x-green-button>
```

Which, is merged with the Component definition:

```php
<button
  style="color: white; background-color: green;"
  <?= isset($onclick) ? 'onclick="' . $onclick . '"' : '' ?>
>
  <?= $slot ?>
</button>
```

And results in this in the browser:

```html
<button
  style="color: white; background-color: green;"
  onclick="alert('I was clicked!')"
>
  Click Me!
</button>
```

## Composer Installation

To install in an _existing composer project_, run in command line:

```bash
composer require dgvirtual/codeigniter4-components:dev-develop
```

## Manual Installation

Let's say you want to put the library into the `app/ThirdParty` directory.

1. Download and unzip the code, copy the `codeigniter4-components` folder to the
   `app/ThirdParty` directory.

2. To enable Codeigniter to find the library, edit the `app/Config/Autoload.php`
   file, add the Components library to the `$psr4` property:

```php
public $psr4 = [
  APP_NAMESPACE => APPPATH,
  'Dgvirtual\Components' => APPPATH . 'ThirdParty/codeigniter4-components/src', // this line
];
```

## Configuration

Edit the `app/Config/View.php` file. Add the following array element to the
`$decorators` property:

```php
public array $decorators = [
  'Dgvirtual\Components\Libraries\ComponentDecorator', // this line
];
```

## Try If It Works

**To check if it works with the example components**, put the string
`<x-green-button>This should look like a button</x-green-button>` in any of your
views and see if it renders as a button (if not, all you will see will be simple
text).

Now you can make some components of your own and put them in
`app/Views/Components` folder to make them usable in your app views.

## How To Write and Use Components

Example components of all three types listed below are available in the
`src/Components` folder of the project. They can be used immediately.

### Self-Closing Tag Components

At their most basic, components serve as dynamic templates that allow you to
reduce the typing in your application. This can help boil longer, complex
sections down to a single HTML tag. This is especially useful with CSS utility
frameworks like TailWind, or when using the utilities in Bootstrap 5, etc. Using
components in these situations allows you to keep the style info in one place
where making changes to one file changes every instance of the view throughout
the application.

To create a component, simply create a new view file within the
`app\Views\Components` directory or another place made accessible as described
in the installation step 4 above.

A simple avatar image component `avatar.php` might look something like this:

```php
<img
  src="<?= $src ?? '' ?>"
  class="rounded-circle shadow-4"
  style="width: <?= $width ?? '150px' ?>;"
  alt="<?= $alt ?? '' ?>"
/>
```

When using the component within a view, you would insert a tag with `x-`
prepended to the filename:

```php
<x-avatar src="<?= $userAvatarURL ?>" alt="<?= $userName ?>" />
```

Any attributes provided when you insert the component like this are made
available as variables within the component view. In this case, the `$src` and
`$alt` attributes are passed to the component view, resulting in the following
output:

```html
<img
  src="http://example.com/avatars/foo.jpg"
  class="rounded-circle shadow-4"
  style="width: 150px"
  alt="John Smith"
/>
```

### Components With Opening and Closing Tags

You can include the content within the opening and closing tags by inserting the
reserved `$slot` variable:

```php
<x-green-button onclick="alert('I was clicked!')">
  Click Me!
</x-green-button>
```

The component `green-button.php` would look like this:

```php
<button
  style="color: white; background-color: green;"
  <?= isset($onclick) ? 'onclick="' . $onclick . '"' : '' ?>
  type="<?= $type ?? 'submit' ?>"
>
  <?= $slot ?>
</button>
```

The rendered html would look like this:

```html
<button
  style="color: white; background-color: green;"
  onclick="alert('I was clicked!')"
  type="submit"
>
  Click Me!
</button>
```

### Controlled Components

Finally, you can create a class to add additional logic to the output. The file
must be in the same directory as the component view and should have a name that
is the PascalCase version of the filename, with 'Component' added to the end of
it.

A `famous-quotes` component would have a view called `famous-quotes.php` and a
controlling class called `FamousQuotesComponent.php`. The class must extend
`Dgvirtual\Component\Libraries\Component`. The only requirement is that you
implement a method called `render()`.

You would call it in one of the ways previously described.

See a usable basic example of `famous-quotes` component in the
`src/Components` folder.

It can be inserted into your project like this (self closing, no atributes):

```html
  <x-famous-quotes />
```

or self-closing tag with attributes:

```html
  <x-famous-quotes seconds="30" />
```

or a component with opening and closing tags:

```html
  <x-famous-quotes seconds="30">Famous Quotes</x-famous-quotes>
```

As you see, controlled components can  include both the content via `$slot` and 
html attributes.

## Advanced Configuration

Review the `src/Config/Components.php` file. The default configuration specifies
that the app will search for component files in two locations:

```php
public $componentsLookupPaths = [
  // your local components
  APPPATH . 'Views/Components/',
  // example components
  __DIR__ . '/../Components/',
];
```

If you want the app to find components located elsewhere, copy this file to your
`app/Config` directory, change the namespace of the copy to `namespace Config;`,
and edit the `$componentsLookupPaths` to list all the locations where you want
the app to look for components. The project will then use this config instead of
the library's own.

_Note:_ The _first component found_ in the lookup paths will be used. Therefore,
if you have custom components, list their paths first, and the default ones
last.

## Credits

This project is an adaptation of Bonfire2 Component rendering functionality for
general CodeIgniter 4 use. Bonfire2 was created by Lonnie Ezell
<lonnieje@gmail.com> and contributors. For more information, visit the
[Bonfire2 project](https://github.com/lonnieezell/Bonfire2).

The adaptation and package was created by Donatas Glodenis. You can reach out to
me at [dg@lapas.info] for any questions or feedback.

## License

This project is licensed under the MIT License. See the LICENSE file for
details.
