<?php
/*
*Ticket Model Unit Tests
* Defining tests:
* 1) Add test name in TestSuit Array and set the test on or off
* 2) create function with the same name as the te
*/

$plugindir = plugin_dir_path( __FILE__ );

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include( $plugindir. $DS . "admin" . $DS."models".$DS."Tickets.php");

#
#####################################################################
echo "<br /><br /><br />";
echo "<b>TICKETS MODEL UNIT TETS</b>";

#Test suit
######################################################################
$TestSuit = array(
'TicketInsert' => True
);


#TEST FUNCTIONS HERE
######################################################################
function TicketInsert(){
	#Load ticket model class file
	$TM = new TicketsModel();

	//Populate ticket model instalate
	$tO = new stdClass(); 
	$tO->tkt_title    	 = "Title";
	$tO->initial_message 	 = "Initial Message";
	$tO->tkt_description 	 = "NEW TICKET";
	$tO->tkt_channel     	 = "EMAIL";
	$tO->tkt_status 	 	 = "OPEN";
	$tO->tkt_private_notes 	 = "Private notes";
	$tO->tkt_tags 	 		 = "tag";
	$tO->tkt_customer_rating = "1";

	//Add to db, should return id
	$id = $TM->addTicket( $tO);
	
	return ( $id > 0 ) ? True : False;
}



#Run Test Suit
##################################################################
echo "<br />";
//Run tests
foreach ( $TestSuit as $test => $flag ){
	if ( $flag == True ){
		echo "<br /><b>Test name: $test</b><br />";
		echo "<hr /></ br>";
		echo ( $test() == True ) ? "STATUS: OK" : "STATUS: FAILED"; 
		echo "";
	}
}





?>
