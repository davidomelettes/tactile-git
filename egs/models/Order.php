<?php
class Order extends DataObject {

	protected $defaultDisplayFields = array('customer'=>'Customer','status'=>'Status');

	function __construct() {
		parent::__construct('store_orders');
		$this->hasMany('OrderItem','items');
		$this->belongsTo('Customer','customer_id','customer');
		$this->belongsTo('Shippingoption','shippingoption_id','shipping_option');
		$this->belongsTo('Country','billing_countrycode','billing_country');
		$this->belongsTo('Country','shipping_countrycode','shipping_country');
		$this->identifierField = 'id';
	}
	/**
	 * To move the items from the current order to a different one
	 * To move the contents of $b to $a:
	 * $b->moveItems($a);
	 */
	function moveItems(Order $order) {
		foreach($this->items as $item) {
			$item->order_id=$order->id;
			$item->save();
		}
	}
	
	
	/**
	 * To add an item to a order.
	 * If the order already contains a line with the product, it will instead increase the quantity
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
			$data['order_id']=$this->id;
			$item=OrderItem::Factory($data,$errors=array(),'OrderItem');
			$item->save();
		}
	}
	
	function total($formatter=null) {
		$total=0;
		foreach($this->items as $item) {
			$total+=($item->quantity*$item->price);
		}
		$db=DB::Instance();
		$query = 'SELECT SUM(price) FROM store_order_extras e  RIGHT JOIN store_order_selected_extras se ON (e.id=se.extra_id) WHERE se.order_id='.$db->qstr($this->id);
		$extras = $db->GetOne($query);
		$total+=$extras;
		if($formatter==null) {
			$formatter = new PriceFormatter();
		}
		return $formatter->format($total);
	}
	
	function num_items() {
		$total=0;
		foreach($this->items as $item) {
			$total+=$item->quantity;
		}
		return $total;
	}
	
	public function getDeliveryOption() {
		$db = DB::Instance();
		$query = 'SELECT e.* FROM store_order_extras e RIGHT JOIN store_order_selected_extras se ON (e.id=se.extra_id) WHERE shipping AND se.order_id='.$this->id;
		return $db->GetRow($query);
	}

	static function makeFromBasket(Basket $basket,&$errors) {
		$order_data=array();
		$order_data['customer_id']=$basket->customer_id;
		$order_data['status']='new';
		
		$customer=new Customer();
		$customer->load($basket->customer_id);

		$person = new Person();
		$person->load($customer->person_id);


		$billing_address = $customer->getBillingAddress();
		$shipping_address = $customer->getShippingAddress();
		$types=array('shipping','billing');
		$fields=array('name','street1','street2','street3','town','county','postcode','countrycode');
		foreach($types as $type) {
			foreach($fields as $fieldname) {
				$value=${$type.'_address'}->$fieldname;
				if($fieldname=='name') {
					if(strtolower($value)=='main') {
						$order_data[$type.'_firstname']=$person->firstname;
						$order_data[$type.'_surname']=$person->surname;
					}
					else {
						$split=explode(' ',$value);
						$firstname=$split[0];
						$surname=$split[1];
						$order_data[$type.'_firstname']=$firstname;
						$order_data[$type.'_surname']=$surname;
					}
				}
				else {
					$order_data[$type.'_'.$fieldname] = $value;
				}
			}
		}
		$order_data['email']=$person->email;
		$order_data['currency']='GBP';
		$db=DB::Instance();
		$db->StartTrans();
		$query = 'DELETE FROM store_orders WHERE status=\'new\' AND customer_id='.$db->qstr($customer->id);
		$db->Execute($query);
		$order = Order::Factory($order_data,$errors,'Order');
		if($order!==false) {
			$order->save();
			foreach($basket->items as $item) {
				$order_item = OrderItem::MakeFromBasketItem($item,$order->id,$errors);
				if($order_item!==false) {
					$order_item->save();
					$order_item->transferOptions($item->id);
				}
			}
			
			//and add shipping if appropriate
			$delivery_item = $basket->getDeliveryOption();
			$selected_extra = new DataObject('store_order_selected_extras');
			$selected_extra->id = $db->GenID('store_order_selected_extras_id_seq');
			$selected_extra->usercompanyid = EGS_COMPANY_ID;
			$selected_extra->extra_id = $delivery_item['id'];
			$selected_extra->quantity=1;
			$selected_extra->order_id = $order->id;
			$selected_extra->save();
		}
		$db->CompleteTrans();
		return $order;
	}

}


?>
