<?php
include('Kunaki.php');

//Create an instance of the Kunaki class giving the UserId, Password, and Mode (Default LIVE)
$Kunaki = new Kunaki('SantaClaus@Northpole.com','Rednose', 'TEST');

$ProductId = 'PXZZ111111';

//getView memember returns the url given the ProductId and the desire view
echo '<img src="'.$Kunaki->getView($ProductId, 'FO').'" />';

//To get shipping options create an order and give the required information
$order = new Kunaki_Order();
$order->addProductId($ProductId, 10);
$order->PostalCode = '10004';
$order->Country = 'United States';
$order->State_Province = 'NY';
print_r($Kunaki->getShippingOptions($order));

//To submit an order give a little extra information to submit the order
$order->City = "New York";
$order->Address1 = '215 Maple Street';
$order->Name = "John Smith";
$order->ShippingDescription = "USPS Priority Mail";
print_r($Kunaki->processOrder($order));

//To get the status of the order only the OrderId must be provided
print_r($Kunaki->getOrderStatus('567129'));