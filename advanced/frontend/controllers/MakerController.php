<?php

namespace frontend\controllers;

use common\models\Maker;
use common\components\AppPost;
use common\components\PolygenData;
use common\components\TransformData;
use common\components\Vector3Data;
use common\components\ComponentData;
use common\components\ConfigureData;
use common\components\ProjectData;
use common\components\EntityData;

class MakerController extends \yii\web\Controller
{
    public function getLogic($maker){
        $data = json_decode($maker->data);
       // echo $maker->data;
    //  echo json_encode($data->snapshots->list);
      $list = json_encode($data->snapshots->list);// '[{"list":[{"handle":{"index":[0,0]},"data":{"data":{"position":{"x":"0","y":"0","z":"0"},"angle":{"x":"0","y":"180","z":"0"},"scale":{"x":"1","y":"1","z":"1"}},"enabled":true,"name":"RootNode_Wrapper"}}]}]';
$logic = <<<HERE

local logic_mt = {
	__index = {
        data = json.decode('$list'),
        index = 1,
		post = function(self, key, json_parameter)
			print('key:'..key)
			parameter = json.decode(json_parameter)
			print('parameter:'..json.encode(parameter))
			
            --[[sample code]]

           if self.handling ~= nil and self.handling[key] ~=nil then
               print(key .. '!!!')
               self.handling[key](self, parameter);
           end
		end,
        update = function(self, interval)

           if self.handling ~= nil and self.handling['@update'] ~=nil then
               self.handling['@update'](self, interval);
           end
        end,
    
        init = function(self)
           if self.handling ~= nil and self.handling['@init'] ~=nil then
               self.handling['@init'](self);
           end
        end,
        setup = function(self)
            self.handling = {}



            --[[logic code]]
            self.handling["next"] = function(self, parameter)
                self.index = self.index + 1;
                CS.MrPP.Lua.LuaExecuter.PlaySnapshot(parameter.sample, 0.5, json.encode(self.data[self.index]));
            end
            self.handling["last"] = function(self, parameter)
            
            end
            self.handling['2:ID'] = function(self, parameter)
                print('!!!!!!!'..parameter.sample)
            end
            self.handling['@update'] = function(self, interval)
                
             --   print('@update'.. interval)
            end

            self.handling['@init'] = function(self)
            

                print('@init'.. json.encode(self.data))
                
                print('@init'.. json.encode(self.data[1]))
                CS.MrPP.Lua.LuaExecuter.PlaySnapshot('entity', 0, json.encode(self.data[self.index]));
                
            end


            self.handling['@destroy'] = function(self)
                print('@destroy')
            end
            
        end,
        
        version = function()
            return 2
        end,
        destroy = function(self)
            if self.handling ~= nil and self.handling['@destroy'] ~=nil then
               self.handling['@destroy'](self);
           end
        end,
 
		callback = function(self, evt)
            CS.MrPP.Lua.LuaExecuter.Execute(evt.key, evt.value);
		end,
	}
}
                   

Logic = {
	Creater = function ()
      
        return setmetatable({}, logic_mt)
    end
}
HERE;
        return $logic;
    }
    public function getConfigure($maker){
    
        $polygen = $maker->getPolygen()->one();
        $data = json_decode($maker->data);
        $polygenData = new PolygenData("polygen", $polygen, TransformData::From($data->transform));
      
        $configure = new \stdClass();
        $configure->list = array();
        $component = new ComponentData("MrPP.SampleLib.SampleStorable", ["SampleRoot"]);
      

        $entity = new EntityData("entity", new TransformData(
           new Vector3Data(0,0,2),
           new Vector3Data(0,0,0),
           new Vector3Data(1,1,1)
       ) );


        $entity->addPolygen($polygenData);
        
        $entity->setToolbar("ppt");

        $component->addEntity($entity);

        $component->complete();
        $configure = new ConfigureData();
        $configure->addComponent($component);


        return $configure;
    
    }
    public function actionIndex()
    {
        $post = new AppPost();
        list($ret, $data)  = $post->setup();
        if($ret->succeed == false){
            return json_encode($ret);
        }
        if(isset($data->projectId)){
            $maker = $this->findMaker($data->projectId);
            if($maker){
            
                $configure = $this->getConfigure($maker);
                $logic = $this->getLogic($maker);
                 return $this->renderPartial('index', ['project'=>new ProjectData($logic, $configure)]);
                
            }else{
            
                $ret->succeed = false;
                $ret->error = "no maker";
                return json_encode($ret);
            }
        }else{
            $ret->succeed = false;
            $ret->error = "no project id";
            return json_encode($ret);
        }
       // echo json_encode($ret);
       // echo json_encode($data);
        return $this->renderPartial('index');
    }

      protected function findMaker($id){
        if (($model = Maker::findOne($id)) !== null) {
            return $model;
        }

        return null;
        //throw new NotFoundHttpException('The requested page does not exist.');
  
    }

}
