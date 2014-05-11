# CakePHP FilterHabtmBehavior

**Add Behavior to model**
```php
class Product extends AppModel {

	public $actsAs = array(
		'FilterHabtm'
	);

}
```

**How to use the behavior**
The following is normally not possible. 
```php

// Product hasAndBelongsTo Category (join model CategoryProduct)
$this->Product->find('all', array(
	'conditions' => array(
		'Category.id' => 4
	)
));
```

The behavior automatically detects conditions involving HABTM assocations and creates the proper joins behind the scenes.
