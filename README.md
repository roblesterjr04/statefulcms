# StatefulCMS

What is StatefulCMS?

StatefulCMS is hybrid framework/cms that puts the power of the back end code, in to your front end interface. The objects engine allows a developer to build an ajax interface on a page, without touching javascript or building functions for every user control action in the interface.

## Installation

1. Copy all the files to your server.
2. go to your site + /install/ (so, http://yoursite.com/install/)
3. Enter the values in the fields, click Save

When you see "Done!" you can go to http://yoursite.com/admin/ and log in!

## Examples

```php
<?php

## my_plugin.php

class My_Plugin extends CP_Object {
  public function title() { // Returns the title of the module in places like the menu, or the parents admin() function
    return 'My Demo Plugin';
  }
  
  // Here's the magic!
  
  public function admin() {
    // This overrides the default admin interface (which is just a table of items... boring.)
    // We don't return anything here, we simply output to the screen.
    
    // Let's make our button! CP_Button($name, $text, $options, $owner)
    $button = new CP_Button('my_button', 'Don't hit the baby!', [], $this);
    
    // Out we go!
    $button->display(); // echos to the screen.
    
  }
  
}

root()->objects->add('My_Plugin');
```

Ok, so why was that so special? All we have to do to hook an action to this button is add this new function to the class.

```php
public function my_button_click($sender) {
  // Do something cool in the background!
  // Lets make a popup...
  root()->iface->alert('Why did you hit the baby?');
}
```
