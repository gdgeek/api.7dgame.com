<?php

namespace api\modules\v1\helper;

class Meta2Resources
{
    public static function HandleAddon($addon, &$resources)
    {
        $resource = null;
        switch ($addon->type) {
            case 'ImageTarget':
                $resource = $addon->parameters->picture;
                break;
        }

        if ($resource != null && !in_array($resource, $resources)) {
            array_push($resources, $resource);
        }

    }
    public static function HandleNode($node, &$resources)
    {
        $resource = null;

        if (!isset($node->type)) {
            return;
        }
        switch (strtolower($node->type)) {

            case 'polygen':
                $resource = $node->parameters->polygen;
                break;
            case 'picture':
                $resource = $node->parameters->picture;
                break;
            case 'video':
                $resource = $node->parameters->video;
                break;
            case 'sound':
                $resource = $node->parameters->sound;
                break;

        }

        if ($resource != null && !in_array($resource, $resources)) {
            array_push($resources, $resource);
        }

        if (isset($node->children) && isset($node->children->entities)) {
            foreach ($node->children->entities as $entity) {
                //echo json_encode($entity);
                Meta2Resources::HandleNode($entity, $resources);
            }
        }

        return $resources;
    }
    public static function Handle($data)
    {
        //echo 'aaa';
        $resources = [];

        //$data = json_decode($meta->data);

        Meta2Resources::HandleNode($data, $resources);

        if (isset($data->children) && isset($data->children->addons)) {

            foreach ($data->children->addons as $addon) {

                Meta2Resources::HandleAddon($addon, $resources);
            }
        }
        return $resources;

    }
}
