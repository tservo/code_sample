<?php

    /**
     * site.php
     * class definition for site information
     * @author rbacon
     * @date 20080422
     */

class Site {

/////////////////////
// member vars
/////////////////////
    protected $name; // name of website
    protected $rating = 0; // total rating
    protected $stumbles = 0; // total stumbles
    protected $positives = array(); // list of positive ratings
    protected $negatives = array(); // list of negative ratings
    protected $last_timestamp = 0; // last time the site was visited in data
    protected $first_timestamp = 0; // first time the site was visited in data
    
    /**
     * constructor
     * @access public
     * @param $name the website name
     */
    public function __construct($name) {
        // initialize members
        $this->name = $name;
        $this->negatives = array(
            'stumbles' => array(
            ),
            'tags' => array()
        );
        $this->positives = array(
            'stumbles' => array(       
            ),
            'tags' => array()
        );
        $this->last_timestamp = 0; // last time the site was stumbled
        $this->first_timestamp = time(); // first time the site was stumbled -- set to now as a dummy
    }
    
    
    /**
     * adds a stumble to the site
     * @access public
     * @param (-1,0,1) $rating -- site rating
     * @param $timestamp -- unix timestamp of stumble
     * @param $tags -- keyed array of tags from the stumble
     * @param $user_info -- array of user information (demographic, etc.)
     */
    public function addStumble($rating, $timestamp, &$tags, &$user_info) {
        // validate rating
        if ((!is_int($rating)) || (abs($rating) > 1)) {
            throw new SiteException("Invalid Rating");
        }
        
        // add a stumble
        $this->stumbles++;
        
        // alter total rating
        $this->rating += $rating;
        
        // alter min/max timestamp
        $this->first_timestamp = min($this->first_timestamp, $timestamp);
        $this->last_timestamp = max($this->last_timestamp, $timestamp);
        // store record into appropriate category
        

        switch ($rating) {
            case -1: // negative
                $this->negatives['stumbles'][] = $user_info; // add the stumble info
                $this->addTags('negatives',$tags); // and the tags that led here
            break;
            case 0: // neutral: don't bother doing anything
            break;
            case 1: // positive
                $this->positives['stumbles'][] = $user_info;
                $this->addTags('positives',$tags); // and the tags that led here
            break;
            default:
                throw new SiteException("This should't happen!");
            break;
        }
    }
    
    /**
     * allows system to clean up the tag lists
     * shouldn't be public, but needs to be called outside of class
     * @access public
     * @param int $n the number of tags to keep (the most common in each list)
     */
    public function cleanTags($n=25) {
        $n = intval($n);
        $this->cleanTagType('positives', $n);
        $this->cleanTagType('negatives', $n);
    }
    
    // getter functions
    
    /**
     * get website's name
     * @access public
     * @return string site name
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * get total number of stumbles to site
     * @access public
     * @return int number of stumbles
     */
    public function getStumbles() {
        return $this->stumbles;
    }

    /**
     * get total number of positive stumbles to site
     * @access public
     * @return int number of positive stumbles
     */
    public function getPositiveStumbles() {
        return count($this->positives['stumbles']);
    }    

    /**
     * get total number of negative stumbles to site
     * @access public
     * @return int number of positive stumbles
     */
    public function getNegativeStumbles() {
        return count($this->negatives['stumbles']);
    }
    
    /**
     * get first time when site was stumbled
     * @access public
     * @return int first timestamp
     */
    public function getFirstTimestamp() {
        return $this->first_timestamp;
    }
    
    /**
     * get last time when site was stumbled
     * @access public
     * @return int last timestamp
     */
    public function getLastTimestamp() {
        return $this->last_timestamp;
    }
        
    /**
     * get site rating
     * @access public
     * @return int site rating
     */
    public function getRating() {
        return $this->rating;
    }
    
    /**
     * get tags
     * return top n tags of either positive or negative stumbles (default 10)
     * works only after cleanTags is called
     * @access public
     * @param string $type {positives, negatives}
     * @param int $n
     * @return array tags
     */
    public function getTags($type = 'positives', $n = 10) {
    
        // sanity check
        if (! $this->validStumbleType($type)) {
            throw new SiteException('invalid stumble type');
        }
        
        // return the top $n tag hits
        return array_slice($this->{$type}['tags'], 0, $n);
        
    }
    

    /**
     * get ratings (positive/negative) by category
     * @access public
     * @param string $attribute (currently gender, country, age_group)
     * @return array category data, sorted by positive/negative and category values
     */
    public function getCategoryRatings($attribute) {

        // attribute sanity check (and set defaults for the category array if necessary
        switch ($attribute) {
            case 'gender' :
                $default_array = array('female' => 0, 'male' => 0); // initial gender categories
            break;
            case 'age_group' :
                $default_array = array( 1 => 0,0,0,0,0,0); // initial age group categories
            break;
            case 'country' :
                $default_array = array(); // no default categories
            break;
            default:
                throw new SiteException("Invalid Attribute: $attribute"); // not handled at all

        }
        
        // rating count initialization
        $category = array( 'positives' => $default_array,
                           'negatives' => $default_array); 

        // do both positives and negatives
        foreach (array('positives', 'negatives') as $type) {
            // now count and filter the stumble array
            foreach ($this->{$type}['stumbles'] as $stumble) {
                $filter_value = $stumble[$attribute]; // get the attribute value
                if (in_array($filter_value, array_keys($category[$type]) )) { // increment the count of filter value if it's in the array
                    $category[$type][$filter_value]++;
                }
                else { // if not, add it
                    $category[$type][$filter_value] = 1;
                }
            }

        }
        // output the count
        return $category;
    }
    

///////////////////////////////////
// protected functions
///////////////////////////////////
    /**
     * check that stumble type is valid
     * helper function
     * @access protected
     */
    protected function validStumbleType($type) {
        return in_array($type, array('positives', 'negatives'));
    }

    /**
     * add/merge tags and values to the specified member list
     * @access protected
     * @param string $list ("positives", "negatives")
     * @param array $tags -- list of tags keyed by tag name, with value.
     */
    protected function addTags($list, &$tags) {
        //loop on the tags to see if they are already added.        
        foreach ($tags as $name => $val) {
            if (isset($this->{$list}['tags'][$name])) { // found it
                $this->{$list}['tags'][$name]['count']++; // count another stumble with tag in it
                $this->{$list}['tags'][$name]['value'] += $val;  // add value to tag
            }
            else {  // new tag, add it
                $this->{$list}['tags'][$name] = array ('count' => 1, 'value' =>$val);
            }
        }
    }

    /**
     * reduce number of tags to number specified
     * for the given type {positives, negatives}
     * saves on memory
     * @access protected
     */
    protected function cleanTagType($type, $n=25) {

        // sanity check
        if (! $this->validStumbleType($type) ) {
            throw new SiteException('invalid stumble type');
        }

        // prime the tag list to be sorted by stumble count descending, ties broken by value descending
        foreach($this->{$type}['tags'] as $name => $val) {
            $count[$name] = $val['count'];
            $value[$name] = $val['value'];
        };
        
        // now sort the tags as necessary using this awful function
        array_multisort($count, SORT_DESC, $value, SORT_DESC, $this->{$type}['tags']);
        // and truncate the array
        array_splice($this->{$type}['tags'], $n);
    }


}

// throw this exception
class SiteException extends Exception {}

?>