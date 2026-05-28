<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ContactRelationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        error_reporting(E_ALL & ~E_DEPRECATED);
    }

    /**
     * 要件：ContactモデルからCategoryおよびTagへのリレーションテスト
     */
    public function test_contact_relations_work_correctly()
    {
        // 1. テストデータの準備
        $category = Category::create(['content' => '製品への苦情']);
        $tag1 = Tag::create(['name' => '要対応']);
        $tag2 = Tag::create(['name' => '至急']);

        $contact = Contact::create([
            'category_id' => $category->id,
            'first_name' => 'テスト',
            'last_name' => '太郎',
            'gender' => '1',
            'email' => 'relation@example.com',
            'tel' => '09011112222',
            'address' => '大阪府生野区',
            'detail' => 'リレーションのテストです'
        ]);

        // 中間テーブルにタグを紐付け
        $contact->tags()->attach([$tag1->id, $tag2->id]);

        // 2. 検証：Contact から Category が正しく取得できるか
        $this->assertEquals('製品への苦情', $contact->category->content);

        // 3. 検証：Contact から Tags が正しく2つ取得できるか
        $this->assertCount(2, $contact->tags);
        $this->assertEquals('要対応', $contact->tags->first()->name);
    }
}