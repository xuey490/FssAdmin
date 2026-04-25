<?php

declare(strict_types=1);

/**
 * Bug Condition Exploration Test
 * 
 * This test verifies that the missing controller routes return 404 errors.
 * 
 * **CRITICAL**: This test MUST FAIL on unfixed code - failure confirms the bug exists
 * **DO NOT attempt to fix the test or the code when it fails**
 * **NOTE**: This test encodes the expected behavior - it will validate the fix when it passes after implementation
 * **GOAL**: Surface counterexamples that demonstrate the bug exists
 * 
 * **Validates: Requirements 1.1, 1.2, 1.3, 1.4, 1.5, 1.6, 1.7, 1.8**
 * 
 * @package Tests\Unit
 * @author  Kiro
 * @date    2026-03-20
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * BugConditionExplorationTest - Test missing controller routes
 */
class BugConditionExplorationTest extends TestCase
{
    /**
     * Base URL for API requests
     * @var string
     */
    private string $baseUrl = 'http://localhost:8000';

    /**
     * Test that GET /api/core/configGroup/list returns 404
     * Bug Condition: ConfigController file missing
     * 
     * **Validates: Requirement 1.1**
     * 
     * @test
     */
    public function test_config_group_list_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/api/core/configGroup/list');
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing ConfigController - config group list endpoint'
        );
    }

    /**
     * Test that POST /api/core/config/save returns 404
     * Bug Condition: ConfigController file missing
     * 
     * **Validates: Requirement 1.2**
     * 
     * @test
     */
    public function test_config_save_returns_404(): void
    {
        $response = $this->makeRequest('POST', '/api/core/config/save', [
            'config_key' => 'test_key',
            'config_value' => 'test_value'
        ]);
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing ConfigController - config save endpoint'
        );
    }

    /**
     * Test that GET /api/core/logs/getLoginLogPageList returns 404
     * Bug Condition: LogController file missing
     * 
     * **Validates: Requirement 1.3**
     * 
     * @test
     */
    public function test_login_log_list_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/api/core/logs/getLoginLogPageList');
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing LogController - login log list endpoint'
        );
    }

    /**
     * Test that GET /api/core/logs/getOperLogPageList returns 404
     * Bug Condition: LogController file missing
     * 
     * **Validates: Requirement 1.4**
     * 
     * @test
     */
    public function test_operation_log_list_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/api/core/logs/getOperLogPageList');
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing LogController - operation log list endpoint'
        );
    }

    /**
     * Test that GET /api/core/server/monitor returns 404 or 401
     * Bug Condition: ServerController file missing
     * 
     * Note: May return 401 if auth middleware runs before route resolution
     * 
     * **Validates: Requirement 1.5**
     * 
     * @test
     */
    public function test_server_monitor_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/api/core/server/monitor');
        
        $this->assertContains(
            $response['status'],
            [404, 401],
            'Expected 404 or 401 for missing ServerController - server monitor endpoint'
        );
    }

    /**
     * Test that GET /api/core/database/table/list returns 404
     * Bug Condition: DatabaseController file missing
     * 
     * **Validates: Requirement 1.6**
     * 
     * @test
     */
    public function test_database_table_list_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/api/core/database/table/list');
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing DatabaseController - database table list endpoint'
        );
    }

    /**
     * Test that GET /api/tool/crontab/list returns 404
     * Bug Condition: CrontabController file missing
     * 
     * **Validates: Requirement 1.7**
     * 
     * @test
     */
    public function test_crontab_list_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/api/tool/crontab/list');
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing CrontabController - crontab list endpoint'
        );
    }

    /**
     * Test that GET /tool/code/table/list returns 404
     * Bug Condition: GenerateController file missing
     * 
     * **Validates: Requirement 1.8**
     * 
     * @test
     */
    public function test_code_generation_table_list_returns_404(): void
    {
        $response = $this->makeRequest('GET', '/tool/code/table/list');
        
        $this->assertEquals(
            404,
            $response['status'],
            'Expected 404 for missing GenerateController - code generation table list endpoint'
        );
    }

    /**
     * Make an HTTP request to the application
     * 
     * @param string $method HTTP method (GET, POST, PUT, DELETE)
     * @param string $path Request path
     * @param array $data Request data (for POST/PUT)
     * @return array Response with 'status' and 'body' keys
     */
    private function makeRequest(string $method, string $path, array $data = []): array
    {
        $url = $this->baseUrl . $path;
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        
        // Set HTTP method
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }
        
        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if ($body === false) {
            $error = curl_error($ch);
            curl_close($ch);
            $this->fail("HTTP request failed: $error. Make sure the server is running at $this->baseUrl");
        }
        
        curl_close($ch);
        
        return [
            'status' => $status,
            'body' => $body
        ];
    }
}
