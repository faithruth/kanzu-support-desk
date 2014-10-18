<?php
/**
 *
 * @package   Kanzu_Support_Desk
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 * @requires  imap php module
 */

class Kanzu_Mail { 	

	protected $defualt_ports = array('pop3'=>'110', 'imap'=>'143', 'pop3/ssl'=>'995', 'imap/ssl'=>'993');
	protected $imap=null;
	private   $num_msgs = 0;


	public function __construct(){

	}

	/*
	* Open connection to mailbox
	*
	* @param protocol One of pop3,imap,pop3/ssl,imap/ssl
	* @param server	  Server ip address or hostname
	* @param user_id  mailbox user id
	* @param password Account password
	* @param port 	  Service port on server
	* @param mailbox  Default is INBOX
	*
	* @return 	  TRUE/FALSE
	*/
	public function connect(  $protocol, $server, $user_id, $password, $port=null, $mailbox="INBOX" )
	{
		if(is_null($port))
		{
			$port= $this->defualt_ports[$protocol];
		}
		//TODO: Add self-signed certificate.
		$this->imap = imap_open("{" . "$server:$port/$protocol"."}"."$mailbox", $user_id, $password);
		
		if( $this->imap != FALSE)
		{
			$this->num_msgs = imap_num_msg($this->imap);
			return TRUE;
		}

		return FALSE;
	}
	
	/*
	* Number of new messages
	*
	*/
	public function numMsgs()
	{
		return $this->num_msgs;
	}


	/*
	* get message
	* @param mid message id
	*/
	public function getBody( $mid)
	{
		return $this->getmsg( $this->imap, $mid);
	}

	/*
	* Close connection
	*
	*/
	public function disconnect()
	{
		return imap_close( $this->imap);
	}

	private function getmsg($mbox,$mid) {
	    // input $mbox = IMAP stream, $mid = message id
	    // output all the following:
	    global $charset,$htmlmsg,$plainmsg,$attachments;
	    $htmlmsg = $plainmsg = $charset = '';
	    $attachments = array();

	    // HEADER
	    $h = imap_header($mbox,$mid);
	    // add code here to get date, from, to, cc, subject...
	    //echo $h;

	    // BODY
	    $s = imap_fetchstructure($mbox,$mid);
	    if (!$s->parts)  // simple
		$this->getpart($mbox,$mid,$s,0);  // pass 0 as part-number
	    else {  // multipart: cycle through each part
		foreach ($s->parts as $partno0=>$p)
		    $this->getpart($mbox,$mid,$p,$partno0+1);
	    }
	
	    return array('html'=>$htmlmsg, 'charset'=>$charset, 'text'=> $plainmsg, 'attachments'=>$attachments );
	}

	private function getpart($mbox,$mid,$p,$partno) 
	{
		// $partno = '1', '2', '2.1', '2.1.3', etc for multipart, 0 if simple
		global $htmlmsg,$plainmsg,$charset,$attachments;
		
		// DECODE DATA
		$data = ($partno)?
		imap_fetchbody($mbox,$mid,$partno):  // multipart
		imap_body($mbox,$mid);  // simple

		// Any part may be encoded, even plain text messages, so check everything.
		if ($p->encoding==4)
			$data = quoted_printable_decode($data);
		elseif ($p->encoding==3)
			$data = base64_decode($data);

		// PARAMETERS
		// get all parameters, like charset, filenames of attachments, etc.
		$params = array();
		if ($p->parameters)
			foreach ($p->parameters as $x)
		    		$params[strtolower($x->attribute)] = $x->value;
		if ( @$p->dparameters)
			foreach ($p->dparameters as $x)
		    		$params[strtolower($x->attribute)] = $x->value;

		// ATTACHMENT
		// Any part with a filename is an attachment,
		// so an attached text file (type 0) is not mistaken as the message.
		if ( @$params['filename'] || @$params['name']) {
			// filename may be given as 'Filename' or 'Name' or both
			$filename = ($params['filename'])? $params['filename'] : $params['name'];
			// filename may be encoded, so see imap_mime_header_decode()
			$attachments[$filename] = $data;  // this is a problem if two files have same name
		}

		// TEXT
		if ($p->type==0 && $data) {
			// Messages may be split in different parts because of inline attachments,
			// so append parts together with blank row.
			if (strtolower($p->subtype)=='plain')
			    $plainmsg .= trim($data) ."\n\n";
			else
			    $htmlmsg .= $data ."<br><br>";
			$charset = $params['charset'];  // assume all parts are same charset
		}
		// EMBEDDED MESSAGE
		// Many bounce notifications embed the original message as type 2,
		// but AOL uses type 1 (multipart), which is not handled here.
		// There are no PHP functions to parse embedded messages,
		// so this just appends the raw source to the main message.
		elseif ($p->type==2 && $data) {
			$plainmsg .= $data."\n\n";
		}

		// SUBPART RECURSION
		if ( @$p->parts) {
			foreach ($p->parts as $partno0=>$p2)
			    $this->getpart($mbox,$mid,$p2,$partno.'.'.($partno0+1));  // 1.2, 1.2.1, etc.
		}
	}
}
?>