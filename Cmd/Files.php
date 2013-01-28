<?php
class Cmd_Files implements Cmd_Base{
    function execute($param){
        foreach ( Runtime_Data::$fullStackMap as $filePath=>$fs ){
            echo $filePath." ==> ";
            $fs->print_oneline_summary();
            echo "\n";
        }
    }

    function print_help(){
        echo "files: list opened file\n";
    }
}
?>
