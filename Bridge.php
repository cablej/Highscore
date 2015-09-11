<?php

require_once('BridgeConfig.php');

class Bridge {
	
	private $actionHandlers = [];
	private $inputHandlers = [];
	
	private $returnValue = [];
	
    private static $_instance = null;
    
    //private function  __construct() { } //Prevent any oustide instantiation of this class
    private function  __clone() { } //Prevent any copy of this object

    public static function getInstance()
    {
        if( !is_object(self::$_instance) ) {
            self::$_instance = new Bridge();
			
			foreach (glob("Extensions/*.php") as $filename) {
				include $filename;
				if(DEBUG)
					echo("<p>Found $filename.</p>");
			}
			
			if(DEBUG)
				echo("<p>Bridges built.</p>");
			
        }
        return self::$_instance;
    }
    
    function validateInputs($inputs) {
    	$validatedArray = [];
    	foreach($inputs as $input) {
    		$value = $_GET[$input["name"]];
    		$type = $input["type"];
    		$required = $input["required"];
			foreach(self::$_instance->inputHandlers as $handler) {
				$validatedInput = $handler->validateInput($value, $type);
				if($validatedInput != NULL && $validatedInput != "") {
					$validatedArray[$input["name"]] = $validatedInput;
				} else { //error
					if($required == "true") {
						self::$_instance->error("Invalid input.");
					}
				}
			}
    	}
    	return $validatedArray;
    }
    
    function processAction() {	
		if(!isSet($_GET["action"])) {
			$this->error("no action specified");
		}

		$action = $_GET["action"];

		$mysqli = $this->getmysqli();

		foreach($this->actionHandlers as $handler) {
			$handler->handleAction($action, $mysqli);
		}
		
    }
    
	//terminates the program with an error
	function error($message) {
		$this->returnValue[] = ["error" => $message];
		$this->returnCurrentValue();
	}
	
	function appendMessage($title, $message) {
		$this->returnValue[] = [$title => $message];
	}
	
	function appendReturnValue($value) {
		$this->returnValue[] = $value;
	}
	
	function appendActionHandler($handler) {
		$this->actionHandlers[] = $handler;
	}
	
	function appendInputHandler($handler) {
		$this->inputHandlers[] = $handler;
	}
	
	function returnCurrentValue() {
		die(json_encode($this->returnValue, JSON_UNESCAPED_SLASHES));
	}
	
	//returns the database used
	function getmysqli() {
		$mysqli = new mysqli(MYSQLI_HOST, MYSQLI_USERNAME, MYSQLI_PASSWORD, MYSQLI_DB_NAME);
		return $mysqli;
	}

	//a generic query, returns an associative array
	function query($sql, $mysqli) {
		$resultArray = [];
		if($result = $mysqli->query($sql)) {
			if($result->num_rows > 0) {
				while($row = $result->fetch_array(MYSQLI_ASSOC)) {
					$resultArray[] = $row;
				}
			}
		} else {
			$this->error("could not query $sql");
		}
		return $resultArray;
	}

	//queries exactly one row
	function query_one($sql, $mysqli) {
		if($result = $mysqli->query($sql)) {
			if($result->num_rows == 1) {
				$row = $result->fetch_array(MYSQLI_ASSOC);
				return $row;
			} else {
				error("could not query12 $sql");
			}
		} else {
			error("could not query1 $sql");
		}
	}
	
}

/*class Singleton
{
    private static $instances = array();
    protected function __construct() {}
    protected function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function getInstance()
    {
        $cls = get_called_class(); // late-static-bound class name
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static;
        }
        return self::$instances[$cls];
    }
}*/


?>