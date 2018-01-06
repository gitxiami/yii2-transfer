<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;
use Yii;
use yii\console\Controller;
use yii\mongodb\Query;
/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author xiami <1134741860@qq.com>
 * @since 2.0
 */
class ToolsController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     */
    public function actionMongodbToMysql($args='')
    {
		
		//初始化数据
       if($args =='init'){
		   $sql= $this->initSqlString();
		   Yii::info($sql) ;
		   Yii::$app->db->createCommand($sql)->execute();
		   //开始迁移数据
	   }else if($args=='start'){
		   $mongodb = Yii::$app->get('mongodb');
		   $query = new Query;
		   $db = Yii::$app->db;
		   
		   $list = $query->from('user')->where(['status' =>['$eq'=>'1']])->limit(10000)->all($mongodb);
		 
		   $transaction = $db->beginTransaction();
		   try {
			   foreach($list as $v){
				   $table_code = $this->getCrcHash($v['mobile']);
				   $db->createCommand( "REPLACE INTO tickets_user (stub) VALUES ('a');")->execute();
			           $ticket_id = $db->getLastInsertID();
				   
				   $db->createCommand()->insert('user_'.$table_code, ['id' => $ticket_id,
						                       'mobile'       => $v['mobile'],
                                                                       'nickname'     => $v['nickname'],
									'gender'       => $v['gender'],
									'device'       => $v['device'],
									'os'           => $v['os'],
									'login_time'   => $v['login_time'],
									'ip'           => $v['ip'],
									'location'     => serialize($v['location']),
									'province'     => $v['province'],
						                        'city'         => $v['city'],
									'district'     => $v['district'],
								         'deliver_address'=> serialize($v['deliver_address'])
								   ])->execute();
				   $transaction->commit();
				   $mongodb->getCollection('user')->update(['_id'=>$v['_id']],['$set'=>['status'=>1]]);
			   }
			    
		   } catch (Exception $e) {
			   $transaction->rollBack();
		   }
	   }
    }

	/** 分表hash
	 * @param $keyword
	 * @param int $n
	 * @return string
	 */
	function getCrcHash($keyword,$n=100)
	{
		$hash = crc32($keyword) >> 16 & 0xffff;
		return sprintf("%03u",$hash % $n);
	}
	/**
	 * 初始化数据 分表 100个存储 1亿数据
	 */
	public function initSqlString(){
				
		$sql = "DROP TABLE IF EXISTS `tickets_user`;
				CREATE TABLE `tickets_user` (
				  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  `stub` char(1) NOT NULL DEFAULT '',
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=100 DEFAULT CHARSET=utf8;";

		for($i=0;$i<100;$i++){
			$code = str_pad($i,3,0,STR_PAD_LEFT );
		    $sql.= "CREATE TABLE `user_".$code."` (
					  `id` bigint(20) unsigned NOT NULL,
					  `mobile` char(11) NOT NULL DEFAULT '0',
					  `nickname` varchar(50) NOT NULL DEFAULT '',
					  `gender` enum('m','f') NOT NULL DEFAULT 'm',
					  `device` varchar(20) NOT NULL DEFAULT '',
					  `os` varchar(10) NOT NULL DEFAULT '',
					  `login_time` char(20) NOT NULL DEFAULT '',
					  `ip` varchar(15) NOT NULL DEFAULT '',
					  `location` varchar(50) NOT NULL DEFAULT '',
					  `province` varchar(50) NOT NULL DEFAULT '',
					  `city` varchar(50) NOT NULL DEFAULT '',
					  `district` varchar(50) NOT NULL DEFAULT '',
					  `deliver_address`  text NULL,
					  PRIMARY KEY (`id`)
					) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		}
		return $sql;
	}
}
