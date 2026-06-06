<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DynamicTemplateExport implements WithMultipleSheets
{
    protected $template;
    protected $withData;

    public function __construct($template, $withData = false)
    {
        $this->template = $template;
        $this->withData = $withData;
    }

    public function sheets(): array
    {
        return [
            new TemplateSheetExport($this->template, $this->withData),
            new LookupsSheetExport($this->template->pondok_id),
        ];
    }
}