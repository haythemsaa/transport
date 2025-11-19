<?php
/**
 * Authentication Tests
 *
 * Tests for authentication and session management
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../helpers/Auth.php';

class AuthTest extends TestCase
{
    /**
     * Test password hashing
     */
    public function testPasswordHash()
    {
        $password = 'MySecurePassword123!';
        $hash = Auth::hashPassword($password);

        // Hash should not be empty
        $this->assertNotEmpty($hash, 'Password hash should not be empty');

        // Hash should not equal plaintext password
        $this->assertNotEquals($password, $hash, 'Hash should not equal plaintext password');

        // Hash should be at least 60 characters (bcrypt standard)
        $this->assertGreaterThanOrEqual(60, strlen($hash), 'Bcrypt hash should be at least 60 characters');

        // Hash should start with $2y$ (bcrypt identifier)
        $this->assertStringStartsWith('$2y$', $hash, 'Hash should use bcrypt algorithm');
    }

    /**
     * Test password verification
     */
    public function testPasswordVerify()
    {
        $password = 'MySecurePassword123!';
        $hash = Auth::hashPassword($password);

        // Correct password should verify
        $this->assertTrue(Auth::verifyPassword($password, $hash), 'Correct password should verify');

        // Incorrect password should not verify
        $this->assertFalse(Auth::verifyPassword('WrongPassword', $hash), 'Incorrect password should not verify');

        // Empty password should not verify
        $this->assertFalse(Auth::verifyPassword('', $hash), 'Empty password should not verify');
    }

    /**
     * Test CSRF token generation
     */
    public function testGenerateCsrfToken()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token1 = Auth::generateCsrfToken();
        $token2 = Auth::generateCsrfToken();

        // Token should not be empty
        $this->assertNotEmpty($token1, 'CSRF token should not be empty');

        // Token should be at least 32 characters (for security)
        $this->assertGreaterThanOrEqual(32, strlen($token1), 'CSRF token should be sufficiently long');

        // Multiple calls should return the same token (within same session)
        $this->assertEquals($token1, $token2, 'CSRF token should remain consistent in session');
    }

    /**
     * Test CSRF token verification
     */
    public function testVerifyCsrfToken()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $token = Auth::generateCsrfToken();

        // Valid token should verify
        $this->assertTrue(Auth::verifyCsrfToken($token), 'Valid CSRF token should verify');

        // Invalid token should not verify
        $this->assertFalse(Auth::verifyCsrfToken('invalid-token'), 'Invalid CSRF token should not verify');

        // Empty token should not verify
        $this->assertFalse(Auth::verifyCsrfToken(''), 'Empty CSRF token should not verify');
    }

    /**
     * Test user type validation
     */
    public function testIsValidUserType()
    {
        // Valid user types
        $this->assertTrue(Auth::isValidUserType('carrier'), 'carrier should be valid user type');
        $this->assertTrue(Auth::isValidUserType('shipper'), 'shipper should be valid user type');
        $this->assertTrue(Auth::isValidUserType('both'), 'both should be valid user type');

        // Invalid user types
        $this->assertFalse(Auth::isValidUserType('admin'), 'admin should not be valid user type');
        $this->assertFalse(Auth::isValidUserType('invalid'), 'invalid should not be valid user type');
        $this->assertFalse(Auth::isValidUserType(''), 'empty should not be valid user type');
    }

    /**
     * Test token generation (for password reset, etc.)
     */
    public function testGenerateToken()
    {
        $token1 = Auth::generateToken();
        $token2 = Auth::generateToken();

        // Token should not be empty
        $this->assertNotEmpty($token1, 'Token should not be empty');

        // Token should be at least 32 characters
        $this->assertGreaterThanOrEqual(32, strlen($token1), 'Token should be sufficiently long');

        // Multiple calls should generate different tokens
        $this->assertNotEquals($token1, $token2, 'Each token should be unique');

        // Token should be hexadecimal
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token1, 'Token should be hexadecimal');
    }

    /**
     * Test session cleanup
     */
    public function testSessionCleanup()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Set some session data
        $_SESSION['test_data'] = 'value';
        $_SESSION['user_id'] = 123;

        // Verify data is set
        $this->assertEquals('value', $_SESSION['test_data'], 'Session data should be set');

        // Cleanup should preserve important data but remove test data
        Auth::cleanupSession(['user_id']);

        // Important data should remain
        $this->assertEquals(123, $_SESSION['user_id'], 'Preserved session data should remain');
    }

    /**
     * Test rate limiting check
     */
    public function testCheckRateLimit()
    {
        $key = 'test_limit_' . time();

        // First attempt should pass
        $this->assertTrue(Auth::checkRateLimit($key, 5, 60), 'First attempt should pass rate limit');

        // Multiple rapid attempts
        for ($i = 0; $i < 4; $i++) {
            Auth::checkRateLimit($key, 5, 60);
        }

        // Should still pass (under limit)
        $this->assertTrue(Auth::checkRateLimit($key, 5, 60), 'Should still pass under rate limit');

        // Exceeding limit
        for ($i = 0; $i < 10; $i++) {
            Auth::checkRateLimit($key, 5, 60);
        }

        // Should fail (over limit)
        $this->assertFalse(Auth::checkRateLimit($key, 5, 60), 'Should fail when exceeding rate limit');
    }
}
