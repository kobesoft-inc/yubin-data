<?php

namespace Kobesoft\YubinData\Services\Yubin;

use Closure;
use Exception;
use ZipArchive;

class YubinDownloader
{
    const KEN_URL = 'https://www.post.japanpost.jp/zipcode/dl/utf/zip/utf_ken_all.zip';
    const KEN_FILE = 'utf_all.csv';
    const JIGYOSYO_URL = 'https://www.post.japanpost.jp/zipcode/dl/jigyosyo/zip/jigyosyo.zip';
    const JIGYOSYO_FILE = 'JIGYOSYO.CSV';

    /**
     * 一時ファイルのディレクトリのパスを返す
     *
     * @param string|null $filename ファイル名
     * @return string 一時ファイルのディレクトリのパス
     */
    protected static function temporaryPath(?string $filename = null): string
    {
        $path = storage_path('app/jp-zip-code');
        if (!is_dir($path)) {
            mkdir($path);
        }
        if ($filename) {
            $path .= '/' . $filename;
        }
        return $path;
    }

    /**
     * URLからファイルをダウンロードする
     *
     * @param string $url ダウンロードするファイルのURL
     * @return string ダウンロードしたファイルのパス
     * @throws Exception
     */
    protected static function downloadFromUrl(string $url): string
    {
        $path = self::temporaryPath(basename($url));
        $fp = fopen($path, 'w+');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        if (!curl_exec($ch)) {
            throw new Exception(curl_error($ch));
        }
        curl_close($ch);
        fclose($fp);
        return $path;
    }

    /**
     * ZIPファイルを解凍する
     *
     * @param string $zipFilename ZIPファイルのパス
     * @param string $filename 解凍するファイルの名前
     * @return string 解凍したファイルのパス
     */
    protected static function extract(string $zipFilename, string $filename): string
    {
        $zip = new ZipArchive();
        $zip->open($zipFilename);
        $zip->extractTo(self::temporaryPath());
        $zip->close();
        return self::temporaryPath($filename);
    }

    /**
     * ファイルの文字コードをUTF-8に変換する
     *
     * @param string $path ファイルのパス
     * @param string $fromEncoding 変換前の文字コード
     */
    protected static function convertEncodingToUtf8(string $path, string $fromEncoding): void
    {
        file_put_contents($path, mb_convert_encoding(file_get_contents($path), 'utf-8', $fromEncoding));
    }

    /**
     * 全国の郵便番号データをインポートする
     *
     * @param Closure $closure
     * @return void
     * @throws Exception
     */
    protected static function downloadKen(Closure $closure)
    {
        // ファイルをダウンロードして解凍する
        $zipFilename = self::downloadFromUrl(self::KEN_URL);
        $csvFilename = self::extract($zipFilename, self::KEN_FILE);

        // CSVファイルを処理する
        $fp = fopen($csvFilename, 'r');
        while ($row = fgetcsv($fp)) {
            $closure(YubinRecord::makeFromKen($row));
        }
        fclose($fp);

        // ファイルを削除する
        unlink($csvFilename);
        unlink($zipFilename);
    }

    /**
     * 事業所の郵便番号データをインポートする
     *
     * @param Closure $closure
     * @return void
     * @throws Exception
     */
    protected static function downloadJigyosyo(Closure $closure)
    {
        // ファイルをダウンロードして解凍する
        $zipFilename = self::downloadFromUrl(self::JIGYOSYO_URL);
        $csvFilename = self::extract($zipFilename, self::JIGYOSYO_FILE);

        // 文字コードを変換する
        self::convertEncodingToUtf8($csvFilename, 'SJIS-win');

        // CSVファイルを処理する
        $fp = fopen($csvFilename, 'r');
        while ($row = fgetcsv($fp)) {
            $closure(YubinRecord::makeFromJigyosyo($row));
        }
        fclose($fp);

        // ファイルを削除する
        unlink($csvFilename);
        unlink($zipFilename);
    }

    /**
     * 郵便番号データをダウンロードし、挿入データを返す
     *
     * @return array
     * @throws Exception
     */
    public static function download(): array
    {
        $zipCodes = collect();
        YubinDownloader::downloadKen(function (YubinRecord $record) use ($zipCodes) {
            $zipCodes->push($record);
        });
        YubinDownloader::downloadJigyosyo(function (YubinRecord $record) use ($zipCodes) {
            $zipCodes->push($record);
        });
        return $zipCodes
            ->groupBy('zipCode')
            ->map(fn($records) => YubinRecord::unify($records->toArray())->toArray())
            ->toArray();
    }
}
