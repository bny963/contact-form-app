<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    /**
     * PG04: 管理画面（一覧表示・検索・ページネーション）
     */
    public function index(Request $request)
    {
        // 1. クエリビルダを初期化（N+1問題対策で category と tags を Eager Loading）
        $query = Contact::with(['category', 'tags']);

        // 2.  検索処理の実装

        // ① 名前（姓・名）で部分一致検索（スペースが入っても探せるように、または単純に両方から探す）
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%"); // 💡 メールアドレスもキーワードに含める仕様の場合
            });
        }

        // ② 性別（1:男性, 2:女性, 3:その他）
        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }

        // ③ お問い合わせの種類（カテゴリID）
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // ④ 年月日（登録日）での検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // 3.  仕様書要件：最新順 ＆ 1ページ「7件」でページネーション
        // appends をつけることで、2ページ目に進んでも検索条件が維持されます
        $contacts = $query->latest()->paginate(7)->appends($request->query());

        // 検索窓のドロップダウン等で使うデータを取得
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact('contacts', 'categories', 'tags'));
    }

    /**
     * お問い合わせ削除処理
     */
    public function destroy($id)
    {
        //  該当のデータを取得して削除
        $contact = Contact::findOrFail($id);

        // 中間テーブル（contact_tag）の紐付けも安全に解除してから削除
        $contact->tags()->detach();
        $contact->delete();

        return redirect()->route('admin.index')->with('success', 'お問い合わせデータを削除しました');
    }
    /**
     * PG05: お問い合わせ詳細画面の表示
     */
    public function show($id)
    {
        // カテゴリとタグも含めてデータを1件取得
        $contact = Contact::with(['category', 'tags'])->findOrFail($id);

        // 💡 JSONではなく、用意されている専用のBladeファイルを表示させる
        return view('admin.show', compact('contact'));
    }
    /**
     * 応用要件：検索条件を引き継いだCSVエクスポート機能
     */
    public function export(Request $request): StreamedResponse
    {
        //  画面の一覧と同じ検索ロジックを適用してデータを取得
        $query = Contact::with('category')->latest();

        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('gender') && $request->gender !== 'all') {
            $query->where('gender', $request->gender);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $contacts = $query->get();

        //  ストリームレスポンスでCSVを生成して即座にダウンロード
        $response = new StreamedResponse(function () use ($contacts) {
            $stream = fopen('php://output', 'w');

            // Excelでの文字化けを防ぐBOMを出力
            fwrite($stream, pack('C*', 0xEF, 0xBB, 0xBF));

            // CSVヘッダー行
            fputcsv($stream, ['ID', 'お名前', '性別', 'メールアドレス', 'ご意見・お問い合わせ内容']);

            // データ行の書き込み
            foreach ($contacts as $contact) {
                $genderText = $contact->gender == 1 ? '男性' : ($contact->gender == 2 ? '女性' : 'その他');
                fputcsv($stream, [
                    $contact->id,
                    $contact->first_name . ' ' . $contact->last_name,
                    $genderText,
                    $contact->email,
                    $contact->detail,
                ]);
            }

            fclose($stream);
        });

        // レスポンスヘッダーの設定（テストコードが検証しているContent-Typeを正確に指定）
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="contacts_' . date('YmdHis') . '.csv"');

        return $response;
    }
}