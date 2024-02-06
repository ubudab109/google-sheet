<?php

namespace App\Services;

use Google\Service\Sheets;
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
    public function __construct($accessToken, $spreadsheetId, $sheetName)
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
     * @throws \Google_Exception
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
        } catch (\Google_Exception $err) {
            Log::info($err->getMessage());
            throw new \Exception($err->getMessage());
        } 
    }
}