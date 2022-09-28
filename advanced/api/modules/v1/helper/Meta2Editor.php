<?php

namespace api\modules\v1\helper;

use MathPHP\LinearAlgebra\Matrix;
use MathPHP\LinearAlgebra\MatrixFactory;
use MathPHP\LinearAlgebra\Vector;

class Meta2Editor
{
    public static function HandleProject()
    {
        $project = new \stdClass();
        $project->shadows = true;
        $project->shadowType = 1;
        $project->vr = false;
        $project->physicallyCorrectLights = false;
        $project->toneMapping = 0;
        $project->toneMappingExposure = 1;
        return $project;
    }
    public static function Scale2Matrix($scale)
    {
        $matrix = [
            [$scale->get(0), 0, 0, 0],
            [0, $scale->get(1), 0, 0],
            [0, 0, $scale->get(2), 0],
            [0, 0, 0, 1],
        ];
        return MatrixFactory::create($matrix);
    }
    public static function Position2Matrix($position)
    {
        $matrix = [
            [1, 0, 0, 0],
            [0, 1, 0, 0],
            [0, 0, 1, 0],
            [$position->get(0), $position->get(1), $position->get(2), 1],
        ];
        return MatrixFactory::create($matrix);
    }
    public static function Rotate2Matrix($rotate)
    {

        $x = -deg2rad($rotate->get(0));
        $y = -deg2rad($rotate->get(1));
        $z = -deg2rad($rotate->get(2));

        $matrix = [
            [
                cos($y) * cos($z),
                sin($x) * sin($y) * cos($z) - cos($x) * sin($z),
                cos($x) * sin($y) * cos($z) + sin($x) * sin($z),
                0,
            ],
            [
                cos($y) * sin($z),
                sin($x) * sin($y) * sin($z) + cos($x) * cos($z),
                cos($x) * sin($y) * sin($z) - sin($x) * cos($z),
                0,
            ],
            [
                -sin($y),
                sin($x) * cos($y),
                cos($x) * cos($y),
                0,
            ],
            [0, 0, 0, 1],
        ];
        return MatrixFactory::create($matrix);
    }
    public static function Matrix2Array($matrix)
    {
        $ret = $matrix->getMatrix();
        $array = [];
        foreach ($ret as $val) {

            foreach ($val as $num) {
                $array[] = $num;
            }
        }
        return $array;
    }
    public static function HandleCamera()
    {
        $camera = new \stdClass();

        $camera->metadata = new \stdClass();
        $camera->metadata->version = 4.5;
        $camera->metadata->type = 'Object';
        $camera->metadata->generator = 'Object3D.toJSON';

        $camera->object = new \stdClass();
        $camera->object->uuid = "ee3bfc9e-7bc8-4e8a-9331-859dd409e2fd";

        $camera->object->type = "PerspectiveCamera";
        $camera->object->name = "Camera";
        $camera->object->layers = 1;
        $camera->object->matrix = [0.9976782948791967, -6.938893903907228e-18, -0.06810300967606882, 0, -0.046505109679017484, 0.730545299790767, -0.6812788267129403, 0, 0.049752333620457195, 0.6828642361067226, 0.728849189027264, 0, 0.5807187085012865, 7.970521348782701, 8.50726647262407, 1];

        $camera->object->fov = 50;
        $camera->object->zoom = 1;
        $camera->object->near = 0.01;
        $camera->object->far = 1000;
        $camera->object->focus = 10;
        $camera->object->aspect = 0.94362292051756;
        $camera->object->filmGauge = 35;
        $camera->object->filmOffset = 0;

        return $camera;
    }

    public static function CreateMatrix($position, $rotate, $scale)
    {
        $r = Meta2Editor::Rotate2Matrix($rotate);
        $s = Meta2Editor::Scale2Matrix($scale);
        $p = Meta2Editor::Position2Matrix($position);
        $end = $s->multiply($r)->multiply($p);
        return $end;
    }
    public static function CreateRoot($meta)
    {

        $matrix = Meta2Editor::CreateMatrix(
            new Vector([0, 0, 0]),
            new Vector([0, 0, 0]),
            new Vector([1, 1, 1])
        );

        $root = new \stdClass();
        $root->uuid = "d0bb29f8-83ca-4321-acbc-72af4ca24fa8";
        $root->type = "Scene";
        $root->name = "Root";
        $root->layers = 1;
        $root->meta = $meta;
        $root->matrix = Meta2Editor::Matrix2Array($matrix);

     //   $box->geometry = "0c06c358-cc8f-43ca-8011-df329b866db5";
      //  $box->material = "7f1a84c7-5b9b-46df-9f07-96889fa54d98";
        return $root;
    }
    public static function CreateScene($meta)
    {

        $scene = new \stdClass();

        $scene->object = new \stdClass();
        $scene->object->uuid = \Faker\Provider\Uuid::uuid();

        $scene->object->type = "Scene";
        $scene->object->name = "Scene";
        $scene->object->layers = 1;
        $matrix = Meta2Editor::CreateMatrix(
            new Vector([3, 1, 1]),
            new Vector([0, 0, 10]),
            new Vector([1, 2, 1])
        );

        $scene->object->matrix = Meta2Editor::Matrix2Array($matrix);
        $root = Meta2Editor::CreateRoot($meta);

        $ambientLight = new \stdClass();
        $ambientLight->uuid = "5b1ecb2a-3dd9-401c-8829-17b2a59f827c";
        $ambientLight->type = "AmbientLight";
        $ambientLight->name = "AmbientLight";
        $ambientLight->layers = 1;
        $ambientLight->matrix = [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1];
        $ambientLight->color = 2236962;
        $ambientLight->intensity = 1;

        $directionalLight = new \stdClass();
        $directionalLight->uuid = "e0b1ed3e-963e-4c09-847f-cdf58985f038";
        $directionalLight->type = "DirectionalLight";
        $directionalLight->name = "DirectionalLight";
        $directionalLight->layers = 1;
        $directionalLight->matrix = [1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 5, 10, 7.5, 1];
        $directionalLight->color = 16777215;
        $directionalLight->intensity = 1;
        $directionalLight->shadow = new \stdClass();
        $directionalLight->shadow->camera = new \stdClass();
        $directionalLight->shadow->camera->uuid = "33f82710-cabd-405d-b64a-56162f6509d3";
        $directionalLight->shadow->camera->type = "OrthographicCamera";
        $directionalLight->shadow->camera->layers = 1;
        $directionalLight->shadow->camera->zoom = 1;
        $directionalLight->shadow->camera->left = -5;
        $directionalLight->shadow->camera->right = 5;
        $directionalLight->shadow->camera->top = 5;
        $directionalLight->shadow->camera->bottom = -5;

        $directionalLight->shadow->camera->near = 0.5;

        $directionalLight->shadow->camera->far = 500;

        $scene->object->children = [$root, $ambientLight, $directionalLight];

        $scene->metadata = new \stdClass();
        $scene->metadata->version = 4.5;
        $scene->metadata->type = 'Object';
        $scene->metadata->generator = 'Object3D.toJSON';

        $geometrie = new \stdClass();
        $geometrie->uuid = "0c06c358-cc8f-43ca-8011-df329b866db5";
        $geometrie->type = "BoxGeometry";
        $geometrie->width = 1;
        $geometrie->height = 1;
        $geometrie->depth = 1;
        $geometrie->widthSegments = 1;
        $geometrie->heightSegments = 1;
        $geometrie->depthSegments = 1;
        $scene->geometries = [$geometrie];
        $material = new \stdClass();
        $material->uuid = "7f1a84c7-5b9b-46df-9f07-96889fa54d98";
        $material->type = "MeshStandardMaterial";
        $material->color = 16777215;

        $material->roughness = 1;
        $material->metalness = 0;
        $material->emissive = 0;
        $material->envMapIntensity = 1;
        $material->depthFunc = 3;
        $material->depthTest = true;
        $material->depthWrite = true;
        $material->colorWrite = true;
        $material->stencilWrite = false;
        $material->stencilWriteMask = 255;

        $material->stencilFunc = 519;

        $material->stencilRef = 0;
        $material->stencilFuncMask = 255;
        $material->stencilFail = 7680;
        $material->stencilZFail = 7680;
        $material->stencilZPass = 7680;
        $scene->materials = [$material];

        return $scene;

    }
    public static function Handle($meta)
    {

        $base = new \stdClass();
        //$base ->asd= cos(deg2rad(90));
        $base->scripts = [];
        $script = new \stdClass();
        $script->name = "test";
        $script->source = "function update( event ) {}";
        $base->history = new \stdClass();
        $base->history->undos = [];
        $base->history->redos = [];
        $base->scripts["d0bb29f8-83ca-4321-acbc-72af4ca24fa8"] = [$script];
        $base->metadate = new \stdClass();

        $base->project = Meta2Editor::HandleProject();
        $base->camera = Meta2Editor::HandleCamera();
        $base->scene = Meta2Editor::CreateScene($meta);
        $ret = new \stdClass();
        $ret->base = $base;
        $ret->objects = [];
        return $ret;

    }
}
