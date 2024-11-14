<?php
// app/api.php

class CVRAPI {
    private $baseUrl = 'https://cvrapi.dk/api';
    private $country = 'dk';
    private $userAgent = 'CompanyManager/1.0 (education@example.com)'; // Required by CVRAPI

    /**
     * Mock data for testing when API is unavailable
     */
    private $mockData = [
        '28856636' => [
            'name' => 'ÅRHUS ApS',
            'address' => 'Testvej 1',
            'zipcode' => '8000',
            'city' => 'Århus C',
            'phone' => '12345678',
            'email' => 'info@aarhus.dk',
            'industrydesc' => 'IT Services',
            'companytype' => 'ApS',
            'employees' => '10-19',
            'startdate' => '2005-01-01'
        ],
        // Add more mock data for other CVR numbers if needed
    ];

    /**
     * Fetch company data from CVR API or fallback to mock data
     * @param string $cvr_number
     * @return array
     */
    public function fetchCompanyData($cvr_number) {
        try {
            // Validate CVR number
            if (!preg_match('/^\d{8}$/', $cvr_number)) {
                throw new Exception('Invalid CVR number format');
            }

            // First try the real API
            $data = $this->callRealApi($cvr_number);
            
            // If API call fails, use mock data
            if (!$data['success'] && isset($this->mockData[$cvr_number])) {
                return [
                    'success' => true,
                    'data' => [
                        'name' => $this->mockData[$cvr_number]['name'],
                        'address' => $this->formatAddress($this->mockData[$cvr_number]),
                        'phone' => $this->mockData[$cvr_number]['phone'],
                        'email' => $this->mockData[$cvr_number]['email'],
                        'industry' => $this->mockData[$cvr_number]['industrydesc'],
                        'company_type' => $this->mockData[$cvr_number]['companytype'],
                        'employees' => $this->mockData[$cvr_number]['employees'],
                        'established' => $this->mockData[$cvr_number]['startdate']
                    ],
                    'source' => 'mock' // Indicate this is mock data
                ];
            }

            return $data;

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Make actual API call
     * @param string $cvr_number
     * @return array
     */
    private function callRealApi($cvr_number) {
        try {
            $url = sprintf(
                '%s?search=%s&country=%s',
                $this->baseUrl,
                urlencode($cvr_number),
                $this->country
            );

            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => [
                        'User-Agent: ' . $this->userAgent
                    ],
                    'timeout' => 5 // 5 seconds timeout
                ]
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                throw new Exception('Failed to connect to CVR API');
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid response from CVR API');
            }

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
                ],
                'source' => 'api'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Format address from data
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
     * Validate company exists
     * @param string $cvr_number
     * @return bool
     */
    public function validateCompany($cvr_number) {
        $result = $this->fetchCompanyData($cvr_number);
        return $result['success'] && isset($result['data']['name']);
    }
}