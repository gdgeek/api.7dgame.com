

<?php

$data = new stdClass();
$logic = new stdClass();

$logic = <<<HERE

local logic_mt = {
	__index = {
		post = function(self, key, json_string)
			print('key:'..key)
			parameter = json.decode(json_string)
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

        version = function(self)
            return 2
        end,

        init = function(self)
           if self.handling ~= nil and self.handling['@init'] ~=nil then
               self.handling['@init'](self);
           end
        end,
        setup = function(self)
            self.handling = {}



            --[[logic code]]

            self.handling['next'] = function(self, parameter)
                print('!!!!!!!next'..parameter.sample)
            end

            
            self.handling['last'] = function(self, parameter)
                print('!!!!!!!last'..parameter.sample)
            end
            self.handling['@update'] = function(self, interval)
                
                --print('@update'.. interval)
            end

            self.handling['@init'] = function(self)
                print('@init')
            end


            self.handling['@destroy'] = function(self)
                print('@destroy')
            end
            
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
$conf = new stdClass();
$data->logic = $logic;
$conf->list = array();
$data->configure = $conf;
$comp = new stdClass();
$comp->component = "MrPP.SampleLib.SampleStorable";
$comp->path = ["SampleRoot"];
$serialize = new stdClass();
$serialize->list = array();
$entity = new stdClass();
$entity->id = "3:ID";
$polygens = array();
$polygen = new stdClass();
$polygen->id = "3:ID";
$polygen->file = new stdClass();
$polygen->file->name = "911f680ca9e998925f7183d095b34424";
$polygen->file->url = "http://files-1257979353.cos.ap-chengdu.myqcloud.com/";
$polygen->file->cache = true;
$polygen->file->compress = true;
$polygen->file->compressed = "zip";
$polygen->type = "type";
array_push($polygens, $polygen);
$polygen->local = new stdClass();
$polygen->local->position=new stdClass();
$polygen->local->position->x=0;
$polygen->local->position->y=0;
$polygen->local->position->z=0;
$polygen->local->scale=new stdClass();
$polygen->local->scale->x=1;
$polygen->local->scale->y=1;
$polygen->local->scale->z=1;


$polygen->local->angle=new stdClass();
$polygen->local->angle->x=0;
$polygen->local->angle->y=0;
$polygen->local->angle->z=0;
$polygen->local->multi = 10000;
/*
$polygen->local->effects = [];
$polygen->propertis = [];
$polygen->natives = [];
$polygen->hints = [];
$polygen->boards = [];
$polygen->videos = [];
$polygen->pictures = [];
*/
$entity->local = new stdClass();
$entity->local->position=new stdClass();
$entity->local->position->x=0;
$entity->local->position->y=0;
$entity->local->position->z=0;
$entity->local->scale=new stdClass();
$entity->local->scale->x=1000;
$entity->local->scale->y=1000;
$entity->local->scale->z=1000;


$entity->local->angle=new stdClass();
$entity->local->angle->x=0;
$entity->local->angle->y=0;
$entity->local->angle->z=0;
$entity->local->multi = 1000;

//dd  dtoolbar\\\":{\\\"destroy\\\":false,\\\"enabled\\\":true,\\\"position\\\":[0,0,0],\\\"buttons\\\":[]}
$entity->toolbar = new stdClass();
$entity->toolbar->destroy = false;
$entity->toolbar->template = "ppt";
$entity->toolbar->enabled = true;
$entity->polygens = $polygens;
array_push($serialize->list, json_encode($entity,JSON_UNESCAPED_SLASHES));

$comp->serialize = json_encode($serialize,JSON_UNESCAPED_SLASHES);
array_push($conf->list, $comp);
$data->title = "test";
$data->introduce ="test";
if(isset($project)){

    echo json_encode($project,JSON_UNESCAPED_SLASHES);
}else{
    echo json_encode($data,JSON_UNESCAPED_SLASHES);
}
?>