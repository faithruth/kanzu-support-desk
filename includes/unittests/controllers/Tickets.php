<?php
/*
* Ticket Controller Unit Tests
* Defining tests:
* 1) Add test name in TestSuit Array and set the test on or off
* 2) create function with the same name as the test name
*/

/*Includes removed. All done by do_admin_includes() in Kanzu_Support_Admin*/

#
#####################################################################
echo "<br /><br /><br />";
echo "<b>TICKETS CONTROLLER UNIT TESTS</b>";

#Test suit
######################################################################
$TestSuit = array(
'logTicket' => False
);


#TEST FUNCTIONS HERE
######################################################################
function logTicket(){
	//Populate ticket model instalate
	$tO = new stdClass(); 
	$tO->tkt_subject    	     = "Title";
	$tO->tkt_message_excerpt 	 = "Initial Message";
	$tO->tkt_message 	 = "NEW TICKET";
	$tO->tkt_channel     	 = "EMAIL";
	$tO->tkt_status 	 	 = "OPEN";
	$tO->tkt_private_notes 	 = "Private notes";
	$tO->tkt_tags 	 		 = "tag";
	$tO->tkt_customer_rating = "1";

	$tO->tkt_id = null;
	$tO->tkt_resolution = null;
	$tO->tkt_time_logged = 'NOW()';
	$tO->tkt_updated_by = null;	
	$tO->tkt_logged_by = 1;
	$TC = new TicketsController();
	$id = $TC->logTicket( $tO );
	

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
