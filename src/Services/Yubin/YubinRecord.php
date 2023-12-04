<?php

namespace Kobesoft\YubinData\Services\Yubin;

class YubinRecord
{
    /**
     * コンストラクタ
     */
    public function __construct(
        public ?string $zipCode = null,
        public ?string $prefecture = null,
        public ?string $city = null,
        public ?string $town = null,
        public ?string $officeAddress = null,
        public ?string $officeName = null,
    )
    {
    }

    /**
     * 全国の郵便番号データからレコードを作成する
     * @param array $row CSVの行
     * @return YubinRecord 郵便番号レコード
     */
    public static function makeFromKen(array $row): YubinRecord
    {
        return new YubinRecord(
            zipCode: $row[2],
            prefecture: $row[6],
            city: $row[7],
            town: $row[8],
        );
    }

    /**
     * 事業所の郵便番号データからレコードを作成する
     * @param array $row CSVの行
     * @return YubinRecord 郵便番号レコード
     */
    public static function makeFromJigyosyo(array $row): YubinRecord
    {
        return new YubinRecord(
            zipCode: $row[7],
            prefecture: $row[3],
            city: $row[4],
            town: $row[5],
            officeAddress: $row[6],
            officeName: $row[2],
        );
    }

    /**
     * コメントを除いた町域名を返す
     *
     * @return string
     */
    public function getTownWithoutComment(): string
    {
        if ($this->town === '以下に掲載がない場合') {
            return '';
        }
        $pos = strpos($this->town, '（');
        return $pos === false ? $this->town : substr($this->town, 0, $pos);
    }

    /**
     * 配列に変換する
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'zip_code' => $this->zipCode,
            'prefecture' => $this->prefecture,
            'city' => $this->city,
            'town' => $this->getTownWithoutComment(),
            'office_address' => $this->officeAddress,
            'office_name' => $this->officeName,
        ];
    }

    /**
     * 先頭から共通する部分を返す
     *
     * @param string $str1 文字列1
     * @param string $str2 文字列2
     * @return string 先頭から共通する部分
     */
    public static function commonSubstringFromHeading(string $str1, string $str2): string
    {
        $n = min(mb_strlen($str1), mb_strlen($str2));
        for ($i = 1; $i < $n; $i++) {
            if (mb_substr($str1, 0, $i) !== mb_substr($str2, 0, $i)) {
                return mb_substr($str1, 0, $i - 1);
            }
        }
        return mb_substr($str1, 0, $n);
    }

    /**
     * 共通化した郵便番号レコードを返す
     *
     * @param array $records
     * @return YubinRecord
     */
    public static function unifyRecords(array $records): YubinRecord
    {
        $unified = array_pop($records);
        foreach ($records as $record) {
            $unified->city = self::commonSubstringFromHeading($unified->city, $record->city);
            $unified->town = self::commonSubstringFromHeading($unified->town, $record->town);
            $unified->officeAddress = $unified->officeAddress === $record->officeAddress ? $unified->officeAddress : null;
            $unified->officeName = $unified->officeName === $record->officeName ? $unified->officeName : null;
        }
        if (count($records) > 0) {
            self::$unifiedData[] = $unified;
        }
        return $unified;
    }

    public static $unifiedData = [];
}
