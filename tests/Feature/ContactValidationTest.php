<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactValidationTest extends TestCase
{
    //  RefreshDatabase が確実にテスト用DBを毎回リセットしてマイグレーションを回します
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 非推奨警告を非表示
        error_reporting(E_ALL & ~E_DEPRECATED);

        //  確実にDBにレコードを作成し、その作成された実際のIDをテストで使うようにします
        $category = Category::create(['content' => '商品について']);
        $tag = Tag::create(['name' => '重要']);

        // クラス内で使い回せるようにプロパティに保存
        $this->categoryId = $category->id;
        $this->tagId = $tag->id;
    }

    /**
     * 要件：API検索バリデーションのテスト
     */
    public function test_index_contact_request_validation()
    {
        $response = $this->json('GET', '/api/v1/contacts', [
            'keyword' => 'テスト',
            'gender' => '1',
            'category_id' => $this->categoryId, //  実際に作られたIDを使用
            'date' => '2026-05-28',
            'per_page' => 10
        ]);
        $response->assertStatus(200);

        // 不正な性別(4)でアクセス -> 422
        $response = $this->json('GET', '/api/v1/contacts', [
            'gender' => '4'
        ]);
        $response->assertStatus(422);

        // 存在しないカテゴリIDでアクセス -> 422
        $response = $this->json('GET', '/api/v1/contacts', [
            'category_id' => 99999
        ]);
        $response->assertStatus(422);
    }

    /**
     * 要件：保存バリデーション（必須項目・不正な電話番号形式の拒否）のテスト
     */
    public function test_store_contact_request_validation()
    {
        $validData = [
            'category_id' => $this->categoryId, //  実際に作られたIDを使用
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => '1',
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'detail' => 'テスト内容です',
            'tag_ids' => [$this->tagId] //  実際に作られたIDを使用
        ];

        $response = $this->json('POST', '/api/v1/contacts', $validData);

        // まだエラーが出る場合は中身を出力
        if ($response->status() !== 201) {
            dump($response->json());
        }

        $response->assertStatus(201);

        // 不正な電話番号形式で登録 -> 422
        $invalidData = array_merge($validData, ['tel' => '090-1234-abcd']);
        $response = $this->json('POST', '/api/v1/contacts', $invalidData);
        $response->assertStatus(422);
    }
}