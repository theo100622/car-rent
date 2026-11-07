
<?php

use Illuminate\Support\Facades\Route;

// Display the Car Rental Dashboard
Route::get('/', function () {
    return view('dashboard');
});

// API to retrieve all rental records
Route::get('/api/rentals', function () {

    $path = storage_path('app/rentals.json');

    if (!file_exists($path)) {
        return response()->json([
            'message' => 'Rental data not found'
        ], 404);
    }

    $records = json_decode(
        file_get_contents($path),
        true
    );

    return response()->json($records);
});
