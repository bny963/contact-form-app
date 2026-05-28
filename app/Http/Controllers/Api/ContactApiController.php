<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Http\Requests\Api\IndexContactRequest;
use App\Http\Requests\Api\StoreContactRequest;
use Illuminate\Http\JsonResponse;

class ContactApiController extends Controller
{
    /**
     * 応用要件：お問い合わせ一覧API (GET /api/v1/contacts)
     */
    public function index(IndexContactRequest $request): JsonResponse
    {
        $query = Contact::with(['category', 'tags'])->latest();

        // キーワード検索
        if ($request->filled('keyword')) {
            $keyword = $request->keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('first_name', 'like', "%{$keyword}%")
                    ->orWhere('last_name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // 性別検索 (1,2,3)
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // カテゴリ検索
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 日付検索
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // per_page の指定があればそれを使用し、無ければ要件通りの「7件ごと」にする
        $perPage = $request->input('per_page', 7);
        $contacts = $query->paginate($perPage);

        return response()->json($contacts, 200);
    }

    /**
     * 応用要件：お問い合わせ詳細API (GET /api/v1/contacts/{id})
     */
    public function show($id): JsonResponse
    {
        //  findOrFail を使うことで、存在しないIDの時は自動で404エラーJSONを返却します
        $contact = Contact::with(['category', 'tags'])->findOrFail($id);

        return response()->json($contact, 200);
    }

    /**
     * 応用要件：お問い合わせ作成API (POST /api/v1/contacts)
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        // バリデーション済みデータを取得
        $validated = $request->validated();

        // レコード作成
        $contact = Contact::create($validated);

        // タグが選択されていれば中間テーブルに同期（sync）
        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->tag_ids);
        }

        //  作成成功時はステータスコード 201 (Created) を返却
        return response()->json($contact->load(['category', 'tags']), 201);
    }

    /**
     * 応用要件：お問い合わせ更新API (PUT /api/v1/contacts/{id})
     */
    public function update(StoreContactRequest $request, $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        $validated = $request->validated();
        $contact->update($validated);

        if ($request->has('tag_ids')) {
            $contact->tags()->sync($request->tag_ids);
        }

        return response()->json($contact->load(['category', 'tags']), 200);
    }

    /**
     * 応用要件：お問い合わせ削除API (DELETE /api/v1/contacts/{id})
     */
    public function destroy($id): JsonResponse
    {
        $contact = Contact::findOrFail($id);

        // 関連する中間テーブルのレコードも安全に削除してから本体を削除
        $contact->tags()->detach();
        $contact->delete();

        //  削除成功時はデータなしの 204 (No Content) を返却
        return response()->json(null, 204);
    }
}