<?php
class Cmd_Exit implements Cmd_Base{
    function execute($param){
        exit;
    }

    function print_help(){
    }
}
