<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use App\Http\Requests\StoreContactRequest;
use App\Models\Contact;

class ContactController extends Controller
{
    /**
     * PG01: お問い合わせフォーム入力ページ
     */
    public function index()
    {
        $categories = Category::all();
        $tags = Tag::all();

        // resources/views/contact/index.blade.php を表示
        return view('contact.index', compact('categories', 'tags'));
    }
    /**
     * PG02: お問い合わせフォーム確認ページ
     */
    public function confirm(StoreContactRequest $request)
    {
        
        $validated = $request->validated();

        $category = Category::find($request->category_id);

        $tags = [];
        if ($request->has('tag_ids')) {
            $tags = \App\Models\Tag::whereIn('id', $request->tag_ids)->get();
        }

        
        return view('contact.confirm', compact('validated', 'category', 'tags'));
    }
    /**
     * お問い合わせ送信処理（データベース保存 ＆ 中間テーブル記録）
     */
    public function store(StoreContactRequest $request)
    {
        // 1. バリデーション済みデータを取得
        $validated = $request->validated();

        // 2. contacts テーブルにレコードを保存
        $contact = Contact::create([
            'category_id' => $validated['category_id'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'gender' => $validated['gender'],
            'email' => $validated['email'],
            'tel' => $validated['tel'],
            'address' => $validated['address'],
            'building' => $validated['building'] ?? null,
            'detail' => $validated['detail'],
        ]);

        // 3.  タグが選択されている場合、中間テーブル（contact_tag）に記録する
        if ($request->has('tag_ids')) {
            $contact->tags()->attach($request->tag_ids);
        }

        // 4. 仕様書通り、サンクスページ（/thanks）へリダイレクト
        return redirect()->route('contact.thanks');
    }

    /**
     * PG03: サンクスページ表示
     */
    public function thanks()
    {
        // resources/views/contact/thanks.blade.php を表示
        return view('contact.thanks');
    }
}