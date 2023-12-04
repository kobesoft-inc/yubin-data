<?php

namespace Kobesoft\YubinData\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 日本の郵便番号
 *
 * @property string $zip_code 郵便番号
 * @property string $prefecture 都道府県・州
 * @property string $city 市区町村
 * @property string $town 町域
 * @property string $office_address 事業所の住所
 * @property string $office_name 事業所の名称
 */
class JpZipCode extends Model
{
    const CREATED_AT = null;
    const UPDATED_AT = null;

    /**
     * 一括代入する属性
     *
     * @var string[] $fillable
     */
    protected $fillable = [
        'zip_code',
        'prefecture',
        'city',
        'town',
        'office_name',
        'office_address',
    ];
}
