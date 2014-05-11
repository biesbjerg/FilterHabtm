CakePHP FilterHabtmBehavior

1. Add Behavior to model
2. Start filtering right away


Product hasAndBelongsTo Category (join model CategoryProduct)
--------------------------------------
$this->Product->find('all', array(
	'conditions' => array(
		'Category.id' => 4
	)
));

The above is normally not possible. 

This behavior automatically detects when there's a condition to an HABTM assocation and creates the proper joins behind the scenes. 
