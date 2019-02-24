<?php

namespace App\Providers;

use App\IdObfuscator;
use App\OptimusIdObfuscator;
use Illuminate\Http\Request;
use App\S3DirectUpload;
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
        $this->app->bind(IdObfuscator::class, OptimusIdObfuscator::class);

        $this->app->singleton(VerificationCodeGenerator::class, function () {
            return new RandomVerificationCodeGenerator();
        });
        $this->app->bind(VerificationCodeGenerator::class, RandomVerificationCodeGenerator::class);

        $this->app->bind(S3DirectUpload::class, function () {
            return new S3DirectUpload(
                env('AWS_ACCESS_KEY'),
                env('AWS_SECRET'),
                env('AWS_S3_BUCKET'),
                env('AWS_S3_BUCKET_REGION')
            );
        });
    }
}
