<?php
class Basket extends DataObject {

	function __construct() {
		parent::__construct('store_baskets');
		$this->hasMany('BasketItem','items');
		$this->hasOne('Customer');
	}

	/**
	 * To move the items from the current basket to a different one
	 * To move the contents of $b to $a:
	 * $b->moveItems($a);
	 */
	function moveItems(Basket $basket) {
		foreach($this->items as $item) {
			$item->basket_id=$basket->id;
			$item->save();
		}
	}
	
	
	/**
	 * To add an item to a basket.
	 * If the basket already contains a line with the product, it will instead increase the quantity
	 *
	 */
	function addItem($data) {
		$index=$this->items->contains('product_id',$data['product_id']);
		if($index!==false) {
			$item=$this->items->getContents($index);
			$item->quantity=$item->quantity+$data['quantity'];
			if($item->quantity<=0) {
				$item->delete();
			}
			else {
				$item->save();
			}
		}
		else {
			$data['basket_id']=$this->id;
			$item=BasketItem::Factory($data,$errors=array(),'BasketItem');
			$item->save();
		}
	}
	
	/**
	 * Let the class handle how the basket should be loaded
	 * - if the customer is logged in and they have a basket then use it
	 * - 
	 */
	public static function &Instance($type=null) {
		static $basket;
		if($basket==null) {
			$basket=new Basket();
			$result=false;
			if(isset($_SESSION['customer_id'])) {
				$result=$basket->loadBy('customer_id',$_SESSION['customer_id']);
			}
			if(!$result) {
				//see if there's already a basket
				$result=$basket->loadBy('sessionid',session_id());
				if($result===false) {
					if('session'&&isset($_SESSION['customer_id'])) {
						$data=array(
							'customer_id'=>$_SESSION['customer_id']
						);
					}
					else {
						$data=array(
							'sessionid'=>session_id()					
						);
					}
					$errors=array();
					$basket=Basket::Factory($data,$errors,'Basket');
					$basket->save();
				}
			}
		}
		return $basket;
	}
	
	function total() {
		$total=0;
		foreach($this->items as $item) {
			$total+=($item->quantity*$item->product->price);
		}
		return $total;
	}
	
	function num_items() {
		$total=0;
		foreach($this->items as $item) {
			$total+=$item->quantity;
		}
		return $total;
	}
	
}


?>