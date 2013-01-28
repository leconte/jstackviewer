<?php
interface Cmd_Base{
    function execute($param);

    function print_help();
}
?>
