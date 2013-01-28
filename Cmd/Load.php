<?php
class Cmd_Load implements Cmd_Base{
    function execute($param){
        $param = ltrim($param);
        if ( strlen($param) == 0 ){
            return $this->print_help();
        }
        $params = explode(" ",$param);
        foreach ( $params as $p ){
            if ( empty($p) ){
                continue;
            }
            echo "Loading file:".$p."\n";
            $pf = new Parser_FullStack();
            $dataFullStack = $pf->doParse($p);
            Runtime_Data::$fullStackMap[$p] = $dataFullStack;
            echo "Load success:".$p."\n";
        }
    }
    function print_help(){
        echo "Usage: load [file1] [file2]\n";
        return 0;
    }
}
?>
