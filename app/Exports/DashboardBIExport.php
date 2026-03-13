<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DashboardBIExport implements WithMultipleSheets
{
    protected $kpiPrincipali;
    protected $performanceClienti;
    protected $performanceMezzi;
    protected $trendMensili;
    protected $redditivitaRotte;
    protected $dataInizio;
    protected $dataFine;

    public function __construct($kpiPrincipali, $performanceClienti, $performanceMezzi, $trendMensili, $redditivitaRotte, $dataInizio, $dataFine)
    {
        $this->kpiPrincipali = $kpiPrincipali;
        $this->performanceClienti = $performanceClienti;
        $this->performanceMezzi = $performanceMezzi;
        $this->trendMensili = $trendMensili;
        $this->redditivitaRotte = $redditivitaRotte;
        $this->dataInizio = $dataInizio;
        $this->dataFine = $dataFine;
    }

    public function sheets(): array
    {
        return [
            new KPISheet($this->kpiPrincipali, $this->dataInizio, $this->dataFine),
            new ClientiSheet($this->performanceClienti),
            new MezziSheet($this->performanceMezzi),
            new TrendSheet($this->trendMensili),
            new RotteSheet($this->redditivitaRotte),
        ];
    }
}

// ========== FOGLIO KPI ==========
class KPISheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $kpi;
    protected $dataInizio;
    protected $dataFine;

    public function __construct($kpi, $dataInizio, $dataFine)
    {
        $this->kpi = $kpi;
        $this->dataInizio = $dataInizio;
        $this->dataFine = $dataFine;
    }

    public function array(): array
    {
        return [
            ['DASHBOARD BI - KPI PRINCIPALI'],
            ['Periodo:', \Carbon\Carbon::parse($this->dataInizio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($this->dataFine)->format('d/m/Y')],
            [''],
            ['Indicatore', 'Valore'],
            ['Fatturato Totale', '€ ' . number_format($this->kpi['fatturato'], 2, ',', '.')],
            ['Margine Operativo', '€ ' . number_format($this->kpi['margine_operativo'], 2, ',', '.')],
            ['Margine %', number_format($this->kpi['margine_percentuale'], 1, ',', '.') . '%'],
            ['Ordini Completati', $this->kpi['numero_ordini']],
            ['Tasso Completamento', number_format($this->kpi['tasso_completamento'], 1, ',', '.') . '%'],
            ['Ricavo Medio Ordine', '€ ' . number_format($this->kpi['ricavo_medio_ordine'], 2, ',', '.')],
            ['Ricavo per Km', '€ ' . number_format($this->kpi['ricavo_per_km'], 2, ',', '.')],
            ['Km Totali Percorsi', number_format($this->kpi['km_totali'], 0, ',', '.')],
            ['Costi Operativi', '€ ' . number_format($this->kpi['costi_operativi'], 2, ',', '.')],
            ['Crescita Fatturato vs Periodo Prec.', number_format($this->kpi['crescita_fatturato'], 1, ',', '.') . '%'],
        ];
    }

    public function title(): string
    {
        return 'KPI Principali';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(40);
        $sheet->getColumnDimension('B')->setWidth(25);

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            4 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// ========== FOGLIO CLIENTI ==========
class ClientiSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $clienti;

    public function __construct($clienti)
    {
        $this->clienti = $clienti;
    }

    public function headings(): array
    {
        return ['Cliente', 'N° Ordini', 'Fatturato (€)', 'Ricavo Medio (€)', 'Km Totali', '€/Km'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->clienti as $cliente) {
            $rows[] = [
                $cliente->nome_cliente,
                $cliente->numero_ordini,
                number_format($cliente->fatturato, 2, ',', '.'),
                number_format($cliente->ricavo_medio, 2, ',', '.'),
                number_format($cliente->km_totali, 0, ',', '.'),
                number_format($cliente->ricavo_per_km, 2, ',', '.'),
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Performance Clienti';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(10);

        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754']
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// ========== FOGLIO MEZZI ==========
class MezziSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $mezzi;

    public function __construct($mezzi)
    {
        $this->mezzi = $mezzi;
    }

    public function headings(): array
    {
        return ['Targa', 'Mezzo', 'N° Ordini', 'Fatturato (€)', 'Km Totali', 'Costi (€)', 'Margine (€)', 'Margine %'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->mezzi as $mezzo) {
            $rows[] = [
                $mezzo->targa,
                $mezzo->nome_mezzo,
                $mezzo->numero_ordini,
                number_format($mezzo->fatturato, 2, ',', '.'),
                number_format($mezzo->km_totali, 0, ',', '.'),
                number_format($mezzo->costi_totali, 2, ',', '.'),
                number_format($mezzo->margine, 2, ',', '.'),
                number_format($mezzo->margine_percentuale, 1, ',', '.') . '%',
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Performance Mezzi';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(12);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(15);
        $sheet->getColumnDimension('H')->setWidth(12);

        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFC107']
            ], 'font' => ['bold' => true]],
        ];
    }
}

// ========== FOGLIO TREND ==========
class TrendSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $trend;

    public function __construct($trend)
    {
        $this->trend = $trend;
    }

    public function headings(): array
    {
        return ['Periodo', 'N° Ordini', 'Fatturato (€)', 'Km Totali', 'Ricavo Medio (€)'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->trend as $item) {
            $rows[] = [
                $item->periodo ?? ($item->mese_nome . ' ' . $item->anno),
                $item->numero_ordini,
                number_format($item->fatturato, 2, ',', '.'),
                number_format($item->km_totali, 0, ',', '.'),
                number_format($item->ricavo_medio, 2, ',', '.'),
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Trend Mensili';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(15);

        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '0DCAF0']
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}

// ========== FOGLIO ROTTE ==========
class RotteSheet implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithStyles
{
    protected $rotte;

    public function __construct($rotte)
    {
        $this->rotte = $rotte;
    }

    public function headings(): array
    {
        return ['Rotta', 'N° Ordini', 'Fatturato (€)', 'Km Totali', '€/Km', 'Margine (€)', 'Margine %'];
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->rotte as $rotta) {
            $rows[] = [
                $rotta['rotta'],
                $rotta['numero_ordini'],
                number_format($rotta['fatturato'], 2, ',', '.'),
                number_format($rotta['km_totali'], 0, ',', '.'),
                number_format($rotta['ricavo_per_km'], 2, ',', '.'),
                number_format($rotta['margine'], 2, ',', '.'),
                number_format($rotta['margine_percentuale'], 1, ',', '.') . '%',
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Redditivita Rotte';
    }

    public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
    {
        $sheet->getColumnDimension('A')->setWidth(30);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(10);
        $sheet->getColumnDimension('F')->setWidth(15);
        $sheet->getColumnDimension('G')->setWidth(12);

        return [
            1 => ['font' => ['bold' => true], 'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '6C757D']
            ], 'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }
}