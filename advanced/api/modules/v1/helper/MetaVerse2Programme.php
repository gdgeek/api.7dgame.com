<?php

namespace api\modules\v1\helper;

/*
class MetaVerse2Programme
{
private static $template = <<<EOF

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
private static function ReadFile($file)
{
$data = new \stdClass();
$data->url = $file->url;
$data->md5 = $file->md5;
$data->type = $file->type;
return $data;
}
private static function ReadInfo(&$data, $info)
{

if (!is_array($info) && !is_object($info)) {
return;
}

foreach ($info as $key => $val) {

if (!isset($data->$key)) {
$data->$key = $val;
}

}

}

private static function ReadResource($id)
{
$data = new \stdClass();
$resource = Resource::findOne($id);
if (isset($resource)) {
$data->file = MetaVerse2Programme::ReadFile($resource->file);
MetaVerse2Programme::ReadInfo($data, json_decode($resource->info));
}
return $data;
}
private static function HandleEntity(&$id, $index, $parent, $entity)
{
$ret = new \stdClass();
++$id;
$ret->id = $id;
$ret->title = $entity->parameters->uuid;
$ret->uuid = $entity->parameters->uuid;
if (isset($entity->parameters->transform)) {
$ret->transform = MetaVerse2Programme::HandleTransform($entity->parameters->transform);

}
$ret->active = $entity->parameters->active;
$ret->parent = $parent;

switch ($entity->type) {
case 'Entity':
$ret->type = 'point';
break;
case 'Polygen':
$ret->type = 'polygen';
$resource = MetaVerse2Programme::ReadResource($entity->parameters->polygen); //need change;
$data = $resource;
$data->resource = MetaVerse2Programme::ReadResource($entity->parameters->polygen);
$ret->data = json_encode($data);
break;
case 'Video':
$ret->type = 'video';

$data = new \stdClass();
$data->resource = MetaVerse2Programme::ReadResource($entity->parameters->video);
$data->width = $entity->parameters->width;

$data->loop = $entity->parameters->loop;
$data->play = $entity->parameters->play;
$data->console = $entity->parameters->console;
$data->name = $entity->parameters->name;
$ret->data = json_encode($data);

break;
case 'Picture':
$ret->type = 'picture';
$data = new \stdClass();
$data->resource = MetaVerse2Programme::ReadResource($entity->parameters->picture);
$data->width = $entity->parameters->width;
$ret->data = json_encode($data);
break;
case 'Text':
$ret->type = 'text';
$data = new \stdClass();
$data->content = $entity->parameters->content;
$ret->data = json_encode($data);
break;
}

$points = [$ret];

if (isset($entity->chieldren)) {
$entity->children = $entity->chieldren;
}

foreach ($entity->children->entities as $key => $val) {
$points = array_merge($points, MetaVerse2Programme::HandleEntity($id, $index, $ret->id, $val));
}

$ret->effects = [];
foreach ($entity->children->components as $key => $val) {
$ret->effects = array_merge($ret->effects, [MetaVerse2Programme::HandleEffect($val)]);
}

return $points;
}
private static function HandleEffect($component)
{

$ret = new \stdClass();
$ret->uuid = $component->parameters->uuid;

switch ($component->type) {
case 'Tooltip':
$ret->type = 'tooltip';
$data = new \stdClass();
$data->position = $component->parameters->position;
$data->text = $component->parameters->text;
$ret->data = json_encode($data);
break;
case 'Billboard':
$ret->type = 'billboard';
break;

case 'Rotate':
$ret->type = 'rotate';
$data = new \stdClass();
$data->speed = $component->parameters->speed;
$ret->data = json_encode($data);

break;

case 'LockedScale':
$ret->type = 'locked-scale';
break;

default:
$ret = $component;

}

return $ret;
}
private static function HandleAddon($addon)
{

$ret = new \stdClass();

$ret->uuid = $addon->parameters->uuid;

switch ($addon->type) {
case 'ImageTarget':
$ret->type = 'target';
$data = new \stdClass();
$data->mark = MetaVerse2Programme::ReadResource($addon->parameters->picture);
$data->width = $addon->parameters->width;
$data->rotate = $addon->parameters->rotate;
$data->position = $addon->parameters->position;
$ret->data = json_encode($data);
break;
case 'Toolbar':

$ret->type = 'toolbar';
$data = new \stdClass();
// $data->addon =$addon;
$data->destroy = $addon->parameters->destroy;

if (isset($addon->children->buttons)) {
$data->buttons = [];

foreach ($addon->children->buttons as $key => $val) {

$button = new \stdClass();

$button->icon = $val->parameters->icon;
$button->title = $val->parameters->title;

$button->action = new \stdClass();
$button->action->uuid = $val->parameters->uuid;
$button->action->name = $val->parameters->action;

if (isset($val->parameters->action_parameter)) {
$button->action->parameter = $val->parameters->action_parameter;

}

array_push($data->buttons, $button);
}
}

$ret->data = json_encode($data);

break;
default:
$ret = $addon;

}

//  $ret->addon = $addon;
return $ret;
}

private static function HandleTransform($transform)
{
$data = new \stdClass();
$data->position = $transform->position;
$data->angle = $transform->rotate;
$data->scale = $transform->scale;
return $data;

}

public static function HandleMetaRoot($index, $meta)
{
$ret = new \stdClass();

$id = 0;

$data = json_decode($meta->data);
$ret->id = $id;
$ret->title = $index;

$ret->transform = MetaVerse2Programme::HandleTransform($data->parameters->transform);
$ret->active = $data->parameters->active;
$ret->points = [];
if (isset($data->chieldren)) {
$data->children = $data->chieldren;
}

foreach ($data->children->entities as $key => $val) {
$ret->points = array_merge($ret->points, MetaVerse2Programme::HandleEntity($id, $meta->id, $ret->id, $val));
}
$ret->addons = [];
foreach ($data->children->addons as $key => $val) {
$ret->addons = array_merge($ret->addons, [MetaVerse2Programme::HandleAddon($val)]);
}

return $ret;
}
public static function GetLogic($script)
{

$pattern = "/--{{logic code}}/";
return preg_replace($pattern, $script, MetaVerse2Programme::$template);
}
public static function Handle($mv)
{
$programme = new \stdClass();
$programme->title = $mv->name;
$programme->description = json_decode($mv->info)->description;

$programme->information = $programme->description;
// $programme->test = $mv->metas;
$configure = new \stdClass();
$content = new \stdClass();
$content->list = [];

foreach ($mv->metas as $k => $v) {
if (isset($v->data)) {
array_push($content->list, \api\modules\v1\helper\MetaVerse2Programme::HandleMetaRoot($v->id, $v));
}
}
$configure->content = json_encode($content);

$programme->configure = json_encode($configure);

$cybers = $mv->verseCybers;
if (isset($cybers[0])) {
$programme->logic = MetaVerse2Programme::GetLogic($cybers[0]->script);
}
return $programme;

}
}
 */
