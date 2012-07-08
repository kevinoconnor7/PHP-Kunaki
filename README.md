Kunaki PHP XMLService Class
===========================
Kunaki is an on-demand CD/DVD publishing service that is fully automated. One of the options to use the service is through their XML Webservice which this class interfaces with.

Requirements
------------

* PHP 5
* SimpleXML (libxml)
* Kunaki publisher account (Free at [Kunaki] (http://kunaki.com).)

How to Use
----------

To begin first initialize the class
	$Kunaki = new Kunaki('SantaClaus@Northpole.com','Rednose', 'TEST');

You'll then need to get the shipping options before you can proces an order. At minimum the following information must be given
	$order = new Kunaki_Order();
	$order->addProductId($ProductId, 10);
	$order->PostalCode = '10004';
	$order->Country = 'United States';
	$order->State_Province = 'NY';
	$shipping = $Kunaki->getShippingOptions($order);

With the returned data you can then pick the shipping option you wish to use. You'll then add it to the order object as well as the remaining required information
	$order->City = "New York";
	$order->Address1 = '215 Maple Street';
	$order->Name = "John Smith";
	$order->ShippingDescription = "USPS Priority Mail";
	$order_info = $Kunaki->processOrder($order);

The returned data will contain an OrderId which can then be used to find the status of the order
	$Kunaki->getOrderStatus('567129');

The class is fully documented if you would like to see the additional options. In addition, the naming conventions match the offical XMLService provided by Kunaki and can be found [http://kunaki.com/XMLService.htm] (here).

License
-------
This software is released under the GPLv3 license. The full license can be read [http://www.gnu.org/licenses/gpl-3.0.txt] (here).