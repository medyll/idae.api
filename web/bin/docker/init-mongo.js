// Initialization script for MongoDB used by docker-compose
// Creates a dedicated test database and a user with readWrite on that DB.

// Adjust DB name and credentials as needed for tests.
const TEST_DB = 'idae_test';
const TEST_USER = 'idae_test_user';
const TEST_PWD = 'idae_test_pwd';

print('Creating test database and user:', TEST_DB, TEST_USER);

db = db.getSiblingDB(TEST_DB);
try {
    db.createUser({
        user: TEST_USER,
        pwd: TEST_PWD,
        roles: [{ role: 'readWrite', db: TEST_DB }]
    });
    print('Created user', TEST_USER, 'on', TEST_DB);
} catch (e) {
    print('Error creating test user (may already exist):', e);
}

// Optionally create a products collection used by integration tests
try {
    db.products.insertMany([
        { idproducts: 1, nameproducts: 'Prod A', status: 'active' },
        { idproducts: 2, nameproducts: 'Prod B', status: 'inactive' }
    ]);
    print('Inserted sample products into', TEST_DB + '.products');
} catch (e) {
    print('Error inserting sample docs (may already exist):', e);
}
