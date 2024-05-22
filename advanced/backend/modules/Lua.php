<?php

namespace backend\modules;

/**
 * Lua module definition class
 */
class Lua extends \yii\base\Module

{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'backend\modules\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // custom initialization code goes here
    }
    private $advanced = <<<EOF

local logic_mt = {
	__index = {
		post = function(self, key, json_string)
			print('key:'..key)
			parameter = json.decode(json_string)
			print('parameter:'..json.encode(parameter))
            --{{sample code}}
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
            --{{logic code}}
        end,
        version = function(self)
            return 1;
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

EOF;
    private $script = <<<EOF
local logic_mt = {
	__index = {
		post = function(self, key, json_string)
			 print('key:'..key)
			parameter = json.decode(json_string)
			 print('parameter:'..json.encode(parameter))
			--begin
--{{coding}}
			--end


		end,
		add_result = function(self, delegate)
			if self.callbacklist == nil then
				self.callbacklist = {}
			end
			table.insert(self.callbacklist, delegate)

			print('add',delegate)
		end,
		remove_result = function(self, delegate)
			for i=1, #self.callbacklist do
				if CS.System.Object.Equals(self.callbacklist[i], delegate) then
					table.remove(self.callbacklist, i)
					break
				end
			end
			print('remove', delegate)
		end,
        version = function(self)
            return -1;
        end,
		callback = function(self, evt)
			if self.callbacklist ~= nil then
				for i=1, #self.callbacklist do
					self.callbacklist[i](self, evt)
				end
			end
		end,
	}
}


Logic = {
	Creater = function ()
        return setmetatable({}, logic_mt)
    end
}

EOF;

    public function packing($func)
    {
        $pattern = "/--{{coding}}/";
        return preg_replace($pattern, $func, $this->script);
    }
    public function getAdvanced($code)
    {

        $pattern = "/--{{logic code}}/";
        return preg_replace($pattern, $code, $this->advanced);
    }
}
