<?php

/**
 * Wrap the ImapMailbox class to make some KSD Mail-specific changes
 * without changing the core class code
 *
 * @package   KSD_Mail
 * @author    Kanzu Code <feedback@kanzucode.com>
 * @license   GPL-2.0+
 * @link      http://kanzucode.com
 * @copyright 2014 Kanzu Code
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'KSD_Mail_ImapMailbox' ) ) :

class KSD_Mail_ImapMailbox extends ImapMailbox {
   	
        /**
         * We over-ride this function because it uses one extra argument when calling
         * imap_open, params - which was added in PHP 5.3.2. To make sure that KSD Mail
         * supports lower versions, PHP >= 5.2.4 (Like WordPress), we remove that extra argument (params) here
         * @return IMAP stream resource
         * @throws ImapMailboxException
         */
        protected function initImapStream() {
		$imapStream = @imap_open($this->imapPath, $this->imapLogin, $this->imapPassword, $this->imapOptions, $this->imapRetriesNum );
		if(!$imapStream) {
			throw new ImapMailboxException('Connection error: ' . imap_last_error());
		}
		return $imapStream;
	}
}
endif;
