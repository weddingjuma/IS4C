<?php
	function addBatch($backoffice) {
		// TODO - Check name, start, end, type against expected values
		$link=mysql_connect($_SESSION["mServer"], $_SESSION["mUser"], $_SESSION["mPass"]);
		if ($link) {
			$query='INSERT INTO `is4c_op`.`batchHeaders` (`name`,`start`,`end`,`batchType_id`,`modified`,`whomodified`) VALUES (\''.$_REQUEST['addBatch_name'].'\', \''.$_REQUEST['addBatch_start'].'\', \''.$_REQUEST['addBatch_end'].'\', '.$_REQUEST['addBatch_type'].', NOW(), \''.$_SERVER['REMOTE_ADDR'].'\')';
			$result=mysql_query($query, $link);
			if ($result) {
				// TODO - Change {link} to URL for batch
				array_push($backoffice['status'], 'Added batch {link}');
			} else {
				array_push($backoffice['status'], 'Error with MySQL query: '.mysql_error($link));
			}
		} else {
			array_push($backoffice['status'], 'Error connecting to MySQL');
		}
	}
	
?>