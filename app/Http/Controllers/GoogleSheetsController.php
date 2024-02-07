<?php

namespace App\Http\Controllers;

use App\Services\GoogleSheetService;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use RealRashid\SweetAlert\Facades\Alert;

class GoogleSheetsController extends Controller
{
    /**
     * The function connects to a Google account using OAuth 2.0 and redirects to the Google
     * authorization page if the access token is not available in the session.
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse a view or a redirect. If the session does not have a 'google_access_token' or if
     * it is null, it will return a view with the generated authorization URL. Otherwise, it will
     * redirect to the 'google.form' route.
     */
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

    /**
     * The function handles the OAuth callback from Google, stores the access token in the session, and
     * redirects the user to a form.
     * 
     * @param Request $request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains information about the request such as the request
     * method, headers, query parameters, and form data.
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse redirect response to the "google.form" route.
     */
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

        $client->setAccessToken($token);
        $sheetServices = new GoogleSheetService($token, null, null);
        $createSheet = $sheetServices->createSheets();
        if ($createSheet['success']) {
            return redirect()->route('google.form');
        } else {
            Alert::toast('Failed Create Sheets. '. $createSheet['message'], 'error')->showCloseButton()->background('#F03D3E');
            return redirect()->route('google.form');
        }
    }

    /**
     * The function checks if a Google account is connected and either displays a form or redirects to
     * connect the account.
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse view called 'form' if the Google account is connected. If the Google account is not
     * connected, it is redirecting to a route called 'google.connect' and passing an error message
     * 'Google account not connected. Please connect first.'
     */
    public function showForm()
    {
        // Check if Google account is connected (use session or database)
        if (session()->has('google_access_token') && session()->get('google_access_token') != null) {
            // Access token is present, the Google account is connected
            $spreadsheet = session()->get('google_sheet_id');
            return view('form', compact('spreadsheet'));
        } else {
            // Access token is not present, the Google account is not connected
            return redirect()->route('google.connect')->with('error', 'Google account not connected. Please connect first.');
        }
    }

    /**
     * The function `addToGoogleSheet` adds data from a form submission to a Google Sheet, and displays
     * success or error messages accordingly.
     * 
     * @param Request request The  parameter is an instance of the Request class, which
     * represents an HTTP request. It contains all the data and information about the current request,
     * such as the request method, URL, headers, and input data.
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse redirect response to the 'google.form' route.
     */
    public function addToGoogleSheet(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required',
        ]);

        // Get the access token (you need to implement a method to get the access token)
        $accessToken = session()->get('google_access_token');

        if ($accessToken) {
            // Spreadsheet ID 
            $spreadsheetId = session()->get('google_sheet_id');
            // Sheet name
            $sheetName = 'Sheet1';
            // Data to be added
            $rowData = [
                $request->input('name'),
                $request->input('phone'),
                $request->input('email'),
            ];
            $googleSheetServices = new GoogleSheetService($accessToken, $spreadsheetId, $sheetName);
            $addToSheets = $googleSheetServices->storeToGoogleSheets($rowData);
            if ($addToSheets['success']) {
                Alert::toast($addToSheets['message'], 'success')->showCloseButton()->background('#007B40');
            } else {
                Alert::toast($addToSheets['message'], 'error')->showCloseButton()->background('#F03D3E');
            }
            return redirect()->route('google.form');
        } else {
            // Only add an error for access token if validation passes
            $errors = [];

            // Check if there are validation errors and add them to the array
            if ($validator = $this->getValidationErrors($request)) {
                $errors += $validator;
                Alert::toast('Failed to submit. Please check form..!', 'error')->showCloseButton()->background('#F03D3E');
                return redirect()->route('google.form')->withErrors($errors);
            } else {
                Alert::toast('Failed to authenticate with Google Sheets', 'error')->showCloseButton()->background('#F03D3E');
                return redirect()->route('google.form');
            }
        }
    }

    /**
     * The function `getValidationErrors` validates a request's input fields for name, phone, and
     * email, and returns any validation errors if they exist.
     * 
     * @param Request $request The  parameter is an instance of the Request class, which
     * represents an HTTP request made to the server. It contains information about the request, such
     * as the request method, headers, and input data.
     * 
     * @return array|null array of validation error messages if the validation fails. If the validation passes,
     * it returns null.
     */
    private function getValidationErrors(Request $request)
    {
        // Validate the request without stopping on first validation failure
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|email',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return $validator->errors()->messages();
        }

        return null;
    }
}
