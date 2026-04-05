<?php

use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;

class FakeRedirectResource
{
    public static function getUrl(string $name): string
    {
        return "/resources/{$name}";
    }
}

it('redirects create pages to index url', function () {
    $page = new class extends CreateRecordRedirectToIndex
    {
        protected static string $resource = FakeRedirectResource::class;
    };

    $method = new ReflectionMethod($page, 'getRedirectUrl');
    $method->setAccessible(true);

    expect($method->invoke($page))->toBe('/resources/index');
});

it('redirects edit pages to index url', function () {
    $page = new class extends EditRecordRedirectToIndex
    {
        protected static string $resource = FakeRedirectResource::class;
    };

    $method = new ReflectionMethod($page, 'getRedirectUrl');
    $method->setAccessible(true);

    expect($method->invoke($page))->toBe('/resources/index');
});
