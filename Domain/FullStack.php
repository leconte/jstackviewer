<?php
class Domain_FullStack extends Domain_RawText{
    var $date;
    var $jvm;
    var $threads = array();//所有线程
    function print_oneline_summary(){
        echo strftime("%Y-%m-%d %H:%M:%S",strtotime($this->date))." ";
        echo "Threads:".sizeof($this->threads);
    }
}
