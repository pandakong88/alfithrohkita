<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Izin - {{ $perizinan->santri->nama_lengkap }}</title>
    <!-- Google Fonts for Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Reset & Page Setup */
        body {
            margin: 0;
            padding: 0;
            background: #ffffff;
            font-family: 'Times New Roman', Times, serif;
            color: #000000;
        }

        /* Grid repeating container */
        .print-grid {
            display: grid;
            width: 210mm; /* A4 Width */
            min-height: 297mm; /* A4 Height */
            box-sizing: border-box;
        }

        /* Grid layout sizes */
        .layout-1 {
            grid-template-columns: 1fr;
            grid-template-rows: 1fr;
        }
        .layout-2 {
            grid-template-columns: 1fr;
            grid-template-rows: 1fr 1fr;
        }
        .layout-4 {
            grid-template-columns: 1fr 1fr;
            grid-template-rows: 1fr 1fr;
        }

        /* Cell styling */
        .print-cell {
            border: 1px dashed #ccc;
            padding: 10mm;
            box-sizing: border-box;
            overflow: hidden;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .print-cell-content {
            width: 100%;
            height: 100%;
        }

        /* Scale content based on size to fit the cut paper */
        .layout-2 .print-cell-content {
            zoom: 0.85;
        }
        .layout-4 .print-cell-content {
            zoom: 0.65;
        }

        /* Print Media Queries */
        @media print {
            nav, .no-print, button {
                display: none !important;
            }
            body, .print-grid {
                width: 100% !important;
                height: 100% !important;
            }
            .print-cell {
                border: 1px dashed #000 !important; /* Keep dashed lines for cutting guide */
            }
            @page {
                size: A4 portrait;
                margin: 0;
            }
        }
    </style>
</head>
<body onload="window.print()">

    @php
        $layout = (int)$perizinan->template->layout_print;
        $class = 'layout-' . $layout;

        $styling = $perizinan->template->rules['styling'] ?? [];
        $fontFamily = $styling['font_family'] ?? "'Times New Roman', Times, serif";
        $lineHeight = $styling['line_height'] ?? '1.4';
        $borderFrame = ($styling['border_frame'] ?? '0') === '1';
    @endphp

    <div class="print-grid {{ $class }}">
        @for($i = 0; $i < $layout; $i++)
            <div class="print-cell">
                <div class="print-cell-content" style="font-family: {!! $fontFamily !!}; line-height: {{ $lineHeight }}; @if($borderFrame) border: 10px double #000; padding: 15px; box-sizing: border-box; @endif">
                    {!! $content !!}
                </div>
            </div>
        @endfor
    </div>

</body>
</html>
