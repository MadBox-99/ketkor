<!DOCTYPE html>
<html lang="hu">

    <head>
        <meta charset="UTF-8">
        <title>Munkalap - {{ $product->serial_number }}</title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 10px;
                line-height: 1.3;
                color: #000;
                padding: 15px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
            }

            td,
            th {
                border: 1px solid #000;
                padding: 4px;
                vertical-align: top;
            }

            .header-table td {
                border: 2px solid #000;
                font-weight: bold;
            }

            .no-border {
                border: none !important;
            }

            .checkbox {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 2px solid #000;
                margin-right: 3px;
                vertical-align: middle;
            }

            .checkbox.checked {
                background-color: #000;
            }

            .text-center {
                text-align: center;
            }

            .text-right {
                text-align: right;
            }

            .bold {
                font-weight: bold;
            }

            .items-table th {
                background-color: #f0f0f0;
                font-weight: bold;
                text-align: center;
                padding: 6px 4px;
            }

            .signature-line {
                border-top: 1px solid #000;
                margin-top: 40px;
                padding-top: 5px;
                text-align: center;
            }

            .work-description {
                min-height: 100px;
                padding: 5px;
            }
        </style>
    </head>

    <body>
        <!-- Fejléc -->
        <table class="header-table" style="margin-bottom: 10px;">
            <tr>
                <td style="width: 30%; text-align: center; vertical-align: middle;">
                    @php
                        $logoPath = resource_path('img/ketkor_logo.webp');
                        $logoData = base64_encode(file_get_contents($logoPath));
                        $logoSrc = 'data:image/webp;base64,' . $logoData;
                    @endphp
                    <img src="{{ $logoSrc }}" alt="Két Kör Kft." style="max-height: 50px; max-width: 100%;">
                    <div style="font-size: 14px; font-weight: bold; margin-top: 5px;">MUNKALAP</div>
                </td>
                <td style="width: 70%; font-size: 9px; text-align: right; vertical-align: middle;">
                    {{ $servicer->organization->name ?? 'Két Kör Kft.' }}<br>
                    Tel: {{ $servicer->organization->phone ?? '+36 23 530 570' }}<br>
                    Web: {{ $servicer->organization->website ?? 'www.ketkorkft.hu' }}<br>
                    Email: {{ $servicer->organization->email ?? 'webaruhaz@ketkorkft.hu' }}
                </td>
            </tr>
        </table>

        <!-- Munka típusa -->
        <table style="margin-bottom: 5px;">
            <tr>
                <td style="width: 25%;">
                    <span class="checkbox {{ $productLog->what === 'commissioning' ? 'checked' : '' }}"></span>
                    ÜZEMBEHELYEZÉS
                </td>
                <td style="width: 25%;">
                    <span
                        class="checkbox {{ $productLog->what === 'maintenance' && $product->warrantee_date && $product->warrantee_date->isFuture() ? 'checked' : '' }}"></span>
                    GARANCIÁLIS
                </td>
                <td style="width: 25%;">
                    <span class="checkbox {{ $productLog->what === 'maintenance' ? 'checked' : '' }}"></span>
                    KARBANTARTÁS
                </td>
                <td style="width: 25%;">
                    <span class="checkbox {{ $productLog->what === 'installation' ? 'checked' : '' }}"></span> JAVÍTÁS
                </td>
            </tr>
        </table>

        <!-- Ügyfél adatok -->
        <table style="margin-bottom: 5px;">
            <tr>
                <td style="width: 15%; font-weight: bold;">ÜGYFÉL NEVE:</td>
                <td style="width: 85%;">{{ $owner->name ?? ($product->owner_name ?? '') }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">CÍME:</td>
                <td>{{ $product->zip ?? '' }} {{ $product->city ?? '' }}, {{ $product->street ?? '' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">TÍPUS:</td>
                <td>{{ $product->tool->name ?? '' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">TELEFON:</td>
                <td style="width: 35%;">{{ $owner->phone ?? '' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">EMAIL:</td>
                <td>{{ $owner->email ?? '' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">GYÁRI SZÁMA:</td>
                <td>{{ $product->serial_number }}</td>
            </tr>
        </table>

        <!-- Alkatrészlista táblázat -->
        <table class="items-table" style="margin-bottom: 5px;">
            <tr>
                <th style="width: 35%;">ANYAG MEGNEVEZÉS</th>
                <th style="width: 25%;">RAKTÁRI SZÁM</th>
                <th style="width: 10%;">DB</th>
                <th style="width: 15%;">EGYSÉGÁR</th>
                <th style="width: 15%;">ÖSSZEG</th>
            </tr>
            @for ($i = 0; $i < 8; $i++)
                <tr>
                    <td style="height: 20px;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </table>

        <!-- Elvégzett munka és költségek -->
        <table style="margin-bottom: 5px;">
            <tr>
                <td style="width: 70%; font-weight: bold; vertical-align: top;">
                    ELVÉGZETT MUNKA LEÍRÁSA:
                    <div class="work-description">
                        {{ $productLog->comment ?? '' }}
                    </div>
                </td>
                <td style="width: 30%; vertical-align: top;">
                    <table style="width: 100%; border: none;">
                        <tr>
                            <td class="no-border" style="font-weight: bold; text-align: right; padding: 2px;">ÖSSZESEN
                            </td>
                        </tr>
                        <tr>
                            <td class="no-border" style="padding: 15px 2px;">
                                <div style="border: 1px solid #000; padding: 3px; margin-bottom: 3px;">
                                    <span class="checkbox"></span> A beépített alkatrészre hónap garanciát vállalunk
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="no-border" style="border-top: 1px solid #000; font-weight: bold; padding: 2px;">
                                ANYAG</td>
                        </tr>
                        <tr>
                            <td class="no-border" style="border-top: 1px solid #000; font-weight: bold; padding: 2px;">
                                MUNKADÍJ</td>
                        </tr>
                        <tr>
                            <td class="no-border" style="border-top: 1px solid #000; font-weight: bold; padding: 2px;">
                                KISZÁLLÁS</td>
                        </tr>
                        <tr>
                            <td class="no-border" style="border-top: 1px solid #000; font-weight: bold; padding: 2px;">
                                ÖSSZESEN</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Dátum és igazolás -->
        <table style="margin-bottom: 10px;">
            <tr>
                <td style="width: 60%;">
                    <strong>A MUNKA ELVÉGZÉSÉNEK IDEJE:</strong> {{ $productLog->when->format('Y. m. d. H:i') }}
                </td>
                <td style="width: 40%; border-left: none;" rowspan="2">
                    <img src="{{ $productLog->signature }}" alt="Signature"
                        style="max-height: 50px; max-width: 180px;">
                </td>
            </tr>
            <tr>
                <td style="font-size: 8px; padding: 5px; border-right: none;">
                    <strong>A MUNKA ELVÉGZÉSÉT IGAZOLOM:</strong><br>
                    Igazolom, hogy a szerelő {{ $productLog->is_online ? 'online' : 'offline' }} módon elvégezte a
                    munkát {{ $productLog->when->format('Y. m. d.') }} napján.
                </td>

            </tr>
        </table>

    </body>

</html>
