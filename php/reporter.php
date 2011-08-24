<?php

    /**
     * reporter.php
     * view / display class
     * @author rbacon
     * @date 20080422
     */

// add classes if necessary
if (! class_exists('Site')) {
    include_once('site.php');
}
class Reporter {


////////////////////
// constructor(
    /**
     * @param $data - associative array of data
     */
    public function __construct($data) {
        $this->data = $data; // store for view's use
    }
    
    /**
     * run the display code
     */
    public function display() {
        foreach ($this->data['sites'] as $site) {
            // set useful variables
            $stumbles = $site->getStumbles();
            $fts = $site->getFirstTimestamp();
            $lts = $site->getLastTimestamp();
            $gender_ratings = $site->getCategoryRatings('gender');
        
            echo "------------------------------------\n";
            echo $site->getName() . "\n";
            echo $site->getStumbles() . " stumbles\n";
            echo 'Rating: '.$site->getRating() . ' (+'.$site->getPositiveStumbles() . '/-'.$site->getNegativeStumbles() . ")\n";
            
            echo 'First stumble: '.date('Ymd h:i:s',$fts) . "\n";
            echo 'Last stumble: '.date('Ymd h:i:s',$lts) . "\n";
            echo 'Average stumbles per hour: ' . round(3600 * $stumbles / ($lts - $fts), 2) . "\n";
            
            echo 'Top five tags of stumblers giving positive review: ' . implode(',',array_keys($site->getTags('positives',5))) . "\n";
            echo 'Top five tags of stumblers giving negative review: ' . implode(',',array_keys($site->getTags('negatives',5))) . "\n";
            
            echo 'By gender: ' . "\n";
            echo '  Female:  (+' . $gender_ratings['positives']['female'] . '/-' . $gender_ratings['negatives']['female'] . ")\n";
            echo '  Male:  (+' . $gender_ratings['positives']['male'] . '/-' . $gender_ratings['negatives']['male'] . ")\n";

        }
    }

}

?>