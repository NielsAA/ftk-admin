<?php

use App\Filament\Imports\MemberImporter;

it('keeps utf8 danish characters unchanged', function () {
    $value = 'Søren Åge';

    expect(MemberImporter::normalizeCsvText($value))->toBe($value);
});

it('normalizes latin1 encoded danish text to utf8', function () {
    $utf8Value = 'Møn æble århus';
    $latin1Value = mb_convert_encoding($utf8Value, 'ISO-8859-1', 'UTF-8');

    expect(MemberImporter::normalizeCsvText($latin1Value))->toBe($utf8Value);
});

it('returns null or empty values unchanged', function (?string $value) {
    expect(MemberImporter::normalizeCsvText($value))->toBe($value);
})->with([null, '']);

it('converts danish dot date format to database format', function () {
    expect(MemberImporter::normalizeDateToDatabaseFormat('15.03.1990'))->toBe('1990-03-15');
});

it('converts danish dash date format to database format', function () {
    expect(MemberImporter::normalizeDateToDatabaseFormat('15-03-1990'))->toBe('1990-03-15');
});

it('passes iso date format through unchanged', function () {
    expect(MemberImporter::normalizeDateToDatabaseFormat('1990-03-15'))->toBe('1990-03-15');
});

it('returns null or empty date values unchanged', function (?string $value) {
    expect(MemberImporter::normalizeDateToDatabaseFormat($value))->toBe($value);
})->with([null, '']);

it('normalizes danish male values to male', function (string $value) {
    expect(MemberImporter::normalizeGender($value))->toBe('male');
})->with(['male', 'M', 'mand', 'Man']);

it('normalizes danish female values to female', function (string $value) {
    expect(MemberImporter::normalizeGender($value))->toBe('female');
})->with(['female', 'F', 'kvinde', 'Kv']);

it('returns null or empty gender values unchanged', function (?string $value) {
    expect(MemberImporter::normalizeGender($value))->toBe($value);
})->with([null, '']);
