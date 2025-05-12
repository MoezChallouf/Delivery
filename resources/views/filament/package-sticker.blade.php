<!-- resources/views/filament/package-sticker.blade.php -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Étiquette d'Impression</title>
    <style>
        @page {
            size: 80mm 50mm;
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0;
            padding: 0;
            width: 80mm;
            height: 50mm;
            font-family: 'Arial Narrow', sans-serif;
        }

        .label {
            width: 80mm;
            height: 50mm;
            padding: 2mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .header {
            display: flex;
            justify-content: space-between;
        }

        .details strong {
            display: inline-block;
            width: 22mm;
            font-weight: 600;
        }

        .qr-container {
            text-align: right;
        }

        .barcode {
            width: 50%;
            height: 8mm;
            margin-top: 1mm;
        }

        .footer-note {
            font-size: 7pt;
            margin-top: 1mm;
        }

        @media print {
            body {
                visibility: hidden;
            }
            .label {
                visibility: visible;
                position: fixed;
                top: 0;
                left: 0;
            }
            .qr-container{
                visibility: visible;
            }
        }
    </style>
    
</head>
<body>
    <div class="label">
        <div class="header">
            <div class="details" style="font-size: 8pt">
                <strong>Qualité :</strong> {{ $package->product->quality }}<br>
                <strong>Référence:</strong> {{ $package->product->code_article }}<br>
                <strong>Couleur :</strong> {{ $package->product->color }}<br>
                <strong>Taille :</strong> {{ $package->product->size }}<br>
                <strong>Quantité :</strong> {{ $package->quantity }}<br>
            </div>
            <div class="qr-container">
                <div style="font-size: 7pt; margin-bottom: 1mm">
                    {{ $package->created_at->format('d.m.Y') }}
                </div>
                {!! $qrCode !!}
            </div>
        </div>

        <div class="footer">
            <img src="https://barcode.tec-it.com/barcode.ashx?data={{ $package->product->ean ?? '6191739805470' }}&code=EAN13&unit=mm&height=10" 
                 alt="Code-barres" 
                 class="barcode">
                 <div class="footer-note">
                    &copy; {{ date('Y') }} Ste Tunisienne Industrielle des Tapis — Sidi El Hani, Zone Industrielle 4025
                </div>
        </div>
    </div>
</body>
</html>