<?php
/**
 * Validator Tests
 *
 * Tests for input validation functions
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../helpers/Validator.php';

class ValidatorTest extends TestCase
{
    /**
     * Test email validation
     */
    public function testValidateEmail()
    {
        // Valid emails
        $this->assertTrue(Validator::validateEmail('test@example.com'), 'Should validate correct email');
        $this->assertTrue(Validator::validateEmail('user.name+tag@example.co.uk'), 'Should validate complex email');

        // Invalid emails
        $this->assertFalse(Validator::validateEmail('invalid.email'), 'Should reject email without @');
        $this->assertFalse(Validator::validateEmail('@example.com'), 'Should reject email without local part');
        $this->assertFalse(Validator::validateEmail('test@'), 'Should reject email without domain');
        $this->assertFalse(Validator::validateEmail(''), 'Should reject empty email');
    }

    /**
     * Test phone validation
     */
    public function testValidatePhone()
    {
        // Valid phone numbers (French format)
        $this->assertTrue(Validator::validatePhone('0123456789'), 'Should validate 10-digit phone');
        $this->assertTrue(Validator::validatePhone('+33123456789'), 'Should validate international format');
        $this->assertTrue(Validator::validatePhone('01 23 45 67 89'), 'Should validate formatted phone');

        // Invalid phone numbers
        $this->assertFalse(Validator::validatePhone('123'), 'Should reject too short phone');
        $this->assertFalse(Validator::validatePhone('abcdefghij'), 'Should reject non-numeric phone');
        $this->assertFalse(Validator::validatePhone(''), 'Should reject empty phone');
    }

    /**
     * Test SIRET validation
     */
    public function testValidateSiret()
    {
        // Valid SIRET (14 digits)
        $this->assertTrue(Validator::validateSiret('12345678901234'), 'Should validate 14-digit SIRET');

        // Invalid SIRET
        $this->assertFalse(Validator::validateSiret('123456789'), 'Should reject too short SIRET');
        $this->assertFalse(Validator::validateSiret('12345678901234567'), 'Should reject too long SIRET');
        $this->assertFalse(Validator::validateSiret('1234567890abcd'), 'Should reject non-numeric SIRET');
        $this->assertFalse(Validator::validateSiret(''), 'Should reject empty SIRET');
    }

    /**
     * Test password strength
     */
    public function testValidatePassword()
    {
        // Strong passwords
        $this->assertTrue(Validator::validatePassword('StrongPass123!'), 'Should validate strong password');
        $this->assertTrue(Validator::validatePassword('MyP@ssw0rd2024'), 'Should validate complex password');

        // Weak passwords
        $this->assertFalse(Validator::validatePassword('weak'), 'Should reject too short password');
        $this->assertFalse(Validator::validatePassword('12345678'), 'Should reject numeric-only password');
        $this->assertFalse(Validator::validatePassword(''), 'Should reject empty password');
    }

    /**
     * Test date validation
     */
    public function testValidateDate()
    {
        // Valid dates
        $this->assertTrue(Validator::validateDate('2024-11-18'), 'Should validate ISO date');
        $this->assertTrue(Validator::validateDate('18/11/2024', 'd/m/Y'), 'Should validate custom format date');

        // Invalid dates
        $this->assertFalse(Validator::validateDate('2024-13-45'), 'Should reject invalid date');
        $this->assertFalse(Validator::validateDate('not-a-date'), 'Should reject non-date string');
        $this->assertFalse(Validator::validateDate(''), 'Should reject empty date');
    }

    /**
     * Test required field validation
     */
    public function testRequired()
    {
        $this->assertTrue(Validator::required('value'), 'Should validate non-empty string');
        $this->assertTrue(Validator::required('0'), 'Should validate zero string');
        $this->assertTrue(Validator::required(0), 'Should validate zero integer');

        $this->assertFalse(Validator::required(''), 'Should reject empty string');
        $this->assertFalse(Validator::required(null), 'Should reject null');
        $this->assertFalse(Validator::required('   '), 'Should reject whitespace-only string');
    }

    /**
     * Test numeric validation
     */
    public function testNumeric()
    {
        $this->assertTrue(Validator::numeric(123), 'Should validate integer');
        $this->assertTrue(Validator::numeric('456'), 'Should validate numeric string');
        $this->assertTrue(Validator::numeric(12.34), 'Should validate float');

        $this->assertFalse(Validator::numeric('abc'), 'Should reject non-numeric string');
        $this->assertFalse(Validator::numeric('12abc'), 'Should reject mixed alphanumeric');
    }

    /**
     * Test min length validation
     */
    public function testMinLength()
    {
        $this->assertTrue(Validator::minLength('hello', 3), 'Should validate string above min length');
        $this->assertTrue(Validator::minLength('hello', 5), 'Should validate string equal to min length');

        $this->assertFalse(Validator::minLength('hi', 5), 'Should reject string below min length');
    }

    /**
     * Test max length validation
     */
    public function testMaxLength()
    {
        $this->assertTrue(Validator::maxLength('hello', 10), 'Should validate string below max length');
        $this->assertTrue(Validator::maxLength('hello', 5), 'Should validate string equal to max length');

        $this->assertFalse(Validator::maxLength('hello world', 5), 'Should reject string above max length');
    }

    /**
     * Test URL validation
     */
    public function testValidateUrl()
    {
        // Valid URLs
        $this->assertTrue(Validator::validateUrl('https://example.com'), 'Should validate HTTPS URL');
        $this->assertTrue(Validator::validateUrl('http://example.com/path'), 'Should validate HTTP URL with path');
        $this->assertTrue(Validator::validateUrl('https://sub.example.com?query=value'), 'Should validate URL with query');

        // Invalid URLs
        $this->assertFalse(Validator::validateUrl('not-a-url'), 'Should reject malformed URL');
        $this->assertFalse(Validator::validateUrl('ftp://example.com'), 'Should reject non-HTTP protocol');
        $this->assertFalse(Validator::validateUrl(''), 'Should reject empty URL');
    }

    /**
     * Test sanitization
     */
    public function testSanitize()
    {
        $dirty = '<script>alert("XSS")</script>Hello';
        $clean = Validator::sanitize($dirty);

        $this->assertStringNotContainsString('<script>', $clean, 'Should remove script tags');
        $this->assertStringContainsString('Hello', $clean, 'Should preserve clean text');
    }
}
