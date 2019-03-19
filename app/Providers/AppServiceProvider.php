<?php

namespace App\Providers;

use App\PhotoStore;
use Aws\S3\S3Client;
use App\IdObfuscator;
use App\S3PhotoStore;
use GuzzleHttp\Client;
use App\S3DirectUpload;
use App\Video\VideoInfo;
use App\OptimusIdObfuscator;
use Illuminate\Http\Request;
use App\Video\OEmbedVideoInfo;
use App\VerificationCodeGenerator;
use Illuminate\Support\ServiceProvider;
use App\RandomVerificationCodeGenerator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(OptimusIdObfuscator::class, function () {
            return new OptimusIdObfuscator(env('OPTIMUS_PRIME'), env('OPTIMUS_INVERSE'), env('OPTIMUS_RANDOM'));
        });

        $this->app->singleton(VerificationCodeGenerator::class, function () {
            return new RandomVerificationCodeGenerator();
        });

        $this->app->bind(S3DirectUpload::class, function () {
            return new S3DirectUpload(
                env('AWS_ACCESS_KEY'),
                env('AWS_SECRET'),
                env('AWS_S3_BUCKET'),
                env('AWS_S3_BUCKET_REGION')
            );
        });

        $this->app->singleton(S3Client::class, function () {
            return new S3Client([
                'region' => env('AWS_S3_BUCKET_REGION'),
                'version' => '2006-03-01',
                'credentials' => new \Aws\Credentials\Credentials(env('AWS_ACCESS_KEY'), env('AWS_SECRET')),
            ]);
        });

        $this->app->singleton(S3PhotoStore::class, function () {
            return new S3PhotoStore(
                $this->app->make(S3Client::class),
                $this->app->make(Client::class),
                env('AWS_S3_BUCKET'),
                env('AWS_S3_KEY_PREFIX', 'dev')
            );
        });

        $this->app->singleton(ContentEditableInput::class, function () {
            return new ContentEditableInput();
        });

        $this->app->bind(IdObfuscator::class, OptimusIdObfuscator::class);
        $this->app->bind(VerificationCodeGenerator::class, RandomVerificationCodeGenerator::class);
        $this->app->bind(VideoInfo::class, OEmbedVideoInfo::class);
        $this->app->bind(PhotoStore::class, S3PhotoStore::class);
    }
}
