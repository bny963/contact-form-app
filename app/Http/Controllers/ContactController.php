<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * PG01: お問い合わせフォーム入力ページ
     */
    public function index()
    {
        //  命名規則遵守：意味のない変数名（$a, $xなど）は使わない
        $categories = Category::all();
        $tags = Tag::all();

        // 提供されたBlade（resources/views/contact/index.blade.php）に変数を渡して表示
        return view('contact.index', compact('categories', 'tags'));
    }
}