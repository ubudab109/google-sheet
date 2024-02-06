<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Http\Request;

class GoogleSheetsController extends Controller
{
    public function connectGoogleAccount()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(config_path('sheets-credentials.json'));
        $client->setRedirectUri(route('google.callback'));
        $client->addScope(Sheets::SPREADSHEETS);

        $authUrl = $client->createAuthUrl();
        
        return view('first', compact('authUrl'));
    }

    public function handleGoogleCallback(Request $request)
    {
        // Handle OAuth callback and store access token in session or database
        // ...

        return redirect()->route('google.form');
    }

    public function showForm()
    {
        // Check if Google account is connected (use session or database)
        // ...

        return view('form');
    }

    public function addToGoogleSheet(Request $request)
    {
        // Add data to Google Sheet using Sheets API
        // ...

        return redirect()->route('google.form')->with('success', 'Data added to Google Sheet');
    }
}
