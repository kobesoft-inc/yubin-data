<?php

namespace Kobesoft\YubinData\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Kobesoft\YubinData\Models\JpZipCode;
use Kobesoft\YubinData\Services\JpZipCode\Import;

class ImportJpZipCodes extends Command
{
    /**
     * コンソールコマンドの名前と署名
     *
     * @var string
     */
    protected $signature = 'green:import:jp-zip-codes';

    /**
     * コンソールコマンドの説明
     *
     * @var string
     */
    protected $description = '日本の郵便番号データベースをインポートする';

    /**
     * コンソールコマンドを実行する
     */
    public function handle()
    {
        // 開始のメッセージを表示する
        $this->info('日本の郵便番号データベースをインポートします。');

        // 郵便番号データをダウンロードする
        $this->info('郵便番号データをダウンロードしています。');
        $inserts = Import::import();

        // 郵便番号データを削除する
        JpZipCode::truncate();

        // 郵便番号データをデータベースに保存する
        $this->info('郵便番号データをデータベースに保存しています。');
        $this->output->progressStart(count($inserts));
        DB::transaction(function () use ($inserts) {
            foreach (array_chunk($inserts, 1000) as $chunk) {
                JpZipCode::insert($chunk);
                $this->output->progressAdvance(count($chunk));
            }
        });
        $this->output->progressFinish();

        // 完了のメッセージを表示する
        $this->info('日本の郵便番号データベースのインポートが完了しました。');
    }
}
