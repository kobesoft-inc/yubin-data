<?php

namespace Kobesoft\YubinData;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * アプリケーションサービスの起動
     *
     * @return void
     */
    public function boot(): void
    {
        // マイグレーションファイルを登録する
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // コンソールコマンドを登録する
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\Commands\ImportYubinData::class,
            ]);
        }
    }
}
