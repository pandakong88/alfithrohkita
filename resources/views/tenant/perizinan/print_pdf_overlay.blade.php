<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Resmi - {{ $perizinan->santri->nama_lengkap }}</title>
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
            position: relative;
        }

        /* Scale content based on size to fit the cut paper */
        .layout-2 .print-cell-content {
            zoom: 0.85;
        }
        .layout-4 .print-cell-content {
            zoom: 0.65;
        }

        .pdf-overlaid-item {
            position: absolute;
            font-weight: bold;
            font-size: 13px;
            color: #000000;
            white-space: nowrap;
            font-family: Arial, Helvetica, sans-serif;
            z-index: 10;
        }

        .qr-code-overlaid {
            width: 65px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-code-overlaid svg {
            width: 100% !important;
            height: 100% !important;
            display: block;
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
</head>
<body>

    @php
        $layout = (int)$perizinan->template->layout_print;
        $class = 'layout-' . $layout;
        $requiredVars = $perizinan->template->required_variables ?? [];
    @endphp

    <div class="print-grid {{ $class }}">
        @for($i = 0; $i < $layout; $i++)
            <div class="print-cell">
                <div class="print-cell-content">
                    <div class="pdf-container" style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; position: relative;">
                        <div class="pdf-wrapper" style="position: relative; display: block; box-shadow: none; margin: 0; padding: 0;">
                            <canvas class="pdf-canvas" style="display: block; width: 100%; height: 100%;"></canvas>
                            <div class="pdf-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                                @foreach($requiredVars as $key => $coords)
                                    @if(isset($coords['x']) && isset($coords['y']))
                                        @php
                                            $value = $placeholders[$key] ?? '';
                                        @endphp
                                        @if($key === 'qr_code')
                                            <div class="pdf-overlaid-item qr-code-overlaid" style="left: {{ $coords['x'] }}%; top: {{ $coords['y'] }}%;">
                                                {!! $value !!}
                                            </div>
                                        @elseif($key === 'kode_surat')
                                            <div class="pdf-overlaid-item" style="left: {{ $coords['x'] }}%; top: {{ $coords['y'] }}%; font-family: monospace; font-size: 11px;">
                                                {{ $value }}
                                            </div>
                                        @else
                                            <div class="pdf-overlaid-item" style="left: {{ $coords['x'] }}%; top: {{ $coords['y'] }}%;">
                                                {{ $value }}
                                            </div>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';

        const url = "{{ asset('storage/'.$perizinan->template->file_pdf) }}";
        pdfjsLib.getDocument(url).promise.then(function(pdf) {
            pdf.getPage(1).then(function(page) {
                const canvases = document.querySelectorAll('.pdf-canvas');
                let renderedCount = 0;

                canvases.forEach(canvas => {
                    const ctx = canvas.getContext('2d');
                    const viewport = page.getViewport({ scale: 1.5 });
                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    const container = canvas.closest('.pdf-container');
                    const wrapper = canvas.closest('.pdf-wrapper');
                    const containerWidth = container.clientWidth;
                    const containerHeight = container.clientHeight;

                    const pdfRatio = viewport.width / viewport.height;
                    const containerRatio = containerWidth / containerHeight;

                    let targetWidth, targetHeight;
                    if (pdfRatio > containerRatio) {
                        // PDF is wider than the layout cell relative bounds
                        targetWidth = containerWidth;
                        targetHeight = containerWidth / pdfRatio;
                    } else {
                        // PDF is taller than the layout cell relative bounds
                        targetHeight = containerHeight;
                        targetWidth = containerHeight * pdfRatio;
                    }

                    wrapper.style.width = targetWidth + 'px';
                    wrapper.style.height = targetHeight + 'px';

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

</body>
</html>
