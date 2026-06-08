<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DynamicTemplateExport implements WithMultipleSheets
{
    protected $template;
    protected $withData;
    protected $filters;

    public function __construct($template, $withData = false, array $filters = [])
    {
        $this->template = $template;
        $this->withData = $withData;
        $this->filters = $filters;
    }

    public function sheets(): array
    {
        return [
            new TemplateSheetExport($this->template, $this->withData, $this->filters),
            new LookupsSheetExport($this->template->pondok_id),
        ];
    }
}