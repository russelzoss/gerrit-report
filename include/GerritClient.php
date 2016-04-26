<?php
/**
 * Gerrit Client
 *
 * @author Ruslan Oprits
 */

class GerritClient {
    
    public function get_objects(){
        /* Run Gerrit query */
        $command = 'ssh -p'.GERRIT_SSH_PORT.' '.GERRIT_USER.'@'.GERRIT_HOST
                .' gerrit query --format JSON --all-approvals '.GERRIT_QUERY;

        $gerrit_output = shell_exec($command);
        $gerrit_array = array_filter(preg_split("/((\r?\n)|(\r\n?))/", $gerrit_output));

        /* For each patch */
        foreach($gerrit_array as $patch_count => $line){

            /* Parse JSON Object */
            $json = json_decode(utf8_encode($line));

            /* Skip statistics object */
            if (isset($json->type)){
                continue;
            }
            
            /* Skip if match GERRIT_EXCLUDE_REGEX */
            if (defined('GERRIT_EXCLUDE_REGEX')){
                if (preg_match(GERRIT_EXCLUDE_REGEX, $json->subject))
                        continue;
            }
            
            $out[] = $json;
        }

        //print_r($out);
        return($out);
    }
    
    public function stats($json, $teams) {
            $CRVW_score         = 0;        //CodeReview score
            $CRVW_total         = 0;        //CodeReview total counter
            $CRVW_remote        = 0;        //CodeReview REMOTE counter
            $VRIF_total         = 0;        //Verifiers counter
            $VRIF_score         = 0;        //Verify score
            $VRIF_negatives     = array();  //Names of negative verifiers
            $CRVW_negatives     = array();  //Names of negative reviewers
            $result             = new stdClass();
            $result->MergeReady = 'NO';
            $result->Blockers   = '';
        
        /* Check if patchSets exist */
        if (isset($json->patchSets)){
//            $team = $teams->find($json->owner->name);
//            print('URL = ' . $json->url . "\n");
//            print('Last-Updated = ' . $json->lastUpdated . "\n");
//            print('Owner = ' . $json->owner->name . "\n");
//            print('Team = ' . $teams->find($json->owner->name) . "\n");
//            print('Email = ' . $json->owner->email . "\n");
//            print('Branch = ' . $json->branch . "\n");
//            print('Subject = ' . $json->subject . "\n");
//            

            /* Loop through patchSets */
            foreach($json->patchSets as $patchset_count => $patchSet) {

                /* Reset counters. Patch will get the last (curent) 
                 * patchset values, while tracking negatives reviewers.
                 */

                $CRVW_score         = 0;        //CodeReview score
                $CRVW_total         = 0;        //CodeReview total counter
                $CRVW_remote        = 0;        //CodeReview REMOTE counter
                $VRIF_score         = 0;        //Verify score
                $VRIF_total         = 0;        //Verifiers counter    
                
                /* Loop through Approvals */
                if (isset($patchSet->approvals))
                    
                    foreach($patchSet->approvals as $approval_count => $approval) {

                        /* Skip approval if AutoBot */
                        if ('AutoBot' == $approval->by->name) 
                            continue;
                        
                        //print_r($approval);

                        /*
                         *****************************************************************
                         * Approval type = "CRVW"
                         *****************************************************************
                         */
                        if ('CRVW' == $approval->type){

                            /* Non-negative value increments CRVW total counter. */
                            if (0 <= $approval->value) {
                                $CRVW_total = $CRVW_total + 1;
                                
                                /* If Team = "REMOTE", increments REMOTE counter too */
                                if ($teams->find($approval->by->name) == 'REMOTE')
                                    $CRVW_remote = $CRVW_remote + 1;
                                
                                /* Non-negative value removes reviewer from negatives */                                
                                while (($pos = array_search($approval->by->name, $CRVW_negatives)) !== false)
                                    unset($CRVW_negatives[$pos]);
                               
                            } else {

                                /* Negative value stores reviewer's 
                                 * Name in $CRVW_negatives array
                                 */
                                $CRVW_negatives[] = $approval->by->name;
                            }

                            /* If CRVW_score = 0, assign value, 
                             * otherwise minimal of both 
                             */
                            if (0 == $CRVW_score)
                                $CRVW_score = $approval->value;
                                else 
                                    $CRVW_score = min($CRVW_score, $approval->value);
                        }
                        
                        /*
                         *****************************************************************
                         * Approval type = "VRIF"
                         *****************************************************************
                         */
                        if ('VRIF' == $approval->type){
                            
                            /* Non-negative value increments VRIF total counter. */
                            if (0 <= $approval->value){
                                $VRIF_total = $VRIF_total + 1;
                            
                                /* Non-negative value removes reviewer from negatives */
                                while (($pos = array_search($approval->by->name, $VRIF_negatives)) !== false)
                                    unset($VRIF_negatives[$pos]);
                            }
                            /* Negative value stores reviewer's 
                             * Name in $VRIF_negatives array
                             */
                            if (0 > $approval->value)
                                $VRIF_negatives[] = $approval->by->name;

                            /* If VRIF_score = 0, assign value, 
                             * otherwise minimal of both 
                             */
                            if (0 == $VRIF_score)
                                $VRIF_score = $approval->value;
                                else 
                                    $VRIF_score = min($VRIF_score, $approval->value);
                            }                    

                    }
              }
        }
        
        /*
         ******************************************************************
         * Calculating Patch values
         ******************************************************************
         */
        
        $result->URL = $json->url;
        //$result->ChangeN = end(explode('/', $json->url));
        $result->LastUpdated = date('m/d/Y', $json->lastUpdated);
        $result->Owner = $json->owner->name;
        $result->Email = $json->owner->email;
        $result->Team = $teams->find($json->owner->name);
        $result->Branch = $json->branch;
        $result->Subject = $json->subject;
        $result->CRVW_score = $CRVW_score;
        $result->CRVW_total = $CRVW_total;
        $result->CRVW_remote = $CRVW_ti;
        $result->VRIF_score = $VRIF_score;
        $result->VRIF_total = $VRIF_total;
        $result->CRVW_negatives = $CRVW_negatives;
        $result->VRIF_negatives = $VRIF_negatives;
        $result->Blockers = implode("\r\n", 
                array_unique(array_merge($CRVW_negatives, $VRIF_negatives)));
        
        if ((0 <= $CRVW_score) && (3 <= $CRVW_total) && (1 <= $CRVW_remote) 
                && (0 <= $VRIF_score) && $VRIF_total && empty($result->Blockers))
             $result->MergeReady = 'YES';
        
        return($result);
        
        }
 

        
}

?>
