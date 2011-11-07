<?php
/*
 * Adds sqlite wrappers
 */
 
class database{
    private $handle = false;
    
	/**
	 * Creates a PDO instance and, if needed, creates the file and the table
	 *
	 * @param string $dbPath The path of the database file
	 */
	public function __construct($dbPath) {
        try{
            // Connect to the database
            $this->handle = new PDO('sqlite:' . $dbPath);
            // Create the table
            $this->handle->exec('CREATE TABLE IF NOT EXISTS options (id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, plugin STRING, name STRING, value STRING)');
        }
        
        catch(PDOException $e) {
            if ($irc->debug) {
                echo $e->getMessage();
            }
        }
    }

    /**
	 * Does a select query
	 *
	 * @param string $name The name of the option
	 * @param string|null $plugin The name of the plugin. If null, it will be ignored
	 * @return array
	 */
    public function get($name = null, $plugin = null) {
			$sql = 'SELECT name,value FROM options WHERE 1';
			$parameters = array();
			
			if(!is_null($name)){
                $sql.= ' AND name = ?';
                $parameters[] = $name;
            }
			
			if(!is_null($plugin)){
                $sql.= ' AND plugin = ?';
                $parameters[] = $plugin;
            }
			
        try{
			$tmp = $this->handle->prepare($sql);
			$tmp->execute($parameters);
            $return = $tmp->fetchAll(PDO::FETCH_ASSOC);
			foreach ($return as $key => $array){
				foreach ($array as $thisKey => $thisValue){
					$return[$key] = array($array['name'] => unserialize($array['value']));
				}
			}
			return $return;
		}
        
        catch(PDOException $e) {
            if ($irc->debug) {
                echo $e->getMessage();
            }
        }
    }    

    /**
	 * Does an insert query
	 *
	 * @param string $name The name of the new option
	 * @param mixed $value The value of the new option
	 * @param string|null $plugin The name of the plugin. If null, it will be ignored
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function insert($name, $value, $plugin = '') {
        try{
            $tmp = $this->handle->prepare('INSERT INTO options (plugin, name, value) VALUES (?, ?, ?)');
            return $tmp->execute(array($plugin, $name, serialize($value)));
        }
        
        catch(PDOException $e) {
            if ($irc->debug) {
                echo $e->getMessage();
            }
        }
    }

    /**
	 * Does an update query changing the value of a given option
	 *
	 * @param string $name The name of the option to update
	 * @param mixed $newValue The new value of the option
	 * @param mixed $oldValue The old value of the option. If null, it will be ignored
	 * @param string|null $plugin The name of the plugin. If null, it will be ignored
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function change($name, $newValue, $oldValue = null, $plugin = null) {
		$sql = 'UPDATE options SET value = ? WHERE name = ?';
		$parameters[] = serialize($newValue);
		$parameters[] = $name;
		
		if(!is_null($oldValue)){
			$sql.= ' AND value = ?';
			$parameters[] = serialize($oldValue);
		}
		
		if(!is_null($plugin)){
			$sql.= ' AND plugin = ?';
			$parameters[] = $plugin;
		}
		
        try{
			$tmp = $this->handle->prepare($sql);
			return $tmp->execute($parameters);
		}
        
        catch(PDOException $e) {
            if ($irc->debug) {   
                echo $e->getMessage();
            }
        }
    }
	
	
    /**
	 * Does a delete query
	 *
	 * @param string $name The name of the option to delete
	 * @param mixed $value The value of the option to delete. If null, it will be ignored
	 * @param string|null $plugin The name of the plugin. If null, it will be ignored
	 * @return bool Returns TRUE on success or FALSE on failure.
	 */
	public function delete($name, $value = null, $plugin = null) {
		$sql = 'DELETE FROM options WHERE name = ?';
		$parameters[] = $name;
		
		if(!is_null($value)){
			$sql.= ' AND value = ?';
			$parameters[] = serialize($value);
		}
		
		if(!is_null($plugin)){
			$sql.= ' AND plugin = ?';
			$parameters[] = $plugin;
		}
		
        try{
			$tmp = $this->handle->prepare($sql);
			return $tmp->execute($parameters);
        }
        
        catch(PDOException $e) {
            if ($irc->debug) {
                echo $e->getMessage();
            }
        }
    }
	
}

$this->database = new database($this->db_path);