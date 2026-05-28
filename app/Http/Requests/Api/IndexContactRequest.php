<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class IndexContactRequest extends FormRequest
{
    /**
     * 認証はすべて許可（APIの仕様に合わせる）
     */
    public function authorize()
    {
        return true;
    }

    /**
     * API検索のバリデーションルール
     * 要件：キーワード、性別(1,2,3)、カテゴリ、日付、per_page
     */
    public function rules()
    {
        return [
            'keyword' => 'nullable|string|max:255',
            'gender' => 'nullable|in:1,2,3', // 💡 要件：不正な性別値を拒否（1,2,3のみ許可）
            'category_id' => 'nullable|integer|exists:categories,id',
            'date' => 'nullable|date_format:Y-m-d',
            'per_page' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     *  重要：APIなので、バリデーションエラー時は画面リダイレクトではなく
     * ステータスコード 422 の JSON エラーを返却するように制御します。
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}