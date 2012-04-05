<?php
/*
	Class to work with github's gist
	
	Api docs: http://developer.github.com/v3/gists/
*/

class gist{
    
    private $public = false;
    private $description = '';
    private $fileName = 'PHPidler';
    private $return = false;
    
    /**
     * Sets if the gist is private or public
     *
     * @param boolean $public
     */
    public function setPublic($public){
	$this->public = $public;
    }
    
    /**
     * Sets the description
     *
     * @param string $description
     */
    public function setDescription($description){
	$this->description = $description;
    }
    
    /**
     * Sets the file name
     *
     * @param string $fileName
     */
    public function setFileName($fileName){
	$this->fileName = $fileName;
    }
    
    /**
     * Sends to gist
     *
     * @param string $txt the text to be sent
     * @return boolean indicating success
     */
    public function paste($txt){
        $ch = curl_init('https://api.github.com/gists');
        $postFields = array('description' => $this->description,
			    'public' => $this->public,
			    'files' => array($this->fileName => array('content' => $txt)));
	    
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFields));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $this->response = json_decode(curl_exec($ch), true);
	
	$return = (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 201);
        curl_close($ch);
        return $return;
    }
    
    /**
     * Gets the result of the last paste()
     *
     * @param string $what if set, only return this part of the response
     */
    public function getResult($what = ''){
	if ($what) {
	    return $this->response[$what];
	}else{
	    return $this->response;
	}
    }
}

