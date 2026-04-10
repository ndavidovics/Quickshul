@extends('layouts.admin')
@section('title', 'Import Members')

@section('content')
<div style="display:flex;align-items:center;gap:0.75rem;margin-bottom:1.75rem">
    <a href="{{ route('admin.members') }}" class="btn btn-outline btn-sm">← Members</a>
    <div>
        <h1 class="page-title" style="margin-bottom:0">Import Members</h1>
        <p class="page-subtitle" style="margin-bottom:0">Import from ShulCloud, spreadsheet, or any CSV</p>
    </div>
</div>

@if(session('import_errors') && count(session('import_errors')))
<div style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.3);border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.25rem">
    <div style="font-weight:600;color:#e74c3c;margin-bottom:0.5rem">Some rows had errors:</div>
    <ul style="margin:0;padding-left:1.25rem;font-size:0.82rem;color:#e74c3c;line-height:1.8">
        @foreach(session('import_errors') as $err)
            <li>{{ $err }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(isset($mapped))
    {{-- ── Preview Step ───────────────────────────────────── --}}
    <div class="card" style="margin-bottom:1.5rem">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.75rem">
            <div>
                <div style="font-weight:700;font-size:1rem;color:var(--text)">Preview — first {{ count($mapped) }} of {{ $totalRows }} rows</div>
                <div class="text-sm text-muted" style="margin-top:0.2rem">
                    Detected {{ count($columnMap) }} of {{ count($headers) }} columns.
                    @php
                        $dupeCount = collect($mapped)->where('_duplicate', true)->count();
                        $allDupes  = collect(array_fill(0, $totalRows, null)); // approximation
                    @endphp
                    @if($dupeCount > 0)
                        <span style="color:var(--gold)">⚠ {{ $dupeCount }} shown have matching names already in your account.</span>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ route('admin.members.import.template') }}">@csrf</form>
        </div>

        <div style="overflow-x:auto;margin-bottom:1.25rem">
            <table class="table" style="font-size:0.78rem;min-width:900px">
                <thead>
                    <tr>
                        <th>Family Name</th>
                        <th>Primary</th>
                        <th>Spouse</th>
                        <th>Email(s)</th>
                        <th>Address</th>
                        <th>Type</th>
                        <th>Joined</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mapped as $row)
                    <tr style="{{ $row['_duplicate'] ? 'opacity:0.5' : '' }}">
                        <td style="font-weight:600">
                            {{ $row['family_name'] ?: '—' }}
                            @if($row['_duplicate'])
                                <span style="font-size:0.7rem;background:rgba(201,168,76,0.15);color:var(--gold);border-radius:4px;padding:0.1rem 0.4rem;margin-left:0.3rem">duplicate</span>
                            @endif
                        </td>
                        <td>
                            {{ trim(($row['primary_first'] ?? '') . ' ' . ($row['primary_last'] ?? '')) ?: '—' }}
                        </td>
                        <td>
                            {{ trim(($row['sec_first'] ?? '') . ' ' . ($row['sec_last'] ?? '')) ?: '—' }}
                        </td>
                        <td style="font-size:0.75rem">
                            @if($row['primary_email']){{ $row['primary_email'] }}@endif
                            @if($row['primary_email'] && $row['sec_email'])<br>@endif
                            @if($row['sec_email']){{ $row['sec_email'] }}@endif
                            @if(!$row['primary_email'] && !$row['sec_email'])—@endif
                        </td>
                        <td style="font-size:0.75rem">
                            {{ $row['city'] ? $row['city'] . ($row['state'] ? ', ' . $row['state'] : '') : '—' }}
                        </td>
                        <td style="font-size:0.75rem">{{ $row['membership_type'] ?: '—' }}</td>
                        <td style="font-size:0.75rem">{{ $row['member_since'] ?: '—' }}</td>
                        <td>
                            @if($row['_duplicate'])
                                <span style="font-size:0.7rem;color:var(--text-muted)">will skip</span>
                            @else
                                <span style="font-size:0.7rem;color:#2ecc71">✓ import</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($totalRows > count($mapped))
            <p class="text-sm text-muted" style="margin-bottom:1.25rem">
                … and {{ $totalRows - count($mapped) }} more rows not shown in preview.
            </p>
        @endif

        <form method="POST" action="{{ route('admin.members.import.process') }}">
            @csrf
            <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap">
                <button type="submit" class="btn btn-gold">
                    Import {{ $totalRows }} Families
                </button>
                <label style="display:flex;align-items:center;gap:0.45rem;font-size:0.875rem;color:var(--text-muted);cursor:pointer">
                    <input type="checkbox" name="skip_duplicates" value="1" checked>
                    Skip families with matching names
                </label>
                <a href="{{ route('admin.members.import') }}" class="btn btn-outline">Start Over</a>
            </div>
        </form>
    </div>

    {{-- Detected column mapping --}}
    <div class="card" style="margin-bottom:1.5rem">
        <div style="font-weight:600;font-size:0.9rem;margin-bottom:0.75rem">Detected column mapping</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:0.4rem 1rem">
            @php
            $fieldLabels = [
                'family_name'    => 'Family Name',
                'primary_first'  => 'Primary First Name',
                'primary_last'   => 'Primary Last Name',
                'primary_email'  => 'Primary Email',
                'primary_phone'  => 'Primary Phone',
                'primary_hebrew' => 'Primary Hebrew Name',
                'primary_gender' => 'Primary Gender',
                'primary_dob'    => 'Primary Birthday',
                'sec_first'      => 'Spouse First Name',
                'sec_last'       => 'Spouse Last Name',
                'sec_email'      => 'Spouse Email',
                'sec_phone'      => 'Spouse Phone',
                'sec_hebrew'     => 'Spouse Hebrew Name',
                'sec_gender'     => 'Spouse Gender',
                'sec_dob'        => 'Spouse Birthday',
                'address'        => 'Street Address',
                'city'           => 'City',
                'state'          => 'State',
                'zip'            => 'ZIP Code',
                'membership_type'=> 'Membership Type',
                'member_since'   => 'Date Joined',
                'notes'          => 'Notes',
            ];
            @endphp
            @foreach($fieldLabels as $field => $label)
                <div style="font-size:0.78rem;display:flex;align-items:center;gap:0.4rem">
                    @if(isset($columnMap[$field]))
                        <span style="color:#2ecc71">✓</span>
                        <span style="color:var(--text-muted)">{{ $label }}</span>
                        <span style="font-family:monospace;font-size:0.72rem;color:var(--gold)">← "{{ $headers[$columnMap[$field]] }}"</span>
                    @else
                        <span style="color:rgba(136,153,187,0.4)">○</span>
                        <span style="color:rgba(136,153,187,0.5)">{{ $label }}</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

@else
    {{-- ── Upload Step ────────────────────────────────────── --}}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">

        <div class="card">
            <div style="font-weight:700;font-size:1rem;color:var(--text);margin-bottom:0.35rem">Upload CSV File</div>
            <p class="text-sm text-muted" style="margin-bottom:1.25rem">
                Upload a CSV from ShulCloud, Excel, or any system. The importer will auto-detect column names.
            </p>

            @if($errors->any())
                <div style="background:rgba(231,76,60,0.08);border:1px solid rgba(231,76,60,0.3);border-radius:6px;padding:0.75rem 1rem;font-size:0.85rem;color:#e74c3c;margin-bottom:1rem">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.members.import.preview') }}" enctype="multipart/form-data">
                @csrf
                <div style="margin-bottom:1.25rem">
                    <label class="form-label">CSV File <span style="color:#e74c3c">*</span></label>
                    <input type="file" name="csv_file" accept=".csv,.txt" class="form-control" required
                           style="padding:0.5rem 0.75rem;cursor:pointer">
                    <div class="text-sm text-muted" style="margin-top:0.3rem">Max 5 MB. UTF-8 encoding recommended.</div>
                </div>
                <button type="submit" class="btn btn-gold">Preview Import →</button>
            </form>
        </div>

        <div>
            <div class="card" style="margin-bottom:1.25rem">
                <div style="font-weight:700;font-size:0.9rem;margin-bottom:0.75rem">Supported formats</div>
                <ul style="margin:0;padding-left:1.25rem;font-size:0.85rem;color:var(--text-muted);line-height:2">
                    <li><strong style="color:var(--text)">ShulCloud</strong> — People Report export (CSV)</li>
                    <li><strong style="color:var(--text)">Generic spreadsheet</strong> — Name, Email, Phone, Address columns</li>
                    <li><strong style="color:var(--text)">QuickShul export</strong> — Re-import your own exports</li>
                </ul>
                <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid var(--border)">
                    <a href="{{ route('admin.members.import.template') }}" class="btn btn-outline btn-sm">
                        ⬇ Download Template CSV
                    </a>
                </div>
            </div>

            <div class="card">
                <div style="font-weight:700;font-size:0.9rem;margin-bottom:0.75rem">What gets imported</div>
                <ul style="margin:0;padding-left:1.25rem;font-size:0.82rem;color:var(--text-muted);line-height:1.9">
                    <li>Family account (name, address, membership type)</li>
                    <li>Primary adult (name, email, Hebrew name, birthday, gender)</li>
                    <li>Spouse/secondary adult (same fields)</li>
                    <li>Date joined</li>
                </ul>
                <div style="margin-top:0.75rem;padding-top:0.75rem;border-top:1px solid var(--border);font-size:0.82rem;color:var(--text-muted)">
                    <strong style="color:var(--text)">Not imported:</strong> financial balances, pledges, payments, yahrtzeits. Those require separate import or manual entry.
                </div>
                <div style="margin-top:0.75rem;font-size:0.82rem;color:var(--text-muted)">
                    <strong style="color:var(--text)">Duplicates:</strong> Families with a matching name are skipped by default.
                </div>
            </div>
        </div>
    </div>
@endif

@endsection
