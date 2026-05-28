<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $category;
    protected $tag;

    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED);

        // テスト用の共通マスタデータ作成
        $this->category = Category::create(['content' => '製品について']);
        $this->tag = Tag::create(['name' => '重要']);

        // テスト用の管理者ユーザー作成
        $this->adminUser = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);
    }

    /**
     * 1. 画面アクセス ＆ 認証機能のテスト
     */
    public function test_admin_pages_require_authentication()
    {
        //  未ログイン状態で管理画面にアクセス -> ログイン画面へリダイレクトされること
        $response = $this->get('/admin');
        $response->assertRedirect('/login');

        //  管理者としてログインしてアクセス -> 200 OK になること
        $response = $this->actingAs($this->adminUser)->get('/admin');
        $response->assertStatus(200);
    }

    /**
     * 2. CSVエクスポート機能のテスト（応用要件）
     */
    public function test_csv_export_works_correctly()
    {
        Contact::create([
            'category_id' => $this->category->id,
            'first_name' => 'CSV',
            'last_name' => '太郎',
            'gender' => '1',
            'email' => 'csv@example.com',
            'tel' => '09012345678',
            'address' => '北海道美唄市',
            'detail' => 'CSV出力のテストです'
        ]);

        // 💡 500エラーの本当の原因を画面に強制表示させるため、以下を一時的に追記
        $this->withoutExceptionHandling();

        $response = $this->actingAs($this->adminUser)->get('/admin/export?keyword=CSV');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    /**
     * 3. 公開APIの一連のCRUD機能テスト（応用要件）
     */
    public function test_api_crud_endpoints()
    {
        // --- C: Create (作成) ---
        $storeData = [
            'category_id' => $this->category->id,
            'first_name' => 'API',
            'last_name' => '次郎',
            'gender' => '2',
            'email' => 'api@example.com',
            'tel' => '08012345678',
            'address' => '大阪市生野区',
            'detail' => 'API作成のテストです',
            'tag_ids' => [$this->tag->id]
        ];
        $response = $this->json('POST', '/api/v1/contacts', $storeData);
        $response->assertStatus(201); // Created
        $contactId = $response->json('id');

        // --- R: Read (一覧・詳細) ---
        // 一覧
        $response = $this->json('GET', '/api/v1/contacts');
        $response->assertStatus(200);

        // 詳細
        $response = $this->json('GET', "/api/v1/contacts/{$contactId}");
        $response->assertStatus(200);
        $response->assertJsonPath('first_name', 'API');

        // --- U: Update (更新) ---
        $updateData = array_merge($storeData, ['first_name' => 'API更新版']);
        $response = $this->json('PUT', "/api/v1/contacts/{$contactId}", $updateData);
        $response->assertStatus(200);

        // --- D: Delete (削除) ---
        $response = $this->json('DELETE', "/api/v1/contacts/{$contactId}");
        $response->assertStatus(204); // No Content

        // 削除後に詳細にアクセスしたら 404 Not Found になること
        $response = $this->json('GET', "/api/v1/contacts/{$contactId}");
        $response->assertStatus(404);
    }
}