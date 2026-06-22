<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Template Kosong - {{ $template->nama }}</title>
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
    <!-- Google Fonts for Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if($template->source_type === 'upload_pdf')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    @endif
</head>
<body @if($template->source_type !== 'upload_pdf') onload="window.print()" @endif>

    @php
        $layout = (int)$template->layout_print;
        $class = 'layout-' . $layout;

        $styling = $template->rules['styling'] ?? [];
        $fontFamily = $styling['font_family'] ?? "'Times New Roman', Times, serif";
        $lineHeight = $styling['line_height'] ?? '1.4';
        $borderFrame = ($styling['border_frame'] ?? '0') === '1';
    @endphp

    <div class="print-grid {{ $class }}">
        @for($i = 0; $i < $layout; $i++)
            <div class="print-cell">
                <div class="print-cell-content" @if($template->source_type !== 'upload_pdf') style="font-family: {!! $fontFamily !!}; line-height: {{ $lineHeight }}; @if($borderFrame) border: 10px double #000; padding: 15px; box-sizing: border-box; @endif" @endif>
                    @if($template->source_type === 'upload_pdf')
                        <canvas class="pdf-canvas" style="width: 100%; height: 100%; object-fit: contain;"></canvas>
                    @else
                        {!! $content !!}
                    @endif
                </div>
            </div>
        @endfor
    </div>

    @if($template->source_type === 'upload_pdf')
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        const url = "{{ asset('storage/'.$template->file_pdf) }}";
        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {
                const canvases = document.querySelectorAll('.pdf-canvas');
                let renderedCount = 0;

                canvases.forEach(canvas => {
                    const ctx = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.5 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise.then(function() {
                        renderedCount++;
                        if (renderedCount === canvases.length) {
                            // Automatically trigger browser print once all canvas copies are rendered
                            setTimeout(() => {
                                window.print();
                            }, 500);
                        }
                    });
                });
            });
        }).catch(err => {
            console.error('Error loading PDF for printing: ', err);
        });
    </script>
    @endif

</body>
</html>
