<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use MuscleZen\Controllers\AuthController;

class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Set up test database
    }

    public function testUserCanLogin()
    {
        $credentials = [
            'email' => 'test@example.com',
            'password' => 'password'
        ];

        $result = AuthController::login($credentials['email'], $credentials['password']);
        
        $this->assertTrue($result['status'] === 'success');
        $this->assertArrayHasKey('user', $result);
    }

    public function testInvalidLoginFails()
    {
        $credentials = [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ];

        $this->expectException(\Exception::class);
        AuthController::login($credentials['email'], $credentials['password']);
    }
}
