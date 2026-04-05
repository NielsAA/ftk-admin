<?php

use App\Filament\Resources\Pages\CreateRecordRedirectToIndex;
use App\Filament\Resources\Pages\EditRecordRedirectToIndex;
use Illuminate\Support\Facades\File;

function filamentResourcePageClasses(string $pagePrefix): \Illuminate\Support\Collection
{
    return collect(File::allFiles(app_path('Filament/Resources')))
        ->map(function ($file): string {
            $relativePath = str_replace(app_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());

            return 'App\\' . str_replace(['/', '\\', '.php'], ['\\', '\\', ''], $relativePath);
        })
        ->filter(fn (string $class) => str_contains($class, 'App\\Filament\\Resources\\')
            && str_contains($class, "\\Pages\\{$pagePrefix}")
            && ! str_contains($class, 'App\\Filament\\Resources\\Pages\\'))
        ->values();
}

it('ensures all filament resource create pages redirect to index after save', function () {
    $createPageClasses = filamentResourcePageClasses('Create');

    expect($createPageClasses)->not->toBeEmpty();

    $createPageClasses->each(function (string $class): void {
        expect(is_subclass_of($class, CreateRecordRedirectToIndex::class))->toBeTrue("{$class} must extend CreateRecordRedirectToIndex");
    });
});

it('ensures all filament resource edit pages redirect to index after save', function () {
    $editPageClasses = filamentResourcePageClasses('Edit');

    expect($editPageClasses)->not->toBeEmpty();

    $editPageClasses->each(function (string $class): void {
        expect(is_subclass_of($class, EditRecordRedirectToIndex::class))->toBeTrue("{$class} must extend EditRecordRedirectToIndex");
    });
});
