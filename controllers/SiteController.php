<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\Abc;


class SiteController extends Controller
{
	public function actionIndex(){
	}
    public function actionTables(){
	
        $_c  = Abc::_getC();
        $_b = Abc::_getB();
		
        if($_b){
             foreach($_b as $v){
               $_ids[]=$v['id'];
             }
             $ids_str= join(',',$_ids);
             $_a = Abc::_getA($ids_str);

            foreach( $_b as $k=>$v){
                $dataProvider[]   = [
                                'id'          =>$v['id'],
                                'name'         => Abc::_getBname($v['id'],$_a),
                                'age'         => $v['age'],
                                'money'       => $v['money'],
                                'level'       => Abc::_getBlevel($v['money'],$_c),
                                'created_at' => $v['created_at']
                            ]  ;
            }
        }

        return $this->render('abc',['dataProvider'=>$dataProvider]);
    }
	public function actionError(){
		
	}
}
