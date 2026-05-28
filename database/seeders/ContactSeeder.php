<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Faker\Factory;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        //  日本語のFakerを生成
        $faker = Factory::create('ja_JP');

        $categories = Category::all();
        $tags = Tag::all();

        for ($i = 0; $i < 20; $i++) {
            $contact = Contact::create([
                'category_id' => $categories->random()->id,
                'first_name' => $faker->lastName,
                'last_name' => $faker->firstName,
                'gender' => $faker->numberBetween(1, 3), // 1,2,3のいずれか
                'email' => $faker->safeEmail,
                //  桁数・ハイフンなしバリデーションを通過する数字のみを生成
                'tel' => $faker->numerify($faker->randomElement(['090########', '080########', '03#######'])),
                'address' => $faker->prefecture . $faker->city . $faker->streetAddress,
                'building' => $faker->optional(0.7)->realText(10) . 'ビル', // 70%の確率で建物名あり
                'detail' => $faker->realText(50),
            ]);

            //  既存のタグからランダムに1〜3件選んで中間テーブル（contact_tag）に紐付ける
            $randomTags = $tags->random(rand(1, 3));
            $contact->tags()->attach($randomTags->pluck('id')->toArray());
        }
    }
}