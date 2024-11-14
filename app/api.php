<?php
// app/api.php

class CVRAPI {
    private $baseUrl = 'https://cvrapi.dk/api';
    private $country = 'dk';
    private $userAgent = 'Company Manager - Educational Project';

    /**
     * Fetch company data from CVR API
     * @param string $cvr_number
     * @return array
     */
    public function fetchCompanyData($cvr_number) {
        try {
            // Validate CVR number
            if (!preg_match('/^\d{8}$/', $cvr_number)) {
                throw new Exception('Invalid CVR number format');
            }

            // Prepare API URL
            $url = sprintf(
                '%s?search=%s&country=%s',
                $this->baseUrl,
                urlencode($cvr_number),
                $this->country
            );

            // Prepare context for API call
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: ' . $this->userAgent
                    ]
                ]
            ]);

            // Make API call
            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                throw new Exception('Failed to connect to CVR API');
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid response from CVR API');
            }

            // Format the response
            return [
                'success' => true,
                'data' => [
                    'name' => $data['name'] ?? null,
                    'address' => $this->formatAddress($data),
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'industry' => $data['industrydesc'] ?? null,
                    'company_type' => $data['companytype'] ?? null,
                    'employees' => $data['employees'] ?? null,
                    'established' => $data['startdate'] ?? null
                ]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format address from API response
     * @param array $data
     * @return string
     */
    private function formatAddress($data) {
        $addressParts = array_filter([
            $data['address'] ?? null,
            $data['zipcode'] ?? null,
            $data['city'] ?? null
        ]);
        
        return !empty($addressParts) ? implode(', ', $addressParts) : null;
    }

    /**
     * Validate company exists in CVR register
     * @param string $cvr_number
     * @return bool
     */
    public function validateCompany($cvr_number) {
        $result = $this->fetchCompanyData($cvr_number);
        return $result['success'] && isset($result['data']['name']);
    }
}