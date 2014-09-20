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
'logTicket' => True
);


#TEST FUNCTIONS HERE
######################################################################
function logTicket(){
	//Populate ticket model instalate
	$tO = new stdClass(); 
	$tO->tkt_title    	     = "Title";
	$tO->tkt_initial_message 	 = "Initial Message";
	$tO->tkt_description 	 = "NEW TICKET";
	$tO->tkt_channel     	 = "EMAIL";
	$tO->tkt_status 	 	 = "OPEN";
	$tO->tkt_private_notes 	 = "Private notes";
	$tO->tkt_tags 	 		 = "tag";
	$tO->tkt_customer_rating = "1";

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
