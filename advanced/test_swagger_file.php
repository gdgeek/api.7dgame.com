<?php
namespace api\modules\v1;
use OpenApi\Annotations as OA;
/**
 * @OA\Info(title="Test", version="1.0")
 */
class TestController
{
    /**
     * @OA\Get(
     *     path="/test",
     *     @OA\Response(response="200", description="ok")
     * )
     */
    public function actionIndex() {}
}
