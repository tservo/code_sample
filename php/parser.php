<?php

/**
 * parser.php
 * reads in files and places them into an array
 * @author rbacon
 * @date 20080422
 */
include_once 'site.php'; // site class

class Parser {

    protected $data = array(); // holds data that is processed
    protected $error; // holds any error that occurs
    
    ////////////
    // constructor
    public function __construct() {
        // initialize data
        $this->data = array ( 'sites' => array(),
                              // 'users' => array() // future expansion: group by users
                            );
    }
    /**
     * read in and attempt to parse $file
     * name of $file is "/path/to/file/website.csv"
     
     * @access public
     * @param string $file filename
     * @return boolean did it work?
     */
     
    public function parse($file) {
        // is $file in format "website.csv?"
        if (strtolower(substr($file, -4)) !== '.csv') {
            return $this->setError('File name not in appropriate format: ' . $file);
        }
        
    
        
        // see if file exists
        if (! file_exists($file)) {
            return $this->setError('File not found: ' . $file);
        }
        
        // okay, we have a file, now to see if we can parse the thing
        $handle = fopen($file, 'r');
        $max_length = 0;
        $bad_line = 0;
        // did we open the file?
        if (! $handle) {
            return $this->setError('Could not read file: ' . $file);
        }


        // get website name -- from base name of file minus suffix
        $website = substr(basename($file), 0, -4); 
        
        
        // site info stored here
        $site = new Site($website);  
        
        // now parse the file in CSV
        while (($data = fgetcsv($handle, 0, ',')) !== false) { // what is the max line length? -- assuming unlimited
            // read in the information into an associative array
            $rating = intval(array_shift($data)); // site rating
            $timestamp = intval(array_shift($data)); // timestamp of action
            
            // user info
            // age group is a category (0-19 -> 1, 20-29 -> 2, 30-39 -> 3,..., 60+ ->6)
            $age = intval(array_shift($data));
            $user['age_group'] = min(6,max(1, floor($age / 10))); 
            $user['gender'] = (intval(array_shift($data)) == 2) ? 'female' : 'male';
            $user['city'] = array_shift($data);
            $user['state'] = array_shift($data);
            $user['country'] = array_shift($data);
            
            $user_tags = array();
            
            // and here are the tags
            $thumbs_up = 0; // total sites thumbed up
            foreach($data as $tag_info) {
                list($tag,$count) = explode(':',$tag_info);
                $user_tags[$tag] = intval($count);
                $thumbs_up += $count;
            }
            
            // calculated information
            $user['thumbsup'] = $thumbs_up;

            // add information to site info
            $site->addStumble($rating, $timestamp, $user_tags, $user);
        }
        

        
        // and save some memory here
        $site->cleanTags(25); // only store top 25 tags
                
        // clean up
        fclose($handle);


        // debug tag:  how much room do we have to crunch?
        // echo memory_get_usage() . "\n";
        
        // add to current info
        $this->addSite($site);        

        // success
        return true;
    }
    


    /**
     * retrieve error
     * @access public
     * @return string the error message
     */
    public function getError() {
        return $this->error;
    }
    
    /**
     * get data array
     * for view purposes -- do not call parser methods in view
     * @access public
     * @return array $data array
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * flag an error with message stored
     * @access protected
     * @param string $error error message
     * @return false
     */
    protected function setError($error) {
        $this->error = $error;
        return false;
    }
    
    /**
     * add site info to data
     * @access protected
     * @param Site $site -- the site to add
     */
    protected function addSite($site) {
        $this->data['sites'][] = $site;
    }
}

?>