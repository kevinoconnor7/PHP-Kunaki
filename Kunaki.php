<?php

/**
 * Kunaki Class
 * 
 * This library provides access to Kunaki's XML service using simple
 * logical interfaces to do so.
 * 
 * @author Kevin O'Connor <kevin@oconnor.mp>
 * @version 1.0
 * @package Kunaki
 * @license http://www.gnu.org/licenses/gpl-3.0.txt GPLv3
 */

class Kunaki
{
	/**
	 * Kunaki UserId
	 * 
	 * @access private
	 * @var string
	 */
	private $UserId;

	/**
	 * Kunaki Password
	 * 
	 * @access private
	 * @var string
	 */
	private $Password;

	/**
	 * The Mode to Process Order In
	 * Can either be "TEST" or "LIVE"
	 * 
	 * @access private
	 * @var string
	 */
	private $Mode;

	/**
	 * Create an instance and set UserId and Password
	 * 
	 * @param string $UserId A string containing the UserId of the Kunaki user
	 * @param string $Password A string containing the passowrd of the Kunaki user	
	 * @param string $Mode optional Either "LIVE" or "TEST" depending on the mode to function in
	 */
	public function __construct($UserId, $Password, $Mode = "LIVE")
	{
		if(empty($UserId) || empty($Password))
		{
			throw new Exception("Kunaki UserId/Password invalid");
		}

		$this->UserId = $UserId;
		$this->Password = $Password;
		$this->Mode = $Mode;
	}

	/**
	 * Request shipping rates for a prospective order
	 * 
	 * @access public
	 * @param object $Order Kunaki_Order object
	 * @return object Response containing shipping information
	 * @returnf int ErrorCode Zero is successfull or > 1 if error
	 * @returnf string ErrorText Description of the error, or "success" if none
	 * @returnf array Option Lists the possible shipping options as:
	 * 		string Description Delivery option description. (E.g., "USPS First Class Mail", "UPS Ground")
	 * 		string DeliveryTime Estimated delivery time for this shipping option. (E.g., "2-5 days")
	 * 		string Price The price of shipping with this option. (This is the price of shipping only)
	 */
	public function getShippingOptions($Order)
	{
		if(!is_object($Order))
		{
			return false;
		}

		$request = "<ShippingOptions>";
		$request .= "<Country>".$Order->Country."</Country>";
		$request .= "<State_Province>".$Order->State_Province."</State_Province>";
		$request .= "<PostalCode>".$Order->PostalCode."</PostalCode>";
		foreach($Order->Products as $product)
		{
			$request .= "<Product>";
			$request .= "<ProductId>".$product->ProductId."</ProductId>";
			$request .= "<Quantity>".$product->Quantity."</Quantity>";
			$request .= "</Product>";
		}
		$request .= "</ShippingOptions>";

		return $this->callServer($request);
	}

	/**
	 * Process an order for the given order
	 * 
	 * @access public
	 * @param object $Order Kunaki_Order object
	 * @return object Response containing order information
	 * @returnf int ErrorCode Zero is successfull or > 1 if error
	 * @returnf string ErrorText Description of the error, or "success" if none
	 * @returnf int OrderId The OrderId of the submitted order (00000 is in TEST mode)
	 */
	public function processOrder($Order)
	{
		if(!is_object($Order))
		{
			return false;
		}

		$request = "<Order>";
		$request .= "<UserId>".$this->UserId."</UserId>";
		$request .= "<Password>".$this->Password."</Password>";
		$request .= "<Mode>".$this->Mode."</Mode>";
		$request .= "<Name>".$Order->Name."</Name>";
		$request .= "<Company>".$Order->Company."</Company>";
		$request .= "<Address1>".$Order->Address1."</Address1>";
		$request .= "<Address2>".$Order->Address2."</Address2>";
		$request .= "<City>".$Order->City."</City>";
		$request .= "<Country>".$Order->Country."</Country>";
		$request .= "<State_Province>".$Order->State_Province."</State_Province>";
		$request .= "<PostalCode>".$Order->PostalCode."</PostalCode>";
		$request .= "<ShippingDescription>".$Order->ShippingDescription."</ShippingDescription>";
		foreach($Order->Products as $product)
		{
			$request .= "<Product>";
			$request .= "<ProductId>".$product->ProductId."</ProductId>";
			$request .= "<Quantity>".$product->Quantity."</Quantity>";
			$request .= "</Product>";
		}
		$request .= "</Order>";

		return $this->callServer($request);
	}

	/**
	 * Lookup the status of an order
	 * 
	 * @access public
	 * @param int $OrderId The order id provided after processing the order
	 * @return object Response containing order status information
	 * @returnf int ErrorCode Zero is successfull or > 1 if error
	 * @returnf string ErrorText Description of the error, or "success" if none
	 * @returnf int OrderId The OrderId of the submitted order
	 * @returnf string TrackingType If the order is shipped with a trackable delivery service it 
	 * 	will state the delivery service (UPS, Fedex). If the order is delivered with a non-trackable 
	 * 	service then this element will be 'NA'
	 * @returnf string TrackingId If the element for TrackingId is not equal to 'NA' then this element 
	 * 	contains the proprietary tracking id.
	 */
	public function getOrderStatus($OrderId)
	{
		$request = "<OrderStatus>";
		$request .= "<UserId>".$this->UserId."</UserId>";
		$request .= "<Password>".$this->Password."</Password>";
		$request .= "<OrderId>".$OrderId."</OrderId>";
		$request .= "</OrderStatus>";

		return $this->callServer($request);
	}

	/**
	 * Get an image of a product
	 * 
	 * @access public
	 * @param string $ProductId the Product ID of the item
	 * @param string $view optional a two character string of the view you want:
	 * 		Front (Default): FO
	 * 		Back: BO
	 * 		f-spine: FS
	 * 		b-spine: BS
	 * 		Left Inside: LI
	 * 		Right Inside: RI
	 * 		Box Shot: BX
	 * @return string The URL of the image of the view
	 */						
	public function getView($ProductId, $view = 'FO')
	{
		return 'http://kunaki.com/ProductImage.ASP?T=I&ST='.$view.'&PID='.$ProductId;
	}

	/**
	 * Perform the XML Request to Kunaki
	 * 
	 * @access private
	 * @param string $XMLRequest The full XML request without the XML definition
	 * @return object The result of the XML request
	 */
	private function callServer($XMLRequest)
	{
		$request = '<?xml version="1.0"?>';
		$request .= $XMLRequest;

		$ch = curl_init("https://Kunaki.com/XMLService.ASP");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $XMLRequest);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$return_xml = curl_exec($ch);
		curl_close($ch);

		$return_xml = preg_replace(array('/<BODY>/i','/<HTML>/i','/\r/','/\n/'), '', $return_xml);

		return new SimpleXMLElement('<?xml version="1.0"?>'.$return_xml);
	}
}

/**
 * Kuanki_Order Class
 * 
 * Define an order for use with the main API.
 */
class Kunaki_Order
{
	/**
	 * $Name - The recipient's name.
	 * 
	 * @access public
	 * @var string
	 */
	public $Name;

	/**
	 * $Company - The recipient's company or organization name. This element can be left empty.
	 * 
	 * @access public
	 * @var string
	 */
	public $Company;

	/**
	 * $Address1 - The recipient's street address.
	 * 
	 * @access public
	 * @var string
	 */
	public $Address1;

	/**
	 * $Address2 - The recipient's secondary address. (E.g., Apt number) This element can be left empty.
	 * 
	 * @access public
	 * @var string
	 */
	public $Address2;

	/**
	 * $City - The recipient's city.
	 * 
	 * @access public
	 * @var string
	 */
	public $City;

	/**
	 * $State_Province - The recipient's state or province. If the Country is United States or Canada
	 *  this element must be a two character abbreviation. Otherwise it can be left blank.
	 * 
	 * @access public
	 * @var string
	 */
	public $State_Province;

	/**
	 * $PostalCode - The recipient's zip or postal code.
	 * 
	 * @access public
	 * @var string
	 */
	public $PostalCode;

	/**
	 * $Country - The recipient's country. Select a country from the list below. For the USA use "United States".
	 * 
	 * @access public
	 * @var string
	 */
	public $Country;

	/**
	 * $ShippingDescription - The shipping description selected from the options retrieved in getShippingOptions().
	 * 
	 * @access public
	 * @var string
	 */
	public $ShippingDescription;

	/**
	 * Products - array of Kunaki_Product Objects
	 * 
	 * @access public
	 * @var array
	 */
	public $Products = array();

	/**
	 * Add a Kunaki_Product object to the order
	 * 
	 * @access public
	 * @param object $Product the Kunaki_Product object to add
	 */		
	public function addProduct($Product)
	{
		$this->Products[] = $Product;
	}

	/**
	 * Create Kunaki_Product object and add to the order
	 * 
	 * @access public
	 * @param string $ProductId The ProductId to add
	 * @param int $Quantity The Amount of the product to add
	 */	
	public function addProductId($ProductId, $Quantity = 1)
	{
		$this->Products[] = new Kunaki_Product($ProductId, $Quantity);
	}
}

/**
 * Kunaki_Product class
 * 
 * Define products for use with the main API.
 */
class Kunaki_Product
{
	/**
	 * Product Id
	 * 
	 * @access public
	 * @var string
	 */
	public $ProductId;

	/**
	 * Quantity of product
	 * 
	 * @access public
	 * @var int
	 */
	public $Quantity;

	/**
	 * Create an instance of Kunaki_Product and set ProductId and Quantity
	 * 
	 * @param string $ProductId a string of the Product's ID
	 * @param int $Qunatity optional an int of the amount of the product
	 */
	public function __construct($ProductId, $Quantity = 1)
	{
		$this->ProductId = $ProductId;
		$this->Quantity = $Quantity;
	}

	/**
	 * Get an image of the product
	 * 
	 * @access public
	 * @param string $view optional a two character string of the view you want:
	 * 		Front (Default): FO
	 * 		Back: BO
	 * 		f-spine: FS
	 * 		b-spine: BS
	 * 		Left Inside: LI
	 * 		Right Inside: RI
	 * 		Box Shot: BX
	 * @return string The URL of the image of the view
	 */						
	public function getView($view = 'FO')
	{
		return 'http://kunaki.com/ProductImage.ASP?T=I&ST='.$view.'&PID='.$this->ProductId;
	}


}