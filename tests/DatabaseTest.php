<?php
/**
 * Database Connection Tests
 *
 * Basic tests to verify database connectivity and operations
 */

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../helpers/Database.php';

class DatabaseTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        // Get database instance
        $this->db = Database::getInstance();
    }

    /**
     * Test database connection is successful
     */
    public function testDatabaseConnection()
    {
        $connection = $this->db->getConnection();
        $this->assertInstanceOf(PDO::class, $connection, 'Database connection should return PDO instance');
    }

    /**
     * Test database query execution
     */
    public function testDatabaseQuery()
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->query("SELECT 1 as test");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(1, $result['test'], 'Simple query should return expected result');
    }

    /**
     * Test prepared statement
     */
    public function testPreparedStatement()
    {
        $connection = $this->db->getConnection();
        $stmt = $connection->prepare("SELECT ? as value");
        $stmt->execute([42]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertEquals(42, $result['value'], 'Prepared statement should bind parameters correctly');
    }

    /**
     * Test database has required tables
     */
    public function testRequiredTablesExist()
    {
        $connection = $this->db->getConnection();
        $requiredTables = [
            'users',
            'freight_offers',
            'vehicle_offers',
            'messages',
            'notifications',
            'saved_searches',
            'favorites'
        ];

        foreach ($requiredTables as $table) {
            $stmt = $connection->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            $result = $stmt->fetch();

            $this->assertNotFalse($result, "Table '$table' should exist in database");
        }
    }

    /**
     * Test transaction rollback
     */
    public function testTransactionRollback()
    {
        $connection = $this->db->getConnection();

        try {
            $connection->beginTransaction();

            // Create a test operation that will be rolled back
            $stmt = $connection->prepare("INSERT INTO users (email, password, user_type, created_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute(['test@rollback.com', 'hashedpass', 'shipper']);

            // Rollback
            $connection->rollBack();

            // Verify rollback worked
            $stmt = $connection->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute(['test@rollback.com']);
            $result = $stmt->fetch();

            $this->assertFalse($result, 'Rolled back transaction should not persist data');

        } catch (Exception $e) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }
            $this->fail('Transaction test failed: ' . $e->getMessage());
        }
    }
}
