<?php

namespace App\Filament\Imports;

use App\Models\Member;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class MemberImporter extends Importer
{
    protected static ?string $model = Member::class;

    public static function templateColumns(): array
    {
        return [
            'firstname',
            'lastname',
            'email',
            'phone',
            'address',
            'postal_code',
            'city',
            'birthdate',
            'gender',
        ];
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('firstname')
                ->label('Fornavn')
                ->requiredMapping()
                ->guess(['fornavn', 'first name', 'forename'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['required', 'max:255']),
            ImportColumn::make('lastname')
                ->label('Efternavn')
                ->requiredMapping()
                ->guess(['efternavn', 'last name', 'surname'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->guess(['e-mail', 'e mail', 'mail'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['required', 'email', 'max:255']),
            ImportColumn::make('phone')
                ->label('Telefon')
                ->guess(['telefon', 'tlf', 'tlf.', 'mobil', 'phone number'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('address')
                ->label('Adresse')
                ->guess(['adresse', 'street', 'vejnavn'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('postal_code')
                ->label('Postnummer')
                ->guess(['postnummer', 'postnr', 'post code', 'postcode', 'zip'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('city')
                ->label('By')
                ->guess(['by', 'bynavn', 'town', 'city name'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeCsvText($state))
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('birthdate')
                ->label('Fødselsdato')
                ->guess(['fødselsdato', 'foedselsdato', 'dato', 'birthday', 'date of birth', 'dob'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeDateToDatabaseFormat($state))
                ->rules(['nullable', 'date'])
                ->example('15-03-1990'),
            ImportColumn::make('gender')
                ->label('Køn')
                ->guess(['køn', 'koen', 'sex'])
                ->castStateUsing(fn (?string $state): ?string => static::normalizeGender($state))
                ->rules(['nullable', 'in:male,female']),
        ];
    }

    public static function normalizeCsvText(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return mb_convert_encoding($value, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
    }

    public static function normalizeDateToDatabaseFormat(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        // Accept Danish formats: DD.MM.YYYY or DD-MM-YYYY → YYYY-MM-DD
        if (preg_match('/^(\d{1,2})[.\-](\d{1,2})[.\-](\d{4})$/', $value, $matches)) {
            return sprintf('%04d-%02d-%02d', $matches[3], $matches[2], $matches[1]);
        }

        // Already Y-m-d or another format strtotime understands
        return $value;
    }

    public static function normalizeGender(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $normalizedValue = mb_strtolower(trim(static::normalizeCsvText($value)));

        return match ($normalizedValue) {
            'male', 'm', 'man', 'mand', 'dreng' => 'male',
            'female', 'f', 'woman', 'kvinde', 'kv', 'pige' => 'female',
            default => $normalizedValue,
        };
    }

    public function resolveRecord(): Member
    {
        return new Member();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your member import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
