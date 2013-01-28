<?php
class Cmd_Parser{
    var $cmdMap = array();
    function Cmd_Parser(){
        $this->cmdMap = array(
            "load"=>new Cmd_Load(),
            "help"=>new Cmd_Help(),
            "exit"=>new Cmd_Exit(),
            "quit"=>new Cmd_Exit(),
            "files"=>new Cmd_Files(),
            "list"=>new Cmd_List(),
        );
    }
    function executeCmd($cmdstr){
        $ret = $this->findCmd($cmdstr);
        $cmd = $ret["cmd"];
        $param = $ret["param"];
        return $cmd->execute($param);
    }
    function findCmd($cmdstr){
        $blankPos = strpos($cmdstr," ");
        $cmd = "help";
        if ( $blankPos === false ){
            $cmd = $cmdstr;
            $param = "";
        }else{
            $cmd = substr($cmdstr,0,$blankPos);
            $param = substr($cmdstr,$blankPos+1);
        }
        if ( isset($this->cmdMap[$cmd]) ){
            return array(
                "cmd"=>$this->cmdMap[$cmd],
                "param"=>$param
                );
        }else{
            return array(
                "cmd"=>$this->cmdMap["help"],
                "param"=>$param
                );
        }
    }
}
?>
