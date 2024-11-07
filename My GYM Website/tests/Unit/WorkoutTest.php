<?php
namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use MuscleZen\Controllers\WorkoutController;

class WorkoutTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_SESSION['user_id'] = 1;
    }

    public function testCreateWorkout()
    {
        $workoutData = [
            'name' => 'Test Workout',
            'category' => 'strength',
            'description' => 'Test workout description'
        ];

        $result = WorkoutController::createWorkout($workoutData);
        
        $this->assertTrue($result['status'] === 'success');
        $this->assertArrayHasKey('id', $result['data']);
    }

    public function testScheduleWorkout()
    {
        $scheduleData = [
            'workout_id' => 1,
            'date' => date('Y-m-d'),
            'time' => '10:00:00',
            'duration' => 60
        ];

        $result = WorkoutController::scheduleWorkout($scheduleData);
        
        $this->assertTrue($result['status'] === 'success');
        $this->assertArrayHasKey('id', $result['data']);
    }

    public function testGetUserWorkouts()
    {
        $workouts = WorkoutController::getUserWorkouts(1);
        
        $this->assertIsArray($workouts);
        $this->assertArrayHasKey('scheduled', $workouts);
        $this->assertArrayHasKey('completed', $workouts);
    }
}
