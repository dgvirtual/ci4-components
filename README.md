# Components

Codeigniter 4 library Components allows you to create custom HTML elements to use within your views. Components
work much like the Blade Components, only without a custom templating language. To write them you should use
regular PHP/CSS/HTML.

## Installation (thus far only manual)

1. Copy the `Components` folder to the `app/ThirdParty` directory.

2. Edit the `app/Config/Autoload.php` file, add the Components library to the `$psr4` property:

```php
    public $psr4 = [
        APP_NAMESPACE => APPPATH,
        'dgvirtual\Components' => APPPATH . 'ThirdParty\Components\src', // this line
    ];
```

3. Edit the `app/Config/View.php` file. Add the following array element to the `$decorators` property:

```php
    public array $decorators = [
        'dgvirtual\Components\Libraries\ComponentDecorator', // this line
    ];
```

4. Review the `app/ThirdParty/Components/src/Config/Components.php` file. The default configuration specifies that the app
   will search for component files in two locations:

```php
    public $componentsLookupPaths = [
        APPPATH . 'Views/Components/',
        APPPATH . 'ThirdParty/Components/src/Views/Components/',
    ];
```

If you want the app to find components located elsewhere, copy this file to your `app/Config` directory, change
the namespace of the copy to `namespace Config;`, and edit the `$componentsLookupPaths` to list all the locations where
you want the app to look for components. The project will then use this config instead of the library's own.

_Note:_ The first component found in the lookup paths will be used. Therefore, if you have custom components, list their paths
first, and the default ones last.

**To check if it works**, put the string `<x-button>This should look like a button</x-button>` in any of your views
and see if it renders as a button. If it does, then the component rendering is working correctly.

## How To Write and Use Components

Example components of all three types listed below are available in the `src/View/Components` folder of
the project. They can be used immediately.

### Self-Closing Tag Components

At their most basic, components serve as dynamic templates that allow you to reduce the typing in your
application. This can help boil longer, complex sections down to a single HTML tag. This is especially
useful with CSS utility frameworks like TailWind, or when using the utilities in Bootstrap 5, etc. Using
components in these situations allows you to keep the style info in one place where making changes to
one file changes every instance of the view throughout the application.

To create a component, simply create a new view file within the `app\Views\Components` directory or another place made
accessible as described in the installation step 4 above.

A simple avatar image might look something like this:

```php
// app/Views/Components/avatar.php
<img
  src="<?= $src ?? '' ?>"
  class="rounded-circle shadow-4"
  style="width: <?= $width ?? '150px' ?>;"
  alt="<?= $alt ?? '' ?>"
/>
```

When using the component within a view, you would insert a tag with `x-` prepended to the filename:

```php
<x-avatar src="<?= $user->avatarUrl() ?>" alt="<?= $user->name ?>" />
```

Any attributes provided when you insert the component like this are made available as variables within
the component view. In this case, the `$src` and `$alt` attributes are passed to the component view, resulting
in the following output:

```html
<img
  src="http://example.com/avatars/foo.jpg"
  class="rounded-circle shadow-4"
  style="width: 150px"
  alt="John Smith"
/>
```

### Components With Opening and Closing Tags

You can include the content within the opening and closing tags by inserting the reserved `$slot` variable:

```php
<x-green-button>Click Me!</x-green-button>
```

```php
// app/Views/Components/green-button.php
<button class="btn btn-success">
    <?= $slot ?>
</button>
```

### Controlled Components

Finally, you can create a class to add additional logic to the output. The file must be in the same directory
as the component view and should have a name that is the PascalCase version of the filename, with 'Component'
added to the end of it.

A `famous-quotes` component would have a view called `famous-quotes.php` and a controlling class called
`FamousQuotesComponent.php`. The class must extend `Dgvirtual\Component\Libraries\Component`. The only
requirement is that you implement a method called `render()`. 

You would call it in one of the ways previously described.

See a usable basic example of `famous-quotes` component in the `src/Views/Components` folder.

## Credits

This project is an adaptation of Bonfire2 Component rendering functionality for general CodeIgniter 4 use.
For more information, visit the [Bonfire2 project](https://github.com/lonnieezell/Bonfire2).

The adaptation and package was created by Donatas Glodenis. You can reach out to me at [dg@lapas.info] for any questions or feedback.

## License

This project is licensed under the MIT License. See the LICENSE file for details.
