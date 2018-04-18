<?php

namespace Tests\Web\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;

class LoginTest extends TestCase
{
    public function testBasicTest()
    {
        $response = $this->get('/');
        $response->assertRedirect('/web/login');
    }

    public function testSuccessLogin()
    {
        $response = $this->post('/web/login', ['email' => 'jjyyg1123@gmail.com', 'password' => 'password']);
        $user = User::where('email', 'jjyyg1123@gmail.com')->first();
        $this->assertAuthenticatedAs($user,'web')
            ->assertGuest('admin');
        $response->assertRedirect('/web/default');
    }

    public function testWrongEmailLogin()
    {
        $response = $this->post('/web/login', ['email' => 'jjyyg1123@gmail.co', 'password' => 'password']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $response->assertSessionHas('danger','帳號或密碼錯誤.')
            ->assertRedirect('/web/login');
    }

    public function testWrongPasswordLogin()
    {
        $response = $this->post('/web/login', ['email' => 'jjyyg1123@gmail.com', 'password' => 'passwor']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $response->assertSessionHas('danger','帳號或密碼錯誤.')
            ->assertRedirect('/web/login');
    }

    public function testInvalidEmailLogin()
    {
        $response = $this->post('/web/login', ['email' => '123g@123g', 'password' => 'password']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('email')[0],"請填入有效的email");

        $response = $this->post('/web/login', ['email' => '123g.123g', 'password' => 'password']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('email')[0],"請填入有效的email");

        $response = $this->post('/web/login', ['email' => '123g@123g..123g', 'password' => 'password']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('email')[0],"請填入有效的email");

        $response = $this->post('/web/login', ['email' => '123g.123g@123g.123g', 'password' => 'password']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('email')[0],"請填入有效的email");
    }

    public function testShortPasswordLogin()
    {
        $response = $this->post('/web/login', ['email' => 'jjyyg1123@gmail.com', 'password' => 'pass']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('password')[0],"密碼長度至少為6字元");
    }

    public function testEmptyEmailLogin()
    {
        $response = $this->post('/web/login', ['email' => '', 'password' => 'password']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('email')[0],"請填入email");
    }

    public function testEmptyPasswordLogin()
    {
        $response = $this->post('/web/login', ['email' => 'jjyyg1123@gmail.com', 'password' => '']);
        $this->assertGuest('web')
            ->assertGuest('admin');
        $errors = session('errors');
        $response->assertSessionHasErrors()
            ->assertRedirect('/web/login');
        $this->assertEquals($errors->get('password')[0],"請填入密碼");
    }
}
