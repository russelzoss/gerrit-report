<?php

/**
 * Teams class
 *
 * @author Ruslan Oprits
 */

require_once 'SharepointClass.php';

class Teams {

    public function __construct(){

        $handle = opendir(TEAMS_DIR);
        $this->teams_obj = new stdClass();
        
        while($name = readdir($handle)) {
            if (is_dir($name)) continue;
            $this->teams_obj->{$name} = file(TEAMS_DIR .DIRECTORY_SEPARATOR. 
                    $name, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
        }
        
        closedir($handle);

        /*** Set proxy if defined ***/
        if (true === USE_PROXY) { 
            $proxy = ' --proxy '.PROXY;
        }
        
        $sharepoint = new SharePoint();
        $this->teams_obj->Local = $sharepoint->get_people();
    }

        
    public function find($name){
        //print "$name\n";
        $name_arr = preg_split("/[\s\.]/", $name, -1, PREG_SPLIT_NO_EMPTY);
        $name_arr = preg_replace('/[IiYy]/','[IiYy]',$name_arr);
        //print_r($name_arr);
        $permutes = $this->array_2D_permute($name_arr, array(), TRUE);
        //print_r($permutes);

	foreach($permutes as $key => $perm) {
            $subpat = implode('[\s\.]', $perm);
            $permutes[$key] = $subpat;
        }
        
        $pat = implode(')|(', $permutes);
        $pattern = '/^[\s\.]*('. $pat .')[\s\.]*$/i';
        //$pattern = "/^ *($n[0] +$n[1])|($n[1] +$n[0]) *$/i";
        //$pattern = "/^ *($n[0] +$n[1])|($n[1] +$n[0]) *$/i";
        //print "$pattern\n";
        
        foreach ($this->teams_obj as $team => $array){
            if(preg_grep($pattern, $array)) {
                return $team;
            }
        }
        return 'REMOTE';
    }
    
    public function show(){
        print_r($this->teams_obj);
    }
    
    public function array_2D_permute($items, $perms = array(), $isNew = false) {
        static $permuted_array = array();
        
        if($isNew)
             $permuted_array = array();
        
        if (empty($items)) {
            $permuted_array[]=$perms;
        
            
        }  else {
            
            for ($i = count($items) - 1; $i >= 0; --$i) {
                 $newitems = $items;
                 $newperms = $perms;
                 list($foo) = array_splice($newitems, $i, 1);
                 array_unshift($newperms, $foo);
                 $this->array_2D_permute($newitems, $newperms);
             }
            
             return $permuted_array;
        }
    }
    
}
?>

