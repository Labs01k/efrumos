<?php


namespace App\Exports;

use App\Models\Subscribers;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

//use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromCollection;

class SubscribersExport implements FromArray, WithHeadings
{

    public function headings(): array
    {
        return [
            'Id',
            'Email',
            'Date',
        ];
    }

    public function array(): array
    {
        $subscribers = Subscribers::orderBy('created_at', 'desc')->get(['id', 'email', 'created_at'])->toArray();

        return $subscribers;
    }
}
