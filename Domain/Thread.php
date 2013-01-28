<?php
class Domain_Thread extends Domain_RawText{
    var $name;//线程名称
    var $poolName;//线程池名称
    var $idxInPool;//在线程池中的次序
    var $prio;
    var $tid;
    var $nid;
    var $isDaemon=false;
    var $threadState;//线程状态
    var $stackFrames = array();//栈帧数组。Domain_StackFrame
}
