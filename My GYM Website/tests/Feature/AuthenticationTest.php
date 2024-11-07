<?php
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class AuthenticationTest extends TestCase
{
    protected $http;

    protected function setUp(): void
    {
        parent::setUp();
        session_start();
    }

    public function testUserCanLogin()
    {
        $response = $this->makeRequest('POST', '/php/login.php', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('user', $response['data']);
    }

    public function testUserCanLogout()
    {
        $_SESSION['user_id'] = 1;
        
        $response = $this->makeRequest('POST', '/php/logout.php');
        
        $this->assertEquals(200, $response['status']);
        $this->assertEmpty($_SESSION);
    }

    public function testInvalidLoginAttempt()
    {
        $response = $this->makeRequest('POST', '/php/login.php', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword'
        ]);

        $this->assertEquals('error', $response['status']);
    }

    protected function makeRequest($method, $path, $data = [])
    {
        $ch = curl_init();
        
        $url = 'http://localhost' . $path;
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}
