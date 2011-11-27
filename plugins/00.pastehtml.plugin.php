<?php
/*
	Class to send text to pastehtml.com
*/

class pastehtml{
    
    private $type = 'html';
    
    /**
     * Sets the type of the paste
     *
     * @return boolean Indicates if the asignation was suscessful
     * @param string $type html|txt|mrk
     */
    public function setType($type){
        if (in_array($type, array('html', 'txt', 'mrk'))) {
            $this->type = $type;
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * Sends the text to pastehtml.com
     *
     * @param string $txt the text to be sent
     */
    public function paste($txt){
        $ch = curl_init('http://pastehtml.com/upload/create?input_type=' . urlencode($this->type) . '&result=address');
        
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('txt' => $txt));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
        return $return;
    }
}