<?php

namespace Tests\Feature\Api;

use Faker\Factory;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductsTest extends TestCase
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

    /**
     * @test
     */
    public function non_authenticated_users_cannot_access_the_following_endpoints_for_the_product_api()
    {
        $store = $this->json('POST', '/api/products');
        $store->assertStatus(401);

        $update = $this->json('PUT', '/api/products/-1');
        $update->assertStatus(401);

        $destroy = $this->json('DELETE', '/api/products/-1');
        $destroy->assertStatus(401);
    }


    /**
     * @test
     */
    public function non_authenticated_users_can_access_the_following_endpoints_for_the_product_api()
    {
        $show = $this->json('GET', '/api/products');
        $show->assertStatus(200);

        $show = $this->json('GET', '/api/products/-1');
        $show->assertStatus(200);
    }

    /**
     * @test
     */
    public function can_create_a_product()
    {
        $faker = Factory::create();
        $user = Sanctum::actingAs(User::factory()->create());

        $response = $this->actingAs($user, 'api')->json('POST', '/api/products', [
            'name' => $name = $faker->company,
            'slug' => Str::slug($name),
            'description' => $text = $faker->text,
            'price' => $price = random_int(10, 100)
        ]);

        $response->assertJsonStructure([
            'id', 'name', 'slug', 'description', 'price'
        ])
        ->assertJson([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $text,
            'price' => $price
        ])
        ->assertStatus(201);

        $this->assertDatabaseHas('products', [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $text,
            'price' => $price
        ]);
    }


    /**
     * @test
     */
    public function can_update_a_product()
    {
        $product = Product::factory()->create();
        $user = Sanctum::actingAs(User::factory()->create());

        $response = $this->actingAs($user, 'api')->json('PUT', "api/products/$product->id", [
            'name' => $product->name.'_updated',
            'slug' => Str::slug($product->name.'_updated'),
            'description' => $product->description,
            'price' => $product->price + 10,
            'created_at' => NULL,
            'updated_at' => NULL
        ]);

        $response->assertStatus(200)
            ->assertExactJson([
                'id' => $product->id,
                'name' => $product->name.'_updated',
                'slug' => Str::slug($product->name.'_updated'),
                'description' => $product->description,
                'price' => $product->price + 10,
                'created_at' => NULL,
                'updated_at' => NULL
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => $product->name.'_updated',
            'slug' => Str::slug($product->name.'_updated'),
            'price' => $product->price + 10
        ]);
    }


    /**
     * @test
     */
    public function can_delete_a_product()
    {
        $product = Product::factory()->create();
        $user = Sanctum::actingAs(User::factory()->create());

        $response = $this->actingAs($user, 'api')->json('DELETE', "api/products/$product->id");
        $response->assertStatus(200)->assertSee(null);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }


    /**
     * @test
     */
    /*public function will_fail_with_validation_errors_when_creating_a_product_with_wrong_inputs()
    {
        $product = Product::factory()->create();
        $user = Sanctum::actingAs(User::factory()->create());

        $response = $this->actingAs($user)->json('POST', '/api/products', [
            'name' => $product->name,
            'price' => 'aaa'
        ]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'price' => [
                        'The price must be an integer.'
                    ],

                    'name' => [
                        'The name has already been taken.'
                    ]
                ]
            ]);

        $response = $this->actingAs($user, 'api')->json('POST', '/api/products', [
            'name' => '',
            'price' => 100
        ]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.'
                    ]
                ]
            ]);

        $response = $this->actingAs($user, 'api')->json('POST', '/api/products', [
            'name' => Str::random(65),
            'price' => 100
        ]);

        $response->assertStatus(422)
            ->assertExactJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name may not be greater than 64 characters.'
                    ]
                ]
            ]);
    }*/

}
