<?php

use App\Enums\RoleEnum;
use App\Models\Address;
use App\Models\Client;
use App\Models\Company;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\AddressSeeder;
use Database\Seeders\RoleSeeder;

use function Pest\Laravel\seed;

beforeEach(function() {
    seed(RoleSeeder::class);
    seed(AddressSeeder::class);
});

test('seller user can only see clients on his tenant', function() {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $user1 = User::factory()
        ->state(['role_id' => RoleEnum::SELLER])
        ->has(Seller::factory()
            ->state(['company_id' => $company1->id]))
        ->create();

    $user2 = User::factory()
        ->state(['role_id' => RoleEnum::SELLER])
        ->has(Seller::factory()
            ->state(['company_id' => $company2->id]))
        ->create();

    Client::factory()
        ->count(10)
        ->create([
            'company_id' => $company1->id,
            'address_id' => Address::first()->id
        ]);

    Client::factory()
        ->count(10)
        ->create([
            'company_id' => $company2->id,
            'address_id' => Address::first()->id
        ]);

    $this->assertSame(20, Client::count());

    auth()->loginUsingId($user1->id);

    $this->assertSame(10, Client::count());
});

test('seller user can only see sellers on his tenant', function() {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();

    $user1 = User::factory()
        ->state(['role_id' => RoleEnum::SELLER])
        ->has(Seller::factory()
            ->state(['company_id' => $company1->id]))
        ->create();

    $user2 = User::factory()
        ->state(['role_id' => RoleEnum::SELLER])
        ->has(Seller::factory()
            ->state(['company_id' => $company2->id]))
        ->create();

    Seller::factory()
        ->count(10)
        ->create(['company_id' => $company1->id]);

    Seller::factory()
        ->count(10)
        ->create(['company_id' => $company2->id]);

    $this->assertSame(22, Seller::count());

    auth()->loginUsingId($user1->id);

    $this->assertSame(11, Seller::count());
});
