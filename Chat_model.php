<?php
Kerisy::import('App.Model', true);
/**
 * Class Chat_Model_Chat
 * 聊天
 * @author youjiawang
 */
class Chat_Model_Chat extends App_Model
{
    const ANNO_CAH = 'ANNO_CAH';
    const FAC_CAH = 'FAC_CAH';
    /**
     * 世界聊天
     * @var string
     */
    const WORLD_CAH = 'WORLD_CAH';
    const GM_CAH = 'GM_CAH';
    const ALL_CAH = 'ALL_CAH';
    const MARQUEE_CAH = 'MARQUEE_CAH';
    

    const ANNO_TARGET = 'anno';
    const WORLD_TARGET = 'world';
    const FAC_TARGET = 'fac';
    const GM_TARGET = 'gm';
    const ALL_TARGET = 'all';
    const MARQUEE_TARGET = 'marquee';

    const CHAT_MAX = 10;

    const CHAT_NUM = 50;

    const CHAT_LIST = 10;

    protected $table_name = 'faction';

    protected $primary_key = 'id';

    /**
     * 展现的最大map值
     * @var int
     */
    const SHOW_CHAT_MAX_COUNT = 30;

    /**
     * 添加会话
     * @param $fromId 发送者编号
     * @param $fromName 发送者名字
     * @param $content 发送内容
     * @param int $targetId 接受者的id
     * @param string $targetType 聊天类型
     */
    public function add( $fromId , $fromName , $content  , $targetId = 0 , $targetType = self::WORLD_TARGET,$vip=0 )
    {
        $result = 0;
        if( method_exists( $this , $targetType ) )
        {
            $result = $this->$targetType( $fromId , $fromName , $content  , $targetId, $vip);
        }
        return $result;
    }

    /**
     * 读取会话
     * @param string $targetType
     * @param int $start
     * @param int $targetId
     */
    public function read( $targetType = self::WORLD_TARGET , $start = 0 ,$targetId = 0 )
    {
        $ids = array();
        $end = $this->getId( $targetType , false , $targetId );
       
     
        $idsCount = 0;
        if( empty( $start ) )
        {
            if( empty( $start ) && $targetType != self::ANNO_TARGET && $end < self::CHAT_LIST )
            {
                for( $i = $end ; $i > 0; $i-- )
                {
                	if ($idsCount > self::SHOW_CHAT_MAX_COUNT)
                	{
                		break;
                	}
                	$idsCount++;
                    $ids[] = $i;
                }
                for( $i = self::CHAT_NUM ; $i > self::CHAT_NUM-self::CHAT_LIST + $end ; $i-- )
                {
                	if ($idsCount > self::SHOW_CHAT_MAX_COUNT)
                	{
                		break;
                	}
                	$idsCount++;
                    $ids[] = $i;
                }

               
            }else
            {
                for( $i = $end ; $i > $end-self::CHAT_LIST; $i-- )
                {
                	if ($idsCount > self::SHOW_CHAT_MAX_COUNT)
                	{
                		break;
                	}
                	$idsCount++;
                    $ids[] = $i;
                }
             
            }
            
        }
        else
        {
            if($start>=self::CHAT_NUM&&$end<self::CHAT_NUM)
            {
                $start = 0;
            }
            for( $i = $end ; $i > $start; $i-- )
            {
                if ($idsCount > self::SHOW_CHAT_MAX_COUNT)
                {
                	break;
                }
                	$idsCount++;
              
                $ids[] = $i;
            }
        }
       
        $keys = $this->getCharKey( $targetType , $targetId) ;
      
        $result = array();
        if(!empty($keys)&&!empty($ids))
        {
            foreach( $ids as $id )
            {
            	foreach ( $keys as $key )
            	{
	                $r = Kerisy::redis()->hGet( $key , $id );
	                if( !empty( $r ) )
	                {
	                    $result[] = $r;
	                }
            	}
            }
        }
        
        
        return $result;
    }

    public function remove()
    {

    }

    /**
     * 公告
     * @param $fromId
     * @param $content
     * @param $targetId
     */
    private function anno( $fromId , $fromName , $content  , $targetId )
    {
        $id = $this->getId(self::WORLD_TARGET);

        $data = array
        (
            'fromId' => $fromId ,
            'fromName' => $fromName,
            'content' => $content,
            'id' => $id ,
            'type' => 'anno',
            'time' => timenow(),
        );
        Kerisy::redis()->hSet( self::WORLD_CAH , $id , $data );
        return $id;
    }

    /**
     * GM公告
     * @param $fromId
     * @param $content
     * @param $targetId
     */
    private function gm( $fromId , $fromName , $content  , $targetId )
    {
        $id = $this->getId(self::WORLD_TARGET);

        $data = array
        (
            'fromId' => $fromId ,
            'fromName' => $fromName,
            'content' => $content,
            'id' => $id ,
            'type' => 'gm',
            'time' => timenow(),
        );
        Kerisy::redis()->hSet( self::WORLD_CAH , $id , $data );
        return $id;
    }
    
    /**
     * GM公告
     * @param $fromId
     * @param $content
     * @param $targetId
     */
    private function marquee( $fromId , $fromName , $content  , $targetId )
    {
    	$id = $this->getId(self::WORLD_TARGET);
    
    	$data = array
    	(
    			'fromId' => $fromId ,
    			'fromName' => $fromName,
    			'content' => $content,
    			'id' => $id ,
    			'type' => 'marquee',
    			'time' => timenow(),
    	);
    	Kerisy::redis()->hSet( self::MARQUEE_CAH , $id , $data );
    	return $id;
    }
    
    /**
     * 系统跑马灯
     * @param $fromId
     * @param $content
     * @param $targetId
     */
    private function gmMarquee( $fromId , $fromName , $content  , $targetId )
    {
    	$id = $this->getId(self::WORLD_TARGET);
    
    	$data = array
    	(
    			'fromId' => $fromId ,
    			'fromName' => $fromName,
    			'content' => $content,
    			'id' => $id ,
    			'type' => 'gm',
    			'time' => timenow(),
    	);
    	Kerisy::redis()->hSet( self::MARQUEE_CAH , $id , $data );
    	return $id;
    }
    

    /**
     * 世界
     * @param $fromId
     * @param $content
     * @param $targetId
     */
    private function world( $fromId , $fromName , $content  , $targetId,$vip )
    {
        $id = $this->getId(self::WORLD_TARGET);
        $delId = Kerisy::redis()->incrBy( $this->getKey( self::WORLD_TARGET ), 1 );
       
        if( $delId > self::CHAT_NUM )
        {
        	$this->removeChat(self::WORLD_CAH, self::CHAT_NUM );
            Kerisy::redis()->setIncr( $this->getKey( self::WORLD_TARGET ) , 1 );
//            //删除CHAT_NUM - FAC_MAX之前的
//            for($i=0;$i<self::CHAT_NUM-self::CHAT_MAX;$i++)
//            {
//            	Kerisy::redis()->hDel( self::WORLD_CAH , $i );
//            }

          
        }
        $data = array
        (
            'fromId' => $fromId ,
            'fromName' => $fromName,
            'content' => $content,
            'type' => 'world',
            'id' => $id ,
            'time' => timenow(),
        	'vip' => $vip,
        );

        Kerisy::redis()->hSet( self::WORLD_CAH , $id , $data );
        return $id;
    }

    /**
     * 帮会
     * @param $fromId 发送者
     * @param $content 内容
     * @param $targetId 接受者
     */
    private function fac( $fromId , $fromName , $content  , $targetId,$vip=0 )
    {
        $id = $this->getId( self::FAC_TARGET , true , $targetId );
        $delId = Kerisy::redis()->incrBy( $this->getKey( self::WORLD_TARGET, $targetId ), 1 );
        if( $delId > self::CHAT_NUM )
        {
            Kerisy::redis()->setIncr( $this->getKey( self::FAC_TARGET, $targetId ) , 1 );
            $this->removeChat(self::CHAT_NUM);
//            //删除CHAT_NUM - FAC_MAX之前的
//            for($i=0;$i<self::CHAT_NUM-self::CHAT_MAX;$i++)
//            {
//            	Kerisy::redis()->hDel( self::FAC_CAH.'_'.$targetId , $i );
//            }
	
            $this->removeChat(self::FAC_CAH.'_'.$targetId, self::CHAT_NUM );
        }
        $data = array
        (
            'fromId' => $fromId ,
            'factionId' => $targetId,
            'fromName' => $fromName,
            'content' => $content,
            'type' => 'fac',
            'id' => $id ,
            'time' => timenow(),
        	'vip'=>$vip,
        );
        Kerisy::redis()->hSet( self::FAC_CAH.'_'.$targetId , $id , $data );
        return $id;
    }
    /**
     * @param string $targetType
     * @param bool $inc
     * @param int $exten
     * @return int当前的ID
     */
    private function getId( $targetType = self::WORLD_TARGET ,$inc = true ,$targetId = 0 )
    {
        $id = 0;

        $key = $this->getKeyAll( $targetType , $targetId );
        if( !empty( $key ) )
        {
            if( $inc )
            {
                $id = Kerisy::redis()->incrBy( $key, 1 );
            }
            else
            {
                $id = Kerisy::redis()->getIncr( $key );
            }
        }
        return $id;
    }
    private function getKeyAll( $targetType = self::WORLD_TARGET , $targetId = 0 )
    {
      	if( empty( $targetType ) )
    	{
    		return false;
    	}
        return "chat_id";
    }
    private function getKey( $targetType = self::WORLD_TARGET , $targetId = 0 )
    {
    	if( empty( $targetType ) )
    	{
    		return false;
    	}
    	return "chat_{$targetType}_id_{$targetId}";
    }

    private function getCharKey( $targetType = self::WORLD_TARGET , $targetId = 0 )
    {
        $key = array();
        if( $targetType == self::WORLD_TARGET )
        {
            $key[] = self::WORLD_CAH;
        }
        elseif( $targetType == self::FAC_TARGET )
        {
            if( !empty( $targetId ) )
            {
                $key[] = self::FAC_CAH.'_'.$targetId;
            }
        }
        elseif( $targetType == self::ANNO_TARGET )
        {
            $key[] = self::ANNO_CAH;
        }
         elseif( $targetType == self::MARQUEE_TARGET )
        {
        	$key[] = self::MARQUEE_CAH;
        }
         else if( $targetType == self::ALL_TARGET )
        {
        	$key[] = self::WORLD_CAH;
        	if( !empty( $targetId ) )
        	{
        		$key[] = self::FAC_CAH.'_'.$targetId;
        	}
        	$key[] = self::ANNO_CAH;
        }
        return $key;
    }

    /**
     * 重置数据
     * @param string $targetType
     * @param int $targetId
     */
    public function resetId( $targetType = self::WORLD_TARGET , $targetId = 0 )
    {
        $id = $this->getId( $targetType , false , $targetId );
        if( $id >= self::CHAT_NUM )
        {
            //删除CHAT_NUM - FAC_MAX之前的

        }
    }
    
    /**
     * 删除聊天信息
     * @param $retain_count
     */
    public function removeChat($chat_key ,$retain_count = 30)
    {
    	$chat_ids = Kerisy::redis()->hKeys($chat_key);

    	if (count($chat_ids) <= 30)
    	{
    		return false;
    	}
    	arsort($chat_ids, SORT_NUMERIC );
    	$i=0;
    	foreach ($chat_ids as $id)
    	{
    		$i++;
    		if ($i < $retain_count)
    		{
    			continue;
    		}
    		Kerisy::redis()->hDel( $chat_key , $id );
    	}
    	return true;
    }
    
}