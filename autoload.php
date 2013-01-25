<?php
function __autoload($class_name) {
    $cls = explode("_",$class_name);
    $cl_path = "";
    foreach ( $cls as $i=>$c){
        $cl_path .= $c."/";
    }
    if ( $cl_path == "" ){
        return;
    }
    $cl_path[strlen($cl_path)-1] = ".";
    require_once $cl_path. 'php';
}
?>
