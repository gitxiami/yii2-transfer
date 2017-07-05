<?php

namespace app\controllers;

use Yii;
use yii\mongodb\Query;


class MigrationController extends Controller
{
    /**
     * 初始化sql 语句
     */
   function initSql(){


   }
    public function actionMongoToMysql()
    {




        if($dbName == 'mall'){
            $malldb = Yii::$app->get('malldb');
            if($mongoTablesImport == 1){
                $mongoTables = [
                    'goods',
                    'goods_analysis',
                    'goods_attribute',
                    'goods_class',
                    'goods_class_ad',
                    'goods_class_ad_list',
                    'goods_combination',
                    'goods_comment',
                    'goods_group_list',
                    'goods_group_sub',
                    'goods_local_price',
                    'goods_package',
                    'goods_picture',
                    'goods_province_price',
                    'goods_type',
                    'mall',
                    'notice',
                    'notice_list',
                    'order',
                    'order_goods',
                    'payment',
                    'receiving',
                    'reduction',
                    'relation',
                    'voucher',
                ];
            }else if($mongoTablesImport == 2){
                $mongoTables = [
                    'order_record',
                    'ordergoods',
                    'order_goods_record',
                    'ordergoodsrecord',
                    'voucher_list_record',
                ];
            }else if(in_array($mongoTablesImport, [3,4,5,6])){
                $mongoTables = [
                    'voucher_list',
                ];
            }else if($mongoTablesImport == 7){
                $mongoTables = [
                    'stock',
                ];
            }else{
                die('mongoTablesImport error');
            }
        }else if($dbName == 'analytics'){
            $malldb = Yii::$app->get('analyticsdb');
            $mongoTables = [
                'goods_comment',
                'goods_sales',
                'goods_sales_day',
            ];
        }else{
            die('dbName error');
        }
        foreach($mongoTables as $mongoTable){
            if(($dbName == 'analytics') || in_array($mongoTable, ['ordergoods', 'ordergoodsrecord'])){
                $mongoTableName = $mongoTable;
            }else{
                $mongoTableName = $dbName.'.'.$mongoTable;
            }
            $mysqlTableName = $dbName.'_'.$mongoTable;
            echo $mysqlTableName.' process is beginning.'.PHP_EOL;
            $start = 0;
            $len = 100;
            $query = new Query;
            if($mongoTablesImport == 3){
                //$total = $query->from($mongoTableName)->where(['in', 'state', ['2', '3']])->count('*', $malldb);echo $total;exit;
                $list = $query->from($mongoTableName)->where(['in', 'state', ['2', '3']])->offset($start)->limit($len)->all($malldb);
            }else if($mongoTablesImport == 4){
                //$total = $query->from($mongoTableName)->where(['state' => '1'])->andWhere(['voucher_id' =>new \MongoId('564adcff08bd711d7d50c1e5')])->count('*', $malldb);echo $total;exit;
                $list = $query->from($mongoTableName)->where(['state' => '1'])->andWhere(['voucher_id' => new \MongoId('564adcff08bd711d7d50c1e5')])->offset($start)->limit($len)->all($malldb);
            }else if($mongoTablesImport == 5){
                $list = $query->from($mongoTableName)->where(['state' => '1'])->andWhere(['voucher_id' => new \MongoId('55e18fa408bd71072c8c017e')])->offset($start)->limit($len)->all($malldb);
            }else if($mongoTablesImport == 6){
                $voucherMongoIdArr = [
                    new \MongoId('56d94b94ed9b245bf517aa21'),
                    new \MongoId('55f68bf708bd7144be9467f5'),
                    new \MongoId('55fa80c208bd7144c0948f0c'),
                    new \MongoId('55de7fc808bd717342242ac4'),
                    new \MongoId('55de7f4908bd71734223fbe3'),
                    new \MongoId('5695c3d3ed9b2464dbc52908'),
                    new \MongoId('56c5a533ed9b247c5d352649'),
                    new \MongoId('56c6ca7fed9b247c5d352a43')];
                $list = $query->from($mongoTableName)->where(['state' => '1'])->andWhere(['in', 'voucher_id', $voucherMongoIdArr])->offset($start)->limit($len)->all($malldb);
            }else{
                $list = $query->from($mongoTableName)->offset($start)->limit($len)->all($malldb);
            }
            while(!empty($list) && is_array($list)) {
                foreach ($list as $each) {
                    // \common\widgets\Tools::P($each);exit;
                    $mysqlData = [];
                    foreach($each as $mongoKey=>$mongoVal){
                        if($mongoKey == '_id'){
                            $mongoKey = 'mongo_id';
                        }
                        if(is_object($mongoVal) && get_class($mongoVal)=='MongoDate'){
                            $mongoVal = $mongoVal->toDateTime()->format('Y-m-d H:i:s');
                        }
                        if(is_array($mongoVal)){
                            $mongoVal = json_encode($mongoVal);
                        }
                        $mongoVal = (string)$mongoVal;
                        if($mongoVal === ''){
                            continue;
                        }
                        if(($mongoTableName == $dbName.'.payment') && ($mongoKey == 'notify_time')){
//                            if(!preg_match("/^d{4}-d{2}-d{2} d{2}:d{2}:d{2}$/s",$mongoVal)) {
//                                continue;
//                            }
                            if($mongoVal == '-- ::'){
                                continue;
                            }
                        }
                        if(($mongoTableName == $dbName.'.receiving') && ($mongoKey == 'Fixed')){
                            $mongoKey = 'fix_phone';
                        }
                        $mysqlData[$mongoKey] = $mongoVal;
                    }
                    //\common\widgets\Tools::P($mysqlData);exit;
                    $mysqldb = $dbName.'Mysql';
                    $exist = Yii::$app->$mysqldb->createCommand('SELECT mongo_id FROM '.$mysqlTableName.' WHERE mongo_id="'.$mysqlData['mongo_id'].'"')->queryOne();
                    try {
                        if(empty($exist)){
                            Yii::$app->$mysqldb->createCommand()->insert($mysqlTableName, $mysqlData)->execute();
                        }else{
                            Yii::$app->$mysqldb->createCommand()->update($mysqlTableName, $mysqlData, 'mongo_id="'.$mysqlData['mongo_id'].'"')->execute();
                        }
                    } catch (Exception $e) {
                        echo $e->getCode().'|'.$e->getMessage().PHP_EOL;
                        exit();
                    }
                    echo (++$start).' '.$mysqlTableName." data already process ok...".PHP_EOL;
                }
                $list = $query->from($mongoTableName)->offset($start)->limit($len)->all($malldb);
            }
            echo $mysqlTableName.' process is over.'.PHP_EOL;
        }
    }
}
