# CakePHP FilterHabtmBehavior

1. Add Behavior to model

```php
class Product extends AppModel {

	public $actsAs = array(
		'FilterHabtm
	);

}
```

2. Use the behavior

```php

// Product hasAndBelongsTo Category (join model CategoryProduct)
$this->Product->find('all', array(
	'conditions' => array(
		'Category.id' => 4
	)
));
```

The above is normally not possible. 

This behavior automatically detects when there's a condition involving an HABTM assocation and creates the proper joins behind the scenes.
