<?php
/*
 * Adds sqlite wrappers
 */
class database{
    
    private $pluginName = null;
    public $handle;
    
    /**
     * Creates a PDO instance and, if needed, creates the file and the table
     *
     * @param string $dbPath The path of the database file
     * @param bool $debug 
     */
    public function __construct($dbPath = 'db.sqlite') {
        if (!extension_loaded('pdo_sqlite')) {
            // We failed to initialize PDO, show a warning
            echo "******************************************\n";
            echo "**               WARNING                **\n";
            echo "** RUNNING WITHOUT PDO WILL BREAK STUFF **\n";
            echo "******************************************\n";
            return false;
        } else {
            // Connect to the database
            $this->handle = new PDO('sqlite:' . $dbPath);
            $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            // Create the table
            $this->handle->exec('CREATE TABLE IF NOT EXISTS options (name STRING PRIMARY KEY, value STRING, plugin STRING)');
        }
    }

    /**
     * Sets the name of the plugin using the class
     *
     * @param string $pluginName
     */
    public function setPluginName($pluginName = '') {
        $this->pluginName = $pluginName;
    }

    /**
     * Gets the value of an option
     *
     * @param string $name The name of the option
     * @return string the value associated with $name in the database or null for failure
     */
    public function get($name) {
        $tmp = $this->handle->prepare('SELECT value FROM options WHERE name = :name AND plugin = :plugin');
        $tmp->bindValue(':name', $name, PDO::PARAM_STR);
        $tmp->bindValue(':plugin', $this->pluginName, PDO::PARAM_STR);
        $tmp->execute();
        $return = $tmp->fetch(PDO::FETCH_NUM);
        return $return[0];
    }  

    /**
     * Sets the value of an option
     *
     * @param string $name The name of the option
     * @param string $value The new value for the option. If null the option will be deleted from the database
     * @return TRUE on success or FALSE on failure. 
     */
    public function set($name, $value) {
        $tmp = $this->handle->prepare('INSERT OR REPLACE INTO options (name, value, plugin) VALUES (:name, :value, :plugin);');
        $tmp->bindValue(':name', $name, PDO::PARAM_STR);
        $tmp->bindValue(':value', $value, PDO::PARAM_STR);
        $tmp->bindValue(':plugin', $this->pluginName, PDO::PARAM_STR);
        return $tmp->execute();
    }    
}

// This will be executed when loading the  plugin
class database_plugin{
    function __construct(&$irc){
        $irc->database = new database($irc->pluginConfig['db_path']);
    }
}