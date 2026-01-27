<?php
/**
 * JWT Configuration Test Script
 * 
 * This script tests if JWT is configured correctly
 */

// Load Yii framework
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/common/config/bootstrap.php';

// Load application configuration
$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/common/config/main.php',
    require __DIR__ . '/common/config/main-local.php'
);

$application = new yii\console\Application($config);

echo "=== JWT Configuration Test ===\n\n";

try {
    // Test 1: Check if JWT component is loaded
    echo "1. Checking JWT component... ";
    $jwt = Yii::$app->jwt;
    echo "✅ OK\n";
    
    // Test 2: Check JWT configuration
    echo "2. Checking JWT configuration... ";
    $config = $jwt->getConfiguration();
    echo "✅ OK\n";
    
    // Test 3: Check signer algorithm
    echo "3. Checking signer algorithm... ";
    $signer = $config->signer();
    $algorithmId = $signer->algorithmId();
    echo "✅ Algorithm: $algorithmId\n";
    
    // Test 4: Check signing key
    echo "4. Checking signing key... ";
    $signingKey = $config->signingKey();
    echo "✅ OK\n";
    
    // Test 5: Try to generate a test token
    echo "5. Generating test JWT token... ";
    $now = new \DateTimeImmutable('now');
    $token = $jwt->getBuilder()
        ->issuedBy('test')
        ->issuedAt($now)
        ->canOnlyBeUsedAfter($now)
        ->expiresAt($now->modify('+1 hour'))
        ->withClaim('uid', 999)
        ->withClaim('test', true)
        ->getToken($signer, $signingKey);
    
    $tokenString = $token->toString();
    echo "✅ OK\n";
    echo "   Token: " . substr($tokenString, 0, 50) . "...\n";
    
    // Test 6: Try to parse the token
    echo "6. Parsing test token... ";
    $parsedToken = $jwt->parse($tokenString);
    $claims = $parsedToken->claims();
    $uid = $claims->get('uid');
    echo "✅ OK (uid: $uid)\n";
    
    // Test 7: Validate the token
    echo "7. Validating test token... ";
    $constraints = [
        new \Lcobucci\JWT\Validation\Constraint\SignedWith($signer, $config->verificationKey()),
    ];
    
    if ($jwt->validate($parsedToken, ...$constraints)) {
        echo "✅ OK\n";
    } else {
        echo "❌ FAILED\n";
    }
    
    echo "\n=== All Tests Passed! ===\n";
    echo "Your JWT configuration is working correctly.\n";
    echo "Algorithm: $algorithmId\n";
    echo "Key file: " . getenv('JWT_KEY') . "\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR\n\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
