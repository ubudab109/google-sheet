<?php

namespace App\Services;

use Google\Service\Sheets;
use Google\Service\Sheets\Spreadsheet;
use Google\Service\Sheets\ValueRange;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    protected $accessToken, $client, $spreadsheetId, $sheetName;

    /**
     * GoogleSheetService Constructor
     * 
     * @param string $accessToken The access token from google success response
     * @param string $spreadsheetId The spreadsheet ID get from sheets 
     * @param string $sheetName The sheetname from sheets
     */
    public function __construct($accessToken, $spreadsheetId = null, $sheetName = null)
    {
        $this->spreadsheetId = $spreadsheetId;
        $this->accessToken = $accessToken;
        $this->sheetName = $sheetName;
        $this->client = new \Google_Client();
        $this->client->setAuthConfig(config_path('sheets-credentials.json'));
        $this->client->addScope(Sheets::SPREADSHEETS);
        $this->client->setAccessToken($this->accessToken);
    }

    /**
     * The function `storeToGoogleSheets` stores an array of data to a Google Sheets spreadsheet using
     * the Google Sheets API.
     * 
     * @param array $data The `` parameter is an array that contains the values you want to store
     * in Google Sheets. Each value in the array represents a row in the spreadsheet.
     * @return array
     */
    public function storeToGoogleSheets(array $data)
    {
        try {
            $sheetsService = new Sheets($this->client);
            $requestBody = new ValueRange([
                'values' => [$data],
            ]);
            $params = [
                'valueInputOption' => 'RAW',
            ];
            $sheetsService->spreadsheets_values->append(
                $this->spreadsheetId,
                $this->sheetName,
                $requestBody,
                $params
            );
            return [
                'success' => true,
                'message' => 'Data added to Google Sheet'
            ];
        } catch (\Google_Exception $err) {
            Log::info($err->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to submit' . $err->getMessage()
            ];
        }
    }

    /**
     * The function creates a new Google Sheets spreadsheet, adds a header row and sample data, and
     * appends it to the specified range in the sheet.
     * 
     * @return array
     */
    public function createSheets()
    {
        try {
            // Create Google Sheets service
            $sheetsService = new Sheets($this->client);

            // Create a new spreadsheet
            $spreadsheet = new Spreadsheet([
                'properties' => [
                    'title' => 'My Dynamic Spreadsheet ' . rand(1, 10),  // Title
                ],
            ]);

            $spreadsheet = $sheetsService->spreadsheets->create($spreadsheet);

            // Retrieve the ID of the created spreadsheet
            $spreadsheetId = $spreadsheet->spreadsheetId;
            session(['google_sheet_id' => $spreadsheetId]);

            // Define data to be added
            $rowData = [
                ['Name', 'Phone', 'Email'], // Header row
                ['John Doe', '1234567890', 'john@example.com'], // Sample data row
            ];

            // Create a ValueRange object
            $valueRange = new ValueRange([
                'values' => $rowData,
            ]);

            // Set the range where you want to append the data (e.g., Sheet1!A1)
            $range = 'Sheet1!A1';

            // Append data to the sheet
            $sheetsService->spreadsheets_values->append($spreadsheetId, $range, $valueRange, [
                'valueInputOption' => 'RAW',
            ]);

            return [
                'success' => true,
                'message' => 'Success Create Google Sheets',
            ];
        } catch (\Google_Exception $err) {
            Log::info($err->getMessage());
            return [
                'success' => false,
                'message' => $err->getMessage(),
            ];
        }
    }
}
