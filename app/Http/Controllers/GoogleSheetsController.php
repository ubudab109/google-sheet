<?php

namespace App\Http\Controllers;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Google\Service\Sheets\ValueRange;

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
        // Load the Google Sheets API client
        $client = new \Google_Client();
        $client->setAuthConfig(config_path('sheets-credentials.json'));
        $client->addScope(Sheets::SPREADSHEETS);
        

        // Get the access token (you need to implement a method to get the access token)
        $accessToken = session()->get('google_access_token'); 

        if ($accessToken) {
            $client->setAccessToken($accessToken);

            // Create Google Sheets service
            $sheetsService = new Sheets($client);

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

            // Append data to the sheet
            $requestBody = new ValueRange([
                'values' => [$rowData],
            ]);

            $params = [
                'valueInputOption' => 'RAW',
            ];

            $sheetsService->spreadsheets_values->append(
                $spreadsheetId,
                $sheetName,
                $requestBody,
                $params
            );

            return redirect()->route('google.form')->with('success', 'Data added to Google Sheet');
        } else {
            return redirect()->route('google.form')->with('error', 'Failed to authenticate with Google Sheets');
        }

        return redirect()->route('google.form')->with('success', 'Data added to Google Sheet');
    }
}
