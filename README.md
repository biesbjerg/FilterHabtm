# CakePHP 2.x FilterHabtmBehavior

**1. Copy plugin to app/Plugin/FilterHabtm folder**

**2. Load plugin in your app/Config/bootstrap.php**

```php
CakePlugin::load('FilterHabtm')
```

**3. Add Behavior to model**

```php
<?php
class Product extends AppModel {

	public $actsAs = array(
		'FilterHabtm.FilterHabtm',
		'Containable' // If you use containable it's very important to load it AFTER FilterHabtm
	);

	public $hasAndBelongsToMany = array(
		'Category' => array(
			'className' => 'Category',
			'foreignKey' => 'product_id',
			'associationForeignKey' => 'category_id',
			'with' => 'CategoryProduct'
		)
	);

}

```

**4. How to use the behavior**

The following is normally not possible. 
```php
<?php
class ProductsController extends AppController {

	public $name = 'Products';

	public function index($categoryId = null) {
		$products = $this->Product->find('all', array(
			'conditions' => array(
				'Category.id' => $categoryId
			)
		));
	}
	
}

```

The behavior automatically detects conditions involving HABTM assocations and creates the proper joins behind the scenes.
