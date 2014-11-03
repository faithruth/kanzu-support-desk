<?php
/*
*Ticket Model Unit Tests
* Defining tests:
* 1) Add test name in TestSuit Array and set the test on or off
* 2) create function with the same name as the test name
*/

 
include_once( KSD_PLUGIN_DIR . "includes/models/Tickets.php" );

#
#####################################################################
echo "<br /><br /><br />";
echo "<b>TICKETS MODEL UNIT TESTS</b>";

#Test suit
######################################################################
$TestSuit = array(
'TicketInsert' => FALSE,
'TicketDelete' => False,
'TicketUpdate' => False,
'TicketGetTicket' => False,
);


#TEST FUNCTIONS HERE
######################################################################
function TicketInsert(){
	#Load ticket model class file
	$TM = new TicketsModel();

	//Populate ticket model instalate
	$tO = new stdClass(); 
	$tO->tkt_subject    	 = "Title";
	$tO->tkt_message_excerpt 	 = "Initial Message";
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
	$tO->new_tkt_message_excerpt 	 = "Initial Message dasdfasdf";
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
