<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetService;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use RealRashid\SweetAlert\Facades\Alert;

class GoogleSheetsController extends Controller
{
    public function connectGoogleAccount()
    {
        $client = new GoogleClient();
        $client->setAuthConfig(config_path('sheets-credentials.json'));
        $client->setRedirectUri(route('google.callback'));
        $client->addScope(Sheets::SPREADSHEETS);
        if (!session()->has('google_access_token') || session()->get('google_access_token') == null) {
            // Generate the authorization URL with the state parameter
            $authUrl = $client->createAuthUrl();
            return view('first', compact('authUrl'));
        } else {
            return redirect(route('google.form'));
        }
    }

    public function handleGoogleCallback(Request $request)
    {
        // Handle OAuth callback and store access token in session or database
        
        if (!$request->has('code')) {
            return redirect()->route('google.connect')->with('error', 'Invalid state parameter');
        }

        $client = new GoogleClient();
        $client->setAuthConfig(config_path('sheets-credentials.json'));
        $client->setRedirectUri(route('google.callback'));
        $client->addScope(Sheets::SPREADSHEETS);

        // Exchange authorization code for access token
        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));

        // Store the access token in the session
        session(['google_access_token' => $token]);

        return redirect()->route('google.form');
    }

    public function showForm()
    {
        // Check if Google account is connected (use session or database)
        if (session()->has('google_access_token') && session()->get('google_access_token') != null) {
            // Access token is present, the Google account is connected
            return view('form');
        } else {
            // Access token is not present, the Google account is not connected
            return redirect()->route('google.connect')->with('error', 'Google account not connected. Please connect first.');
        }
    }

    public function addToGoogleSheet(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ]);
        // Load the Google Sheets API client
        $client = new \Google_Client();
        $client->setAuthConfig(config_path('sheets-credentials.json'));
        $client->addScope(Sheets::SPREADSHEETS);
        // Get the access token (you need to implement a method to get the access token)
        $accessToken = session()->get('google_access_token'); 

        if ($accessToken) {
            // Spreadsheet ID 
            $spreadsheetId = '1cLfmOHwH0-3UzUxvMWzAgfcV-CUWAC2CXmBMVmsaiM4';
            // Sheet name
            $sheetName = 'Sheet1';
            // Data to be added
            $rowData = [
                $request->input('name'),
                $request->input('phone'),
                $request->input('email'),
            ];
            $googleSheetServices = new GoogleSheetService($accessToken, $spreadsheetId, $sheetName);
            $googleSheetServices->storeToGoogleSheets($rowData);
            Alert::toast('Data added to Google Sheet','success');
            return redirect()->route('google.form');
        } else {
            Alert::toast('Failed to authenticate with Google Sheets','success');
            return redirect()->route('google.form')->withErrors([
                'name' => 'Please provide a valid name.',
                'phone' => 'Please provide a valid phone number.',
                'email' => 'Please provide a valid email address.',
            ]);
        }
    }
}
