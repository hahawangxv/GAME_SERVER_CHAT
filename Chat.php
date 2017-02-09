<?php
Kerisy::import('App.Controller.Terminal', true);
/**
 * Class Chat_Controller_Chat
 * 聊天
 * @author wangxv
 */
class Chat_Controller_Chat extends App_Controller_Terminal
{
	public function testAction()
	{
		$result = Kerisy::model('Chat_Model_Chat')->read( 'marquee' );
		ee($result);
	}
    /**
     * 会话
     */
    public function listAction()
    {
    	
    	
        $start = $this->parames['start'];  //开始标识符
        $targetType = $this->parames['type'];
        $fromId = $this->playerId();
//        $fromId = $this->parames['fromId'];
        if( empty( $targetType ) )
        {
        	$targetType = 'all';
        }
        $targetId = 0;
        if( $targetType == 'all' || $targetType == 'fac' )
        {
            $data = Kerisy::model('Faction_Model_FactionPlayer')->getFactionByPlayerId( $fromId );
            if( !empty( $data ) )
            {
                $targetId = $data['faction_id'];
            }
        }

        $result = array();
        $result = Kerisy::model('Chat_Model_Chat')->read( $targetType ,$start , $targetId );
        $time = array();
        $id  = array();
        $timeSpan = timenow() - 21600;
        foreach ($result as $key => $row)
        {
        	//6小时以内 的聊天
        	if( $row['time'] > $timeSpan && $row['id'] )
        	{
	            $time[$key] = $row['time'];
	            $id[$key] = $row['id'];
        	}
        	else 
        	{
        		unset($result[$key]);
        	}
        }
        array_multisort( $time , SORT_NUMERIC , SORT_DESC ,$id , SORT_NUMERIC , SORT_DESC , $result );
        $returnArr = array();
        foreach( $result as $val )
        {
        	//if( $val['time'] > timenow()-60*60*6 && !empty( $val['id'] ))
        	//if( $val['time'] > timenow()-21600 && !empty( $val['id'] ))
        	//{
	            $returnArr[] = $val['fromName'].';'.$val['type'].';'.$val['id'].';'.$val['time'].';'.$val['content'];
        	//}
        }
        return $this->returnSuccess( $returnArr );
    }

    public function chatAction()
    {
    	if($this->player_status == 2 )
    	{
    		if( $this->status_time > timenow() )
    		{
    			$this->returnError( '您已经被禁言', '10011' );
    		}
    	}
    	$targetArr = array
    	(
    		'world','fac'		
    	);
        $targetType = $this->parames['type'];
        $content = urldecode($this->parames['content']);
        $fromId = $this->playerId();
//        $fromId = $this->parames['fromId'];
        if( empty( $targetType ) || empty( $content ) || strlen( $content )>10000 || strlen( $content )<1 || !in_array( $targetType, $targetArr ))
        {
            return $this->returnError( '请求错误' , '10008' );
        }
        //转换成utf8
//        $content = mb_convert_encoding($content, 'UTF-8');

        if(!in_array(mb_detect_encoding($content), array('ASCII', 'UTF-8')))
        {
        	 return $this->returnError( '请求错误' , '10008' );
        }
        
        $content = Kerisy::helper( 'Player_Helper_FilterWords' )->filter( $content, true, '*' );
        $targetId = 0;
        if( $targetType=='fac' )
        {
            $data = Kerisy::model('Faction_Model_FactionPlayer')->getFactionByPlayerId( $fromId );
            if( !empty( $data ) )
            {
                $targetId = $data['faction_id'];
            }
            else
            {
                return $this->returnError( '请求错误' , '10008' );
            }
        }
        $fromInfo = Kerisy::model('Player_Model_Player')->getBasicInfo( $fromId );
        $id = Kerisy::model('Chat_Model_Chat')->add( $fromId , $fromInfo['nickname'] , $content  ,
            $targetId , $targetType,$fromInfo['vip'] );
        $format = array($id,$content);
        return $this->returnSuccess($format);

    }
    public function noticeAction()
    {
    	
    	$notices = Kerisy::model( 'Chat_Model_Notice' )->getNotice();
    	$returnArr = array();
    	foreach( $notices as $val )
    	{
    			$returnArr[] = $val['title'].';'.$val['content'];
    	}
    	
    	Kerisy::model( 'Chat_Model_Notice' )->setNoticeOnce($this->playerId());
    	
    	return $this->returnSuccess( $returnArr );
    	
    	
    }
    
   
}