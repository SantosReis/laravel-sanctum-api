<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_example()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function users_can_authenticate(){
        
        /*$user = Sanctum::actingAs(User::factory()->create());
        $response = $this->json('POST', 'api/login', $user);
        $response->assertOk();*/
        //$data = $this->actingAs(User::factory()->create()->assertStatus(200));
        User::create([
            'name' => 'test',
            'email'=>'test@gmail.com',
            'password' => bcrypt('secret123$')
        ]);

        $data = [
            'email' => 'test@gmail.com',
            'password' => 'secret123$',
        ];
        
        $response = $this->json('POST', 'api/login', $data);
        $response->assertStatus(201);
        $this->assertArrayHasKey('token', $response->json());

    }
}
