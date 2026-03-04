<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Http\Kernel')->handle(
    $request = \Illuminate\Http\Request::capture()
);

use App\Models\FamilyMember;
$member = FamilyMember::find(105);
if ($member) {
    echo "Member 105: " . $member->full_name . "\n";
    echo "Date of Birth: " . ($member->date_of_birth ? $member->date_of_birth->format('Y-m-d') : 'null') . "\n";
    echo "Hebrew DOB: " . ($member->hebrew_date_of_birth ?? 'null') . "\n";
    echo "Hebrew DOB Override: " . ($member->hebrew_dob_override ? 'true' : 'false') . "\n";
    
    // Now test the conversion
    $hebrewDateService = app('App\Services\HebrewDateService');
    if ($member->date_of_birth) {
        $h = $hebrewDateService->gregorianToHebrew($member->date_of_birth);
        echo "\nConverted Hebrew Date: " . $h['formatted'] . "\n";
        echo "Month name: " . $h['month_name'] . " (month number: " . $h['month'] . ")\n";
    }
} else {
    echo "Member 105 not found\n";
}
