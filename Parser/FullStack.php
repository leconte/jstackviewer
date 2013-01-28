<?php
class Parser_FullStack{

    function doParse($file){
        $fullStackData = new Domain_FullStack();
        $fulltext = file_get_contents($file);
        $texts = explode("\n",$fulltext);
        $totalLineNumber = sizeof($texts);
        $line = 0;
        $fullStackData->date = $texts[$line++];
        $fullStackData->jvm = $texts[$line++];
        $currentThreadInfo = null;
        $nextLineIsThreadDesc = false;//下一行是线程描述
        $nextLineIsThreadState = false;//下一行是线程状态
        for ( ;$line < $totalLineNumber;$line++ ){
            $currentLine = $texts[$line];
            //step0.记录当前行
            if ( $currentThreadInfo != null ){
                $currentThreadInfo->text .= $currentLine;
                $currentThreadInfo->text .= "\n";
            }
            //step1.空行
            if ( strlen($currentLine) == 0 ){//空行意味着上一个的结束和下一个的开始
                if ( $currentThreadInfo != null 
                    && strlen($currentThreadInfo->name)>0 ){
                   $fullStackData->threads[] = $currentThreadInfo;
                }
                $currentThreadInfo = new Domain_Thread();
                $nextLineIsThreadDesc = true;
                $nextLineIsThreadState = false;
                continue;
            }
            //step2.线程描述
            if ( $nextLineIsThreadDesc ){
                $this->parseThreadDesc($currentLine,$currentThreadInfo);
                $nextLineIsThreadState = true;
                $nextLineIsThreadDesc = false;
                continue;
            }
            //step3.线程状态
            if ( $nextLineIsThreadState ){
                $this->parseThreadState($currentLine,$currentThreadInfo);
                $nextLineIsThreadState = false;
                $nextLineIsThreadDesc = false;
                continue;
            }
            //step4.正常Frame
            $this->parseThreadStackFrame($currentLine,$currentThreadInfo);
        }
        return $fullStackData;
    }
/*"S_ATO_CTU_ato169-msgWorkTP-187048467-8-thread-3" prio=10 tid=0x00002aab032f2800 nid=0x2427 waiting on condition [0x000000004e16d000]*/
    function parseThreadDesc($desc,$threadInfo){
        //1.寻找名称
        $lastQuota = strrpos($desc,"\"");
        if ( $lastQuota==false){
            return;
        }
        $name = substr($desc,0,$lastQuota);
        $desc = substr($desc,$lastQuota+2);
        //2.寻找是否daemon
        $daemonPos = strpos($desc,"daemon");
        if ( $daemonPos !== false){
            $threadInfo->isDaemon = true;
            $desc = substr($desc,$daemonPos+strlen("daemon")+1);
        }else{
            $threadInfo->isDaemon = false;
        }
        //3.解析剩余的
        list($prio,$tid,$nid,$st) = explode(" ",$desc,4);
        $threadInfo->name = substr($name,1,strlen($name)-1);
        list($dummy,$threadInfo->prio) = explode("=",$prio);
        list($dummy,$threadInfo->tid) = explode("=",$tid);
        list($dummy,$threadInfo->nid) = explode("=",$nid);
        //4.分析线程名，解析出线程池的名字
        $pos = strrpos($threadInfo->name,"-");
        if ( $pos!= false ){
            $threadInfo->poolName = substr($threadInfo->name,0,$pos);
            $threadInfo->idxInPool = substr($threadInfo->name,$pos+1);
        }
    }
    function parseThreadState($state,$threadInfo){
        $match = array();
        preg_match('/State: ([^ ]*)/',$state,$match);  
        if ( !empty($match) ){
            $threadInfo->threadState = $match[1];
        }
    }
    function parseThreadStackFrame($frame,$threadInfo){
        $frame = ltrim($frame);
        if ( strpos($frame,"at") === 0 ){
            //1.判断是否Native
            $nativePos = strpos($frame,"Native Method");
            if ( $nativePos !== false ){
                $this->parseNativeStackFrame($frame,$threadInfo);
                return;
            }
            //2.Java Frame
            $this->parseJavaStackFrame($frame,$threadInfo);
            return;
        }else if (strpos($frame,"-") === 0 ){
            $this->parseLockInfo($frame,$threadInfo->stackFrames[sizeof($threadInfo->stackFrames)-1]);
        }else{
            echo $frame."\n";
            exit;
        }
    }
    function parseNativeStackFrame($nativeFrame,$threadInfo){
        $match = array();
        preg_match('/at ([^(]*)\(Native Method\)/',$nativeFrame,$match);
        if ( !empty($match) ){
            $sf = new Domain_StackFrame();
            $sf->functionName = $match[1];
            $sf->isNative = true;
            $sf->text = $nativeFrame;
            $threadInfo->stackFrames[] = $sf;
        }
    }
    function parseJavaStackFrame($frame,$threadInfo){
        $match = array();
        preg_match('/at ([^(]*)\(([^:]*):([^)]*)\)/',$frame,$match);
        if ( !empty($match) ){
            $sf = new Domain_StackFrame();
            $sf->functionName = $match[1];
            $sf->fileName = $match[2];
            $sf->lineNumber = $match[3];
            $sf->isNative = false;
            $sf->text = $frame;
            $threadInfo->stackFrames[] = $sf;
        }
    }
    function parseLockInfo($lockInfo,$stackFrame){
        $obj = new Domain_Obj();
        $obj->text = $lockInfo;
        if ( strpos($lockInfo,"parking to wait for") !== false){
            $match = array();
            preg_match('/- parking to wait for  <([^>]*)> \(a ([^)]*)\)/',$lockInfo,$match);
            if ( !empty($match) ){
                $obj->address = $match[1];
                $obj->cl = $match[2];
                $stackFrame->waitingOnObj = $obj;
                return;
            }
        }
        if ( strpos($lockInfo,"waiting on") !== false ){
            $match = array();
            preg_match('/- waiting on <([^>]*)> \(a ([^)]*)\)/',$lockInfo,$match);
            if ( !empty($match) ){
                $obj->address = $match[1];
                $obj->cl = $match[2];
                $stackFrame->waitingOnObj = $obj;
                return;
            }
        }
        if ( strpos($lockInfo,"waiting to lock") !== false ){
            $match = array();
            preg_match('/- waiting to lock <([^>]*)> \(a ([^)]*)\)/',$lockInfo,$match);
            if ( !empty($match) ){
                $obj->address = $match[1];
                $obj->cl = $match[2];
                $stackFrame->blockedOnObj = $obj;
                return;
            }
        }
        if ( strpos($lockInfo,"locked") !== false ){
            $match = array();
            preg_match('/- locked <([^>]*)> \(a ([^)]*)\)/',$lockInfo,$match);
            if ( !empty($match) ){
                $obj->address = $match[1];
                $obj->cl = $match[2];
                $stackFrame->lockedObjs[] = $obj;
                return;
            }
        }
    }

}
