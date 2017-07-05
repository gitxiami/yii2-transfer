<?php

namespace app\models;

use Yii;
use yii\mongodb\Query;
use yii\base\Model;

/**
 * Abc infro
 */
class Abc extends Model
{
     public static function _getA($ids){
          $connection = Yii::$app->db;
          $connection->open();
          $command = $connection->createCommand('SELECT * FROM A where id IN ('.$ids.')');
          $a_list = $command->queryAll();
          return $a_list;
      }
     public static function _getB($offset=0,$limit=10){
          $mongodb = Yii::$app->get('mongodb');
          $query = new Query;
          $b_list = $query->from('B')->offset($offset)->limit($limit)->all($mongodb);
          return   $b_list;
      }
     public static function _getC(){
         $redis = Yii::$app->redis;
         $c_list = $redis->hget('C','level');
         return   $c_list;
    }
    public static function _getBname($id,$data){
       foreach($data as $v ){
        if($v['id']==$id){
            return $v['name'];
            break;
        }
       }
    }
    public static function _getBlevel($money,$data){
            if($data<$money){
                return 'lavel';
            }
    }
}
