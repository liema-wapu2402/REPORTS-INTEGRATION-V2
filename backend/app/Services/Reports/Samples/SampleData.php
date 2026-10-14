<?php

namespace App\Services\Reports\Samples;

/**
 * Deterministic demo datasets used by the sample-report catalog.
 *
 * Nothing here touches the database: every method returns the same rows on
 * every call (values are derived from a hash of seed + index), so a sample
 * report looks identical each time it is generated and is easy to test.
 */
final class SampleData
{
    private const BRANCHES = ['Manila Main', 'Quezon City', 'Makati', 'Pasig', 'Cebu'];

    private const FIRST = ['Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Liza', 'Carlo', 'Grace', 'Miguel', 'Rhea', 'Paolo', 'Jasmine'];

    private const LAST = ['Dela Cruz', 'Santos', 'Reyes', 'Garcia', 'Bautista', 'Ramos', 'Mendoza', 'Aquino', 'Villanueva', 'Castro'];

    private const DEPARTMENTS = ['Records', 'IT', 'Finance', 'Operations', 'HR', 'Sales'];

    private static function r(string $seed, int $i, int $min, int $max): int
    {
        return $min + (crc32($seed . ':' . $i) % ($max - $min + 1));
    }

    /** @return array{0: array<int,string>, 1: array<int,array<string,mixed>>} headings + rows keyed by heading */
    public static function table(string $dataset): array
    {
        $rows = match ($dataset) {
            'sales_summary' => self::salesSummary(),
            'transactions' => self::transactions(),
            'employees' => self::employees(),
            'inventory' => self::inventory(),
            'attendance' => self::attendance(),
            'payroll' => self::payroll(),
            'soa' => self::soa(),
            'invoice' => self::invoiceLines(),
            default => [],
        };

        return [$rows ? array_keys($rows[0]) : [], $rows];
    }

    public static function salesSummary(): array
    {
        $rows = [];
        $i = 0;
        foreach (self::BRANCHES as $branch) {
            foreach (['Noodles', 'Rice Meals', 'Beverages', 'Desserts'] as $category) {
                $gross = self::r('gross', $i, 18_000, 96_000);
                $discount = (int) round($gross * self::r('disc', $i, 2, 9) / 100);
                $rows[] = [
                    'branch' => $branch,
                    'category' => $category,
                    'gross_sales' => $gross,
                    'discounts' => $discount,
                    'net_sales' => $gross - $discount,
                ];
                $i++;
            }
        }

        return $rows;
    }

    public static function transactions(int $count = 60): array
    {
        $items = [
            ['Angkong Noodles', 'Noodles', 185], ['Beef Mami', 'Noodles', 210], ['Pancit Canton', 'Noodles', 160],
            ['Chicken Rice Bowl', 'Rice Meals', 175], ['Pork Siomai (6)', 'Rice Meals', 120], ['Iced Tea', 'Beverages', 65],
            ['Calamansi Juice', 'Beverages', 70], ['Halo-Halo', 'Desserts', 120], ['Leche Flan', 'Desserts', 95],
        ];
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            [$name, $category, $price] = $items[self::r('item', $i, 0, count($items) - 1)];
            $qty = self::r('qty', $i, 1, 6);
            $rows[] = [
                'receipt_no' => 'OR-' . str_pad((string) (10_400 + $i), 6, '0', STR_PAD_LEFT),
                'date' => date('Y-m-d', strtotime('2026-09-01 +' . self::r('day', $i, 0, 29) . ' days')),
                'branch' => self::BRANCHES[self::r('br', $i, 0, count(self::BRANCHES) - 1)],
                'category' => $category,
                'item' => $name,
                'qty' => $qty,
                'unit_price' => $price,
                'amount' => $qty * $price,
            ];
        }
        usort($rows, fn ($a, $b) => [$a['date'], $a['receipt_no']] <=> [$b['date'], $b['receipt_no']]);

        return $rows;
    }

    public static function employees(int $count = 24): array
    {
        $rows = [];
        for ($i = 0; $i < $count; $i++) {
            $first = self::FIRST[$i % count(self::FIRST)];
            $last = self::LAST[self::r('ln', $i, 0, count(self::LAST) - 1)];
            $dept = self::DEPARTMENTS[self::r('dept', $i, 0, count(self::DEPARTMENTS) - 1)];
            $rows[] = [
                'employee_no' => 'EMP-' . str_pad((string) (1001 + $i), 4, '0', STR_PAD_LEFT),
                'name' => "$first $last",
                'department' => $dept,
                'position' => ['Associate', 'Specialist', 'Supervisor', 'Manager'][self::r('pos', $i, 0, 3)],
                'email' => strtolower($first . '.' . str_replace(' ', '', $last)) . '@example.com',
                'date_hired' => date('Y-m-d', strtotime('2019-01-15 +' . self::r('hire', $i, 0, 2000) . ' days')),
                'status' => self::r('st', $i, 0, 9) > 0 ? 'Active' : 'On Leave',
            ];
        }

        return $rows;
    }

    public static function inventory(): array
    {
        $names = [
            'Egg Noodles (kg)', 'Rice (sack)', 'Pork Belly (kg)', 'Chicken Breast (kg)', 'Soy Sauce (L)', 'Cooking Oil (L)',
            'Garlic (kg)', 'Onion (kg)', 'Calamansi (kg)', 'Sugar (kg)', 'Tea Leaves (pack)', 'Disposable Bowls (100)',
            'Paper Cups (100)', 'Napkins (box)', 'LPG Tank', 'Siomai Wrapper (pack)',
        ];
        $rows = [];
        foreach ($names as $i => $name) {
            $onHand = self::r('oh', $i, 2, 120);
            $reorder = self::r('ro', $i, 10, 40);
            $cost = self::r('cost', $i, 35, 2_400);
            $rows[] = [
                'sku' => 'SKU-' . str_pad((string) (200 + $i), 4, '0', STR_PAD_LEFT),
                'item' => $name,
                'on_hand' => $onHand,
                'reorder_level' => $reorder,
                'unit_cost' => $cost,
                'stock_value' => $onHand * $cost,
                'status' => $onHand <= $reorder ? 'Reorder' : 'OK',
            ];
        }

        return $rows;
    }

    public static function attendance(): array
    {
        $rows = [];
        foreach (self::employees(14) as $i => $emp) {
            $present = self::r('pr', $i, 17, 22);
            $rows[] = [
                'employee_no' => $emp['employee_no'],
                'name' => $emp['name'],
                'department' => $emp['department'],
                'days_present' => $present,
                'days_absent' => 22 - $present,
                'late_minutes' => self::r('late', $i, 0, 95),
                'overtime_hours' => self::r('ot', $i, 0, 14),
            ];
        }

        return $rows;
    }

    public static function payroll(): array
    {
        $rows = [];
        foreach (self::employees(12) as $i => $emp) {
            $basic = self::r('basic', $i, 18, 55) * 1000;
            $allow = self::r('allow', $i, 1, 5) * 500;
            $gross = $basic + $allow;
            $deductions = (int) round($gross * 0.11);
            $rows[] = [
                'employee_no' => $emp['employee_no'],
                'name' => $emp['name'],
                'basic_pay' => $basic,
                'allowances' => $allow,
                'gross_pay' => $gross,
                'deductions' => $deductions,
                'net_pay' => $gross - $deductions,
            ];
        }

        return $rows;
    }

    /** Statement of account lines (charges/payments/running balance). */
    public static function soa(): array
    {
        $lines = [
            ['2026-08-02', 'Outpatient consultation', 1500, 0],
            ['2026-08-02', 'Laboratory - CBC', 650, 0],
            ['2026-08-05', 'Chest X-ray', 1200, 0],
            ['2026-08-09', 'Medication - antibiotics', 980, 0],
            ['2026-08-10', 'HMO payment received', 0, 3000],
            ['2026-08-18', 'Follow-up consultation', 1500, 0],
            ['2026-08-24', 'Physical therapy session', 2200, 0],
            ['2026-08-29', 'HMO payment received', 0, 2500],
        ];
        $balance = 0;
        $rows = [];
        foreach ($lines as [$date, $desc, $charge, $payment]) {
            $balance += $charge - $payment;
            $rows[] = ['date' => $date, 'description' => $desc, 'charges' => $charge, 'payments' => $payment, 'balance' => $balance];
        }

        return $rows;
    }

    public static function invoiceLines(): array
    {
        return [
            ['description' => 'Report engine integration (DomPDF, Snappy, Puppeteer)', 'qty' => 1, 'unit_price' => 45000, 'amount' => 45000],
            ['description' => 'Excel / CSV / DOCX exporters', 'qty' => 1, 'unit_price' => 28000, 'amount' => 28000],
            ['description' => 'Dashboard UI customisation', 'qty' => 3, 'unit_price' => 6500, 'amount' => 19500],
            ['description' => 'Training and handover (hours)', 'qty' => 8, 'unit_price' => 1200, 'amount' => 9600],
        ];
    }

    /** Headline numbers for the executive dashboard PDF. */
    public static function kpis(): array
    {
        $sales = self::salesSummary();
        $gross = array_sum(array_column($sales, 'gross_sales'));
        $net = array_sum(array_column($sales, 'net_sales'));

        $byBranch = [];
        foreach ($sales as $row) {
            $byBranch[$row['branch']] = ($byBranch[$row['branch']] ?? 0) + $row['net_sales'];
        }
        $byCategory = [];
        foreach ($sales as $row) {
            $byCategory[$row['category']] = ($byCategory[$row['category']] ?? 0) + $row['net_sales'];
        }

        $monthly = [];
        foreach (['Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'] as $i => $m) {
            $monthly[$m] = (int) round($net / 5.2 * (0.78 + self::r('mo', $i, 0, 40) / 100));
        }

        return [
            'gross' => $gross,
            'net' => $net,
            'discounts' => $gross - $net,
            'orders' => array_sum(array_column(self::transactions(), 'qty')) * 14,
            'by_branch' => $byBranch,
            'by_category' => $byCategory,
            'monthly' => $monthly,
        ];
    }
}
