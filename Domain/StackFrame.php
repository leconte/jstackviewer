<?php
class Domain_StackFrame extends Domain_RawText{
    var $fileName;
    var $functionName;
    var $lineNumber;
    var $lockedObjs = array();//锁住的对象Domain_Obj
    var $blockedOnObj = null;//等待锁的对象Domain_Obj
    var $waitingOnObj = null;//WaitingFor
    var $isNative = false;//是否Native方法
}
?>
