# CakePHP FilterHabtmBehavior

**1. Copy plugin to app/Plugin/FilterHabtm folder**

**2. Load plugin in your app/Config/bootstrap.php**

```php
CakePlugin::load('FilterHabtm')
```

**3. Add Behavior to model**

```php
class Product extends AppModel {

	public $actsAs = array(
		'FilterHabtm.FilterHabtm',
		'Containable' // If you do use containable it's very important to load it AFTER FilterHabtm
	);

}
```

**4. How to use the behavior**

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
