<?php
/*
*Ticket Model Unit Tests
* Defining tests:
* 1) Add test name in TestSuit Array and set the test on or off
* 2) create function with the same name as the test name
*/

$plugindir = plugin_dir_path( __FILE__ );

$DS=DIRECTORY_SEPARATOR;
$plugindir = dirname(dirname(plugin_dir_path( __FILE__ )));
include_once( $plugindir. $DS . "admin" . $DS."models".$DS."Tickets.php");

#
#####################################################################
echo "<br /><br /><br />";
echo "<b>TICKETS MODEL UNIT TESTS</b>";

#Test suit
######################################################################
$TestSuit = array(
'TicketInsert' => False,
'TicketDelete' => False,
'TicketUpdate' => False,
'TicketGetTicket' => True,
);


#TEST FUNCTIONS HERE
######################################################################
function TicketInsert(){
	#Load ticket model class file
	$TM = new TicketsModel();

	//Populate ticket model instalate
	$tO = new stdClass(); 
	$tO->tkt_subject    	 = "Title";
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

function TicketDelete(){
	#Load ticket model class file
	$TM = new TicketsModel();
	$tO = new stdClass(); 
	$tO->tkt_id = "5";
	$id = $TM->deleteTicket($tO);
	return ( $id > 0 ) ? True : False;
}

function TicketUpdate(){
	#Load ticket model class file
	$TM = new TicketsModel();

		//Populate ticket model instalate
	$tO = new stdClass(); 
	$tO->new_tkt_subject    	 = "Title Update Test";
	$tO->new_initial_message 	 = "Initial Message dasdfasdf";
	$tO->tkt_customer_rating = "1";
	$TM->updateTicket($tO);
	return true;
}

function TicketGetTicket(){
	$TM = new TicketsModel();

	$tO = $TM->getTicket( 6);
	print_r( $tO);
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
