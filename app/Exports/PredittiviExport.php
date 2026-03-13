<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PredittiviExport implements WithMultipleSheets
{
    protected $previsioniDomanda;
    protected $forecastCarburante;
    protected $analisiStagionalita;

    public function __construct($previsioniDomanda, $forecastCarburante, $analisiStagionalita)
    {
        $this->previsioniDomanda = $previsioniDomanda;
        $this->forecastCarburante = $forecastCarburante;
        $this->analisiStagionalita = $analisiStagionalita;
    }

    public function sheets(): array
    {
        return [
            new PrevisioniDomandaSheet($this->previsioniDomanda),
            new ForecastCarburanteSheet($this->forecastCarburante),
            new StagionalitaSheet($this->analisiStagionalita),
        ];
    }
}

// ========== FOGLIO PREVISIONI DOMANDA ==========
class PrevisioniDomandaSheet implements FromArray, WithTitle, WithStyles
{
    protected $dati;

    public function __construct($dati)
    {
        $this->dati = $dati;
    }

    public function array(): array
    {
        $rows = [];
        $rows[] = ['PREVISIONI DOMANDA - PROSSIMI 3 MESI'];
        $rows[] = ['Generato il: ' . \Carbon\Carbon::now()->format('d/m/Y H:i')];
        $rows[] = [''];

        // Trend
        $rows[] = ['TREND IDENTIFICATO'];
        $rows[] = ['Trend Ordini', number_format($this->dati['trend_ordini'], 1) . ' ordini/mese'];
        $rows[] = ['Trend Fatturato', '€ ' . number_format($this->dati['trend_fatturato'], 0, ',', '.') . '/mese'];
        $rows[] = [''];

        // Dati storici
        $rows[] = ['DATI STORICI'];
        $rows[] = ['Mese', 'Anno', 'N° Ordini', 'Fatturato (€)'];
        foreach ($this->dati['dati_storici'] as $dato) {
            $rows[] = [
                $dato->mese,
                $dato->anno,
                $dato->numero_ordini,
                number_format($dato->fatturato, 2, ',', '.'),
            ];
        }
        $rows[] = [''];

        // Previsioni
        $rows[] = ['PREVISIONI'];
        $rows[] = ['Mese', 'Anno', 'Ordini Previsti', 'Fatturato Previsto (€)'];
        foreach ($this->dati['previsioni'] as $prev) {
            $rows[] = [
                $prev['mese'],
                $prev['anno'],
                $prev['ordini_previsti'],
                number_format($prev['fatturato_previsto'], 2, ',', '.'),
            ];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Previsioni Domanda';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(20);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true, 'size' => 12]],
            8 => ['font' => ['bold' => true, 'size' => 12]],
            9 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0D6EFD']
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// ========== FOGLIO FORECAST CARBURANTE ==========
class ForecastCarburanteSheet implements FromArray, WithTitle, WithStyles
{
    protected $dati;

    public function __construct($dati)
    {
        $this->dati = $dati;
    }

    public function array(): array
    {
        return [
            ['FORECAST COSTI CARBURANTE'],
            [''],
            ['Parametro', 'Valore'],
            ['Km Medi Mensili', number_format($this->dati['km_medi_mensili'], 0, ',', '.')],
            ['Prezzo Diesel Attuale', '€ ' . $this->dati['prezzo_diesel_attuale'] . '/L'],
            ['Consumo Medio', $this->dati['consumo_medio'] . ' L/100km'],
            ['Costo Mensile Attuale', '€ ' . number_format($this->dati['costo_mensile_attuale'], 2, ',', '.')],
            [''],
            ['SCENARI PREVISIONALI'],
            ['Scenario', 'Costo Mensile Previsto (€)'],
            ['Ottimistico (-5%)', '€ ' . number_format($this->dati['scenari']['ottimistico'], 2, ',', '.')],
            ['Realistico (+5%)', '€ ' . number_format($this->dati['scenari']['realistico'], 2, ',', '.')],
            ['Pessimistico (+15%)', '€ ' . number_format($this->dati['scenari']['pessimistico'], 2, ',', '.')],
        ];
    }

    public function title(): string
    {
        return 'Forecast Carburante';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(30);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            3 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFC107']
            ]],
            9 => ['font' => ['bold' => true, 'size' => 12]],
            10 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFC107']
            ]],
        ];
    }
}

// ========== FOGLIO STAGIONALITA ==========
class StagionalitaSheet implements FromArray, WithTitle, WithHeadings, WithStyles
{
    protected $dati;

    public function __construct($dati)
    {
        $this->dati = $dati;
    }

    public function headings(): array
    {
        return ['Mese', 'Fatturato Medio (€)', 'Ordini Totali'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->dati as $item) {
            $rows[] = [
                $item->mese_nome ?? 'Mese ' . $item->mese,
                number_format($item->fatturato_medio, 2, ',', '.'),
                $item->ordini_totali,
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Stagionalita';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(22);
        $sheet->getColumnDimension('C')->setWidth(15);

        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0DCAF0']
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}