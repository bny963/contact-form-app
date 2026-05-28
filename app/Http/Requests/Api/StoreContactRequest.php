<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreContactRequest extends FormRequest
{
    /**
     * 認証はすべて許可
     */
    public function authorize()
    {
        return true;
    }

    /**
     * API作成・更新時のバリデーションルール
     */
    public function rules()
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:1,2,3',
            'email' => 'required|string|email|max:255',
            //  不正な電話番号形式を拒否（ハイフンなし10桁〜11桁の数字、または一般的な電話番号形式を想定）
            'tel' => 'required|string|regex:/^[0-9]{10,11}$/',
            'address' => 'required|string|max:255',
            'building' => 'nullable|string|max:255',
            'detail' => 'required|string|max:120', // 120文字制限などの仕様に合わせて調整
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ];
    }

    /**
     * バリデーションエラー時は 422 JSON を返却
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors()
        ], 422));
    }
}