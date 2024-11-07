<?php
namespace Tests\Feature;

use PHPUnit\Framework\TestCase;

class WorkoutManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        session_start();
        $_SESSION['user_id'] = 1;
    }

    public function testUserCanCreateWorkout()
    {
        $response = $this->makeRequest('POST', '/php/save_workout.php', [
            'name' => 'Test Workout',
            'category' => 'strength',
            'description' => 'Test workout description'
        ]);

        $this->assertEquals('success', $response['status']);
        $this->assertArrayHasKey('id', $response['data']);
    }

    public function testUserCanScheduleWorkout()
    {
        $response = $this->makeRequest('POST', '/php/save_schedule.php', [
            'workout_id' => 1,
            'date' => date('Y-m-d'),
            'time' => '10:00:00',
            'duration' => 60
        ]);

        $this->assertEquals('success', $response['status']);
    }

    public function testUserCanViewWorkouts()
    {
        $response = $this->makeRequest('GET', '/php/get_workouts.php');
        
        $this->assertEquals('success', $response['status']);
        $this->assertIsArray($response['data']);
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
