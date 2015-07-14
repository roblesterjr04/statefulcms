# StatefulCMS

What is StatefulCMS?

StatefulCMS is hybrid framework/cms that puts the power of the back end code, in to your front end interface. The objects engine allows a developer to build an ajax interface on a page, without touching javascript or building functions for every user control action in the interface.

## Installation

1. Copy all the files to your server.
2. go to your site + /install/ (so, http://yoursite.com/install/)
3. Enter the values in the fields, click Save

When you see "Done!" you can go to http://yoursite.com/admin/ and log in!

## Examples

In the plugins directory, create a folder called my_plugin, and in it, a file named my_plugin.php

```php
<?php

## my_plugin.php

class My_Plugin extends CP_Object {

  public function __construct() {
    parent::__construct('My_Plugin');
  }

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
  
  ## The state manager will do an ajax call and look for a function titled my_button_click
  ## The function name is a combination of the control name (my_button) and the event that was fired (click)
  ## most javascript events are handled, such as mouseenter, hover, mouseout, change, keyup .. etc.
  
  public function my_button_click($sender) {
    // Do something cool in the background!
    // Lets make a popup...
    root()->iface->alert('Why did you hit the baby?');
  }
  
}

root()->objects->add('My_Plugin'); // Tell the root to load this object on initialization
```

## Binding events

Some controls have default events bound to them. Buttons have click, text boxes have keyup, and so on. But we want to bind more!

```php
$button = new CP_Button('my_button', 'Don't hit the baby!', [], $this);

$button->bind('mouseout');
$button->bind('hover');
```

Thats pretty nice, right? Now you can write functions like my_button_mouseout() or my_button_hover(), and they'll get fired when the event happens on the front end.

```php
$textbox = new CP_TextField('my_text_field', '', [], $this);

$textbox->bind('change')->bind('click')->unbind('keyup');
```

See what we did there? We bound change and click to the textbox, and unbound keyup, all on one line of code!

## Custom Controls

Want to make a custom control? Easy! Just extend the CP_Control class. There's lots of things to override or extend.

The $options variable is parsed as the control tags attributes. So, $options['type']='email' will output type="email" in the html.

This will basically create a textbox, but it will be of type "email" instead of type "text." (The default markup basically only outputs a standard html input tag with attributes)

```php

class Email_Control extends CP_Control {
	
	public function __construct($name, $text, $options, $owner) { 
		// At the very minimum, you need to pass an owner in to the control. 
		// We need an object to associate it with, and that object must be a subclass of CP_Object
		
		// Add/override some options in the options array
		
		$options['type'] = 'email';
		$options['value'] = $text;
		
		// We also have to run the parent's construct.
		parent::__construct($name, $options, $owner);
		
	}
	
}

```

Lets use it! Add this to the admin function of the object class.

```php

$email_address_field = new Email_Control('my_email', '', ['class'=>'text-box-class', 'placeholder'=>'Enter Email Address'], $this);

$email_address_field->display();

## or

echo $email_address_field->control(); // Basically the same as display(), but returns control markup rather than echoing to screen.


```

Maybe we want a control with custom markup.

```php

class Special_Control extends CP_Control {
	
	public function __construct($name, $options, $owner) {	
		parent::__construct($name, $options, $owner);
	}
	
	// We want custom markup!
	
	public function markup() {
		
		$atts = $this->atts();
		$output = "<div $atts>My custom control output!</div>";
		
		return $output;
		
	}
	
}

```