<?php
   
class database {
  
    private $server="mysql-emmastr:4167";
    private $database="emmastr";
    private $user="admin";
    private $password="qGUZ1dx4";   

    function __construct($user="",$password="",$server="",$database="")  {
        
    	if($user!="")    {$this->user=$user;}
        if($password!=""){$this->password=$password;}
        if($server!="")  {$this->server=$server;}
        if($database!=""){$this->database=$database;}        
       
        $domain = $_SERVER['HTTP_HOST'];
        if ( $domain == 'localdev.infrafrontier.eu' ){
            $this->server="localhost:3306";
            $this->database="emmastr";
            $this->user="root";
            $this->password="a";
        }
    }
	function db_fetch($query) {
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

