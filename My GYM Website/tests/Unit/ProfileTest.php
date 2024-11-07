<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use MuscleZen\Controllers\ProfileController;

class ProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION['user_id'] = 1; // Mock authenticated user
    }

    public function testGetUserProfile()
    {
        $profile = ProfileController::getProfile(1);
        
        $this->assertIsArray($profile);
        $this->assertArrayHasKey('username', $profile);
        $this->assertArrayHasKey('email', $profile);
    }

    public function testUpdateProfile()
    {
        $data = [
            'username' => 'TestUser',
            'dob' => '1990-01-01',
            'gender' => 'male'
        ];

        $result = ProfileController::updateProfile(1, $data);
        
        $this->assertTrue($result['status'] === 'success');
        $this->assertEquals($data['username'], $result['data']['username']);
    }

    public function testInvalidProfileUpdate()
    {
        $this->expectException(\Exception::class);
        
        ProfileController::updateProfile(1, [
            'username' => '', // Invalid empty username
            'dob' => 'invalid-date'
        ]);
    }
}
