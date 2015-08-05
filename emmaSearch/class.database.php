<?php

#===================
# LICENCE
#===================

/*

Copyright 2015 EMBL-EBI

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License. You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is 
distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. 

See the License for the specific language governing permissions and limitations under the License.

*/
   
class database {
    private $server="";
    private $database="";
    private $user="";
    private $password=""; 

    function __construct($user="",$password="",$server="",$database="")  {
    require_once('inc_db_config.php');
    $this->server=HOST . ":" . PORT;
            $this->database=DATABASE;
            $this->user=USER;
            $this->password=PASSWD;    
    	if($user!="")    {$this->user=$user;}
        if($password!=""){$this->password=$password;}
        if($server!="")  {$this->server=$server;}
        if($database!=""){$this->database=$database;}        
# Now not necessary, left for historical purposes
        $domain = $_SERVER['HTTP_HOST'];
        if ( $domain == 'localdev.infrafrontier.eu' ){
            $this->server=HOST . ":" . PORT;
            $this->database=DATABASE;
            $this->user=USER;
            $this->password=PASSWD;
        }
    }
	function db_fetch($query) {
error_log("SQL IS::$query", 0);
		$dbconn = mysql_connect($this->server,$this->user,$this->password);
        mysql_select_db($this->database,$dbconn);    
                
        $result = mysql_query($query,$dbconn);
        $arr=array();
        if (!$result) {
			$arr= "ERROR";
        }
        else {
			while($row = mysql_fetch_assoc($result)){
				$arr[]=$row;
			}
        }
        return $arr;
	}
}

?>

