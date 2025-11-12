<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Munkalap - {{ $product->serial_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 11px;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #f0f0f0;
            padding: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #333;
        }

        .info-row {
            display: flex;
            padding: 5px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-label {
            font-weight: bold;
            width: 35%;
            padding-right: 10px;
        }

        .info-value {
            width: 65%;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            border-top: 1px solid #333;
            padding-top: 10px;
            text-align: center;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #333;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .comment-box {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            margin-top: 5px;
            min-height: 80px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>MUNKALAP</h1>
        <p>{{ $productLog->what === 'commissioning' ? 'Beüzemelés' : ($productLog->what === 'maintenance' ? 'Karbantartás' : 'Javítás') }}</p>
    </div>

    <!-- Megrendelő/Tulajdonos adatok -->
    <div class="section">
        <div class="section-title">Megrendelő / Tulajdonos adatok</div>
        <table>
            <tr class="info-row">
                <td class="info-label">Név:</td>
                <td class="info-value">{{ $owner->name ?? $product->owner_name ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">E-mail cím:</td>
                <td class="info-value">{{ $owner->email ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Telefon:</td>
                <td class="info-value">{{ $owner->phone ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Telepítési hely -->
    <div class="section">
        <div class="section-title">Telepítési hely</div>
        <table>
            <tr class="info-row">
                <td class="info-label">Irányítószám:</td>
                <td class="info-value">{{ $product->zip ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Település:</td>
                <td class="info-value">{{ $product->city ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Utca, házszám / Hrsz:</td>
                <td class="info-value">{{ $product->street ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Termék adatok -->
    <div class="section">
        <div class="section-title">Termék adatok</div>
        <table>
            <tr class="info-row">
                <td class="info-label">Termék megnevezése:</td>
                <td class="info-value">{{ $product->tool->name ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Gyártó:</td>
                <td class="info-value">{{ $product->tool->factory_name ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Termék gyári száma:</td>
                <td class="info-value">{{ $product->serial_number }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Értékesítés dátuma:</td>
                <td class="info-value">{{ $product->purchase_date?->format('Y. m. d.') ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Beüzemelés dátuma:</td>
                <td class="info-value">{{ $product->installation_date?->format('Y. m. d.') ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Garancia érvényessége:</td>
                <td class="info-value">{{ $product->warrantee_date?->format('Y. m. d.') ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Elvégzett munka -->
    <div class="section">
        <div class="section-title">Elvégzett munka</div>
        <table>
            <tr class="info-row">
                <td class="info-label">Munka típusa:</td>
                <td class="info-value">
                    @if($productLog->what === 'commissioning')
                        Beüzemelés
                    @elseif($productLog->what === 'maintenance')
                        Karbantartás
                    @else
                        Javítás
                    @endif
                </td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Munka módja:</td>
                <td class="info-value">{{ $productLog->is_online ? 'Online' : 'Offline' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Dátum:</td>
                <td class="info-value">{{ $productLog->when->format('Y. m. d. H:i') }}</td>
            </tr>
        </table>
    </div>

    <!-- Megjegyzés -->
    <div class="section">
        <div class="section-title">Megjegyzés (elvégzett munka, felhasznált alkatrészek)</div>
        <div class="comment-box">
            {{ $productLog->comment ?? 'Nincs megjegyzés' }}
        </div>
    </div>

    <!-- Szervizpartner -->
    <div class="section">
        <div class="section-title">Szervizpartner adatok</div>
        <table>
            <tr class="info-row">
                <td class="info-label">Szervizpartner neve:</td>
                <td class="info-value">{{ $servicer->name ?? 'N/A' }}</td>
            </tr>
            <tr class="info-row">
                <td class="info-label">Szervezet:</td>
                <td class="info-value">{{ $servicer->organization->name ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <!-- Aláírások -->
    <div class="signature-section">
        <div class="signature-box">
            <div style="height: 60px;"></div>
            <div>Megrendelő / Meghatalmazottja aláírása</div>
        </div>
        <div class="signature-box">
            @if($productLog->signature)
                <div style="height: 60px; display: flex; align-items: center; justify-content: center;">
                    <img src="{{ $productLog->signature }}" alt="Signature" style="max-height: 50px; max-width: 200px;">
                </div>
            @else
                <div style="height: 60px;"></div>
            @endif
            <div>Szervizpartner aláírása</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Dokumentum generálva: {{ now()->format('Y. m. d. H:i:s') }}</p>
    </div>
</body>
</html>
