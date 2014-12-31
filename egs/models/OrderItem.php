<?php
class OrderItem extends DataObject {
	protected $defaultDisplayFields = array('price','quantity','in_stock');
	private $_options;
	function __construct() {
		parent::__construct('store_order_items');
		$this->hasOne('Product');
		$this->belongsTo('Order');
		$this->setAdditional('in_stock','bool');
	}

	public static function makeFromBasketItem(BasketItem $item,$order_id,&$errors) {
		$item_data=array();
		$item_data['order_id']=$order_id;
		$item_data['product_id']=$item->product_id;
		$item_data['quantity']=$item->quantity;
		$db=DB::Instance();
		$query = 'SELECT sum(price) FROM product_options po JOIN store_basket_item_options io ON (po.id=io.option_id) WHERE io.item_id='.$db->qstr($item->id);
		$mod_price = $db->GetOne($query);
		
		$price = $item->product->price + $mod_price;
		
		$item_data['price'] = $price;
		
		$item = OrderItem::Factory($item_data,$errors,'OrderItem');
		return $item;	
	}
	
	public function transferOptions($item_id) {
		$db=DB::Instance();
		$query = 'INSERT INTO store_order_item_options (item_id, option_id) (SELECT '.$this->id.', option_id FROM store_basket_item_options WHERE item_id='.$db->qstr($item_id).')';
		$db->Execute($query);
	}
	
	public function getProductName() {
		return @$this->product;
	}
	
	
	public function getOptions() {
		$db = DB::Instance();
		if(isset($this->_options)) {
			return $this->_options;
		}
		$query = 'SELECT poc.name, po.description FROM product_options po
			JOIN product_option_categories poc ON (po.category_id=poc.id) 
			JOIN store_order_item_options soio ON (soio.option_id=po.id)
			WHERE soio.item_id='.$db->qstr($this->id);
		$options=$db->GetAssoc($query);
		$this->_options=$options;
		return $options;
		
	}
	
}
?>
