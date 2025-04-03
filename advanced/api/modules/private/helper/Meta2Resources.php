<?php

namespace api\modules\private\helper;

class Meta2Resources
{
    public static function HandleAddon($addon, &$resources)
    {
        $resource = null;
        if (isset($node['parameters']['resource'])) {
            $resource = $addon['parameters']['resource'];
        } else {
            switch ($addon['type']) {
                case 'ImageTarget':
                    $resource = $addon['parameters']['picture'];
                    break;
            }
        }

        if ($resource && !in_array($resource, $resources)) {
            array_push($resources, values: $resource);
        }
        return $resources;
    }
    public static function HandleNode($node, &$resources)
    {
        $resource = null;

        if (!isset($node['type'])) {
            return;
        }
        if (isset($node['parameters']['resource'])) {
            $resource = $node['parameters']['resource'];
        } else {
            switch (strtolower($node['type'])) {
                case 'polygen':
                    if (isset($node['parameters']['polygen'])) {
                        $resource = $node['parameters']['polygen'];
                    }

                    break;
                case 'voxel':
                    if (isset($node['parameters']['voxel'])) {
                        $resource = $node['parameters']['voxel'];
                    }

                    break;
                case 'picture':
                    if (isset($node['parameters']['picture'])) {
                        $resource = $node['parameters']['picture'];
                    }
                    break;
                case 'video':
                    if (isset($node['parameters']['video'])) {
                        $resource = $node['parameters']['video'];
                    }
                    break;
                case 'sound':
                    if (isset($node['parameters']['sound'])) {
                        $resource = $node['parameters']['sound'];
                    }
                    break;
            }
        }

        if ($resource != null && !in_array($resource, $resources)) {
            array_push($resources, $resource);
        }

        if (isset($node['children']) && isset($node['children']['entities'])) {
            foreach ($node['children']['entities'] as $entity) {
                Meta2Resources::HandleNode($entity, $resources);
            }
        }
        return $resources;
    }
    public static function Handle($data)
    {

        $resources = [];

        Meta2Resources::HandleNode($data, $resources);

        if (isset($data['children']) && isset($data['children']['addons'])) {

            foreach ($data['children']['addons'] as $addon) {
                Meta2Resources::HandleAddon($addon, $resources);
            }
        }
        return $resources;

    }
}
