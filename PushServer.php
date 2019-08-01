<?php
use MessageHandle;


class PushServer{
    private static $instance=null;
    private static $server;
    private static $messageHandle;//出来消息的对象
    private function __construct()
    {
        //创建websocket对象
        self::$server = new swoole_websocket_server('0.0.0.0',9507);
        //注册事件
        self::$server->on('open',[$this,'onOpen']);//将onOpen方法作为open事件的处理函数
        self::$server->on('message',[$this,'onMessage']);//将onMessage方法作为客服端向服务器发送消息时的处理函数
        self::$server->on('WorkerStart',[$this,'onWorkerStart']);
        self::$server->on('close',[$this,'onClose']);//将onClose方法作为message事件的处理函数
    }

    //当客户端连接上之后要执行的方法
    public function onOPen($server,$req)
    {

    }

    //客服端向服务器发送消息时执行的函数
    public function onMessage($server,$frame)
    {
        //让其执行onWorkerStart函数重新加载
        self::$server->reload();
        $data = json_decode($frame->data,true);
        if( method_exists(self::$messageHandle,$data['cmd']) ) {
            call_user_func([self::$messageHandle,$data['cmd']],$frame->fd,$data);
        }
    }

    public function onWorkerStart()
    {
        self::$messageHandle = new MessageHandle();
    }

    //客户端和服务器端断开连接时执行的函数
    public function onClose($server,$fd)
    {
        return self::$server->close();
    }



    public static function getInstance()
    {
        if( self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function start()
    {
        return self::$server->start();
    }
}
