<?php

namespace App\Http\Controllers\Admin;

use App\Models\MembershipType;
use App\Http\Controllers\Controller;
use App\Models\Family;
use App\Models\FamilyEmail;
use App\Models\FamilyMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImportController extends Controller
{
    // Aliases for each logical field — matches ShulCloud, generic, and QuickShul native exports
    private array $aliases = [
        'family_name'    => ['account name', 'family name', 'household name', 'name', 'last name'],
        'primary_first'  => ['primary first name', 'head first name', 'first name', 'primary first'],
        'primary_last'   => ['primary last name', 'head last name', 'primary last'],
        'primary_email'  => ['primary email', 'head email', 'email'],
        'primary_phone'  => ['primary cell phone', 'primary home phone', 'cell phone', 'phone', 'home phone', 'primary phone'],
        'primary_hebrew' => ['primary hebrew name', 'hebrew name', 'primary hebrew'],
        'primary_gender' => ['primary gender', 'gender'],
        'primary_dob'    => ['primary birthday', 'primary date of birth', 'birthday', 'date of birth', 'dob'],
        'sec_first'      => ['secondary first name', 'spouse first name', 'co-head first name', 'secondary first'],
        'sec_last'       => ['secondary last name', 'spouse last name', 'secondary last'],
        'sec_email'      => ['secondary email', 'spouse email'],
        'sec_phone'      => ['secondary cell phone', 'spouse cell phone', 'spouse phone', 'secondary phone'],
        'sec_hebrew'     => ['secondary hebrew name', 'spouse hebrew name', 'secondary hebrew'],
        'sec_gender'     => ['secondary gender', 'spouse gender'],
        'sec_dob'        => ['secondary birthday', 'spouse birthday', 'secondary date of birth'],
        'address'        => ['street address', 'address', 'street'],
        'city'           => ['city'],
        'state'          => ['state'],
        'zip'            => ['zip code', 'zip', 'postal code'],
        'membership_type'=> ['account type', 'membership type', 'type', 'member type'],
        'member_since'   => ['date joined', 'member since', 'join date'],
        'notes'          => ['notes', 'comments', 'memo'],
    ];

    public function show()
    {
        return view('admin.members.import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $path = $request->file('csv_file')->store('imports', 'local');

        $fullPath = storage_path('app/' . $path);
        [$headers, $rows] = $this->parseCsv($fullPath);

        if (empty($headers)) {
            return back()->withErrors(['csv_file' => 'Could not read CSV file. Ensure it is a valid CSV with a header row.']);
        }

        $columnMap = $this->detectColumns($headers);
        $preview   = array_slice($rows, 0, 8);
        $mapped    = array_map(fn($row) => $this->mapRow($row, $headers, $columnMap), $preview);

        // Count existing families to flag duplicates
        $existingNames = Family::pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->flip()
            ->all();

        foreach ($mapped as &$m) {
            $m['_duplicate'] = isset($existingNames[strtolower(trim($m['family_name'] ?? ''))]);
        }
        unset($m);

        $totalRows = count($rows);
        $session   = [
            'import_path'  => $path,
            'import_count' => $totalRows,
        ];
        $request->session()->put($session);

        return view('admin.members.import', compact('headers', 'columnMap', 'mapped', 'totalRows'));
    }

    public function process(Request $request)
    {
        $path = $request->session()->get('import_path');
        if (!$path) {
            return redirect()->route('admin.members.import')->withErrors(['csv_file' => 'Session expired. Please upload again.']);
        }

        $fullPath = storage_path('app/' . $path);
        [$headers, $rows] = $this->parseCsv($fullPath);
        $columnMap = $this->detectColumns($headers);

        $skipDuplicates = $request->boolean('skip_duplicates', true);

        $existingNames = Family::pluck('name')
            ->map(fn($n) => strtolower(trim($n)))
            ->flip()
            ->all();

        $created  = 0;
        $skipped  = 0;
        $errors   = [];
        $tenantId = app('tenant')->id;

        foreach ($rows as $i => $row) {
            $data = $this->mapRow($row, $headers, $columnMap);

            $familyName = trim($data['family_name'] ?? '');
            if (!$familyName) {
                $skipped++;
                continue;
            }

            if ($skipDuplicates && isset($existingNames[strtolower($familyName)])) {
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($data, $familyName, $tenantId) {
                    $family = Family::create([
                        'tenant_id'       => $tenantId,
                        'name'            => $familyName,
                        'address'         => $data['address'] ?: null,
                        'city'            => $data['city'] ?: null,
                        'state'           => $data['state'] ?: null,
                        'zip'             => $data['zip'] ?: null,
                        'phone'           => $data['primary_phone'] ?: null,
                        'membership_type' => $this->resolveMembershipType($data['membership_type'] ?? ''),
                        'member_since'    => $this->resolveDate($data['member_since'] ?? ''),
                        'notes'           => $data['notes'] ?: null,
                    ]);

                    // Emails
                    $emailsAdded = [];
                    foreach (['primary_email', 'sec_email'] as $i => $key) {
                        $email = filter_var(trim($data[$key] ?? ''), FILTER_VALIDATE_EMAIL);
                        if ($email && !in_array($email, $emailsAdded)) {
                            FamilyEmail::create([
                                'tenant_id'  => $tenantId,
                                'family_id'  => $family->id,
                                'email'      => $email,
                                'label'      => $i === 0 ? 'Primary' : 'Spouse',
                                'is_primary' => $i === 0,
                            ]);
                            $emailsAdded[] = $email;
                        }
                    }

                    // Primary adult
                    $primaryFirst = trim($data['primary_first'] ?? '');
                    $primaryLast  = trim($data['primary_last'] ?? $familyName);
                    if ($primaryFirst) {
                        FamilyMember::create([
                            'tenant_id'   => $tenantId,
                            'family_id'   => $family->id,
                            'first_name'  => $primaryFirst,
                            'last_name'   => $primaryLast,
                            'hebrew_name' => $data['primary_hebrew'] ?: null,
                            'gender'      => $this->resolveGender($data['primary_gender'] ?? ''),
                            'role'        => 'head',
                            'date_of_birth' => $this->resolveDate($data['primary_dob'] ?? ''),
                        ]);
                    }

                    // Secondary adult (spouse)
                    $secFirst = trim($data['sec_first'] ?? '');
                    if ($secFirst) {
                        FamilyMember::create([
                            'tenant_id'   => $tenantId,
                            'family_id'   => $family->id,
                            'first_name'  => $secFirst,
                            'last_name'   => trim($data['sec_last'] ?? $primaryLast),
                            'hebrew_name' => $data['sec_hebrew'] ?: null,
                            'gender'      => $this->resolveGender($data['sec_gender'] ?? ''),
                            'role'        => 'spouse',
                            'date_of_birth' => $this->resolveDate($data['sec_dob'] ?? ''),
                        ]);
                    }
                });

                $created++;
            } catch (\Throwable $e) {
                $errors[] = "Row " . ($i + 2) . " ({$familyName}): " . $e->getMessage();
            }
        }

        // Cleanup
        \Storage::disk('local')->delete($path);
        $request->session()->forget(['import_path', 'import_count']);

        return redirect()->route('admin.members')
            ->with('success', "Import complete: {$created} families created, {$skipped} skipped.")
            ->with('import_errors', $errors);
    }

    public function template()
    {
        $headers = [
            'Account Name',
            'Primary First Name', 'Primary Last Name',
            'Primary Email', 'Primary Cell Phone',
            'Primary Hebrew Name', 'Primary Gender', 'Primary Birthday',
            'Secondary First Name', 'Secondary Last Name',
            'Secondary Email', 'Secondary Cell Phone',
            'Secondary Hebrew Name', 'Secondary Gender', 'Secondary Birthday',
            'Street Address', 'City', 'State', 'Zip Code',
            'Account Type', 'Date Joined', 'Notes',
        ];

        $examples = [
            [
                'Cohen Family',
                'David', 'Cohen', 'david@cohen.com', '(901) 555-1234',
                'Dovid ben Avraham', 'Male', '1975-03-15',
                'Sarah', 'Cohen', 'sarah@cohen.com', '',
                'Sara bat Moshe', 'Female', '1978-07-22',
                '123 Oak St', 'Memphis', 'TN', '38120',
                'Member Family', '2010-09-01', '',
            ],
            [
                'Levy Family',
                'Michael', 'Levy', 'mlevy@example.com', '(901) 555-5678',
                '', 'Male', '',
                'Rachel', 'Levy', 'rlevy@example.com', '',
                '', 'Female', '',
                '456 Maple Ave', 'Memphis', 'TN', '38117',
                'Associate', '2022-01-15', '',
            ],
        ];

        return response()->streamDownload(function () use ($headers, $examples) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $headers);
            foreach ($examples as $row) {
                fputcsv($out, $row);
            }
            fclose($out);
        }, 'quickshul-import-template.csv', ['Content-Type' => 'text/csv']);
    }

    // ── Private helpers ────────────────────────────────────────

    private function parseCsv(string $path): array
    {
        if (!file_exists($path)) {
            return [[], []];
        }

        $handle  = fopen($path, 'r');
        $headers = [];
        $rows    = [];

        while (($line = fgetcsv($handle)) !== false) {
            if (empty($headers)) {
                $headers = array_map('trim', $line);
                continue;
            }
            // Skip entirely blank rows
            if (implode('', $line) === '') continue;
            $rows[] = $line;
        }

        fclose($handle);
        return [$headers, $rows];
    }

    private function detectColumns(array $headers): array
    {
        $normalized = array_map('strtolower', $headers);
        $map        = [];

        foreach ($this->aliases as $field => $aliases) {
            foreach ($normalized as $idx => $h) {
                if (in_array($h, $aliases)) {
                    $map[$field] = $idx;
                    break;
                }
            }
        }

        return $map;
    }

    private function mapRow(array $row, array $headers, array $columnMap): array
    {
        $data = [];
        foreach ($this->aliases as $field => $_) {
            $idx         = $columnMap[$field] ?? null;
            $data[$field] = ($idx !== null && isset($row[$idx])) ? trim($row[$idx]) : '';
        }

        // If no family_name column but we have primary_last, use "Last Family"
        if (!$data['family_name'] && $data['primary_last']) {
            $data['family_name'] = $data['primary_last'] . ' Family';
        }

        return $data;
    }

    private function resolveMembershipType(string $raw): string
    {
        if (!$raw) return $this->donorSlug();

        // Try exact QB label match first
        $types = MembershipType::where('active', true)->get();
        foreach ($types as $mt) {
            if ($mt->matchesQbLabel($raw)) return $mt->slug;
        }

        // Fuzzy fallback on label
        $n = strtolower(trim($raw));
        foreach ($types as $mt) {
            if (str_contains($n, strtolower($mt->label))) return $mt->slug;
        }

        return $this->donorSlug();
    }

    private function donorSlug(): string
    {
        return MembershipType::where('is_donor', true)->where('active', true)->value('slug') ?? 'donor';
    }

    private function resolveGender(string $raw): ?string
    {
        $n = strtolower(trim($raw));
        if (in_array($n, ['m', 'male'])) return 'Male';
        if (in_array($n, ['f', 'female'])) return 'Female';
        return null;
    }

    private function resolveDate(string $raw): ?string
    {
        if (!$raw) return null;

        // Try various formats
        foreach (['Y-m-d', 'm/d/Y', 'd/m/Y', 'm-d-Y', 'M d, Y', 'F j, Y'] as $fmt) {
            $d = \DateTime::createFromFormat($fmt, $raw);
            if ($d && $d->format($fmt) === $raw) {
                return $d->format('Y-m-d');
            }
        }

        // Last resort
        try {
            return (new \DateTime($raw))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
