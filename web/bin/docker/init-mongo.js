// Initialization script for MongoDB used by docker-compose
// Creates a dedicated test database and a user with readWrite on that DB.

// Adjust DB name and credentials as needed for tests.
// IdaeConnect prefixes codeAppscheme_base with MDB_PREFIX (`maw_` locally).
// Keep the integration database aligned with that runtime convention.
const TEST_DB = 'maw_idae_test';
const TEST_DB_CODE = 'idae_test';
const SITEBASE_APP_DB = 'maw_sitebase_app';
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

// Create the products collection used by integration tests.
try {
    const products = [
        { idproducts: 1, nameproducts: 'Prod A', status: 'active' },
        { idproducts: 2, nameproducts: 'Prod B', status: 'inactive' }
    ];
    products.forEach(product => {
        db.products.updateOne(
            { idproducts: product.idproducts },
            { $setOnInsert: product },
            { upsert: true }
        );
    });
    print('Ensured sample products exist in', TEST_DB + '.products');
} catch (e) {
    print('Error inserting sample docs (may already exist):', e);
}

// Register the test collection in the metadata database used by IdaeConnect.
db = db.getSiblingDB(SITEBASE_APP_DB);
try {
    db.appscheme.updateOne(
        { codeAppscheme: 'products' },
        {
            $setOnInsert: {
                codeAppscheme: 'products',
                codeAppscheme_base: TEST_DB_CODE
            }
        },
        { upsert: true }
    );
    print('Ensured products metadata exists in', SITEBASE_APP_DB + '.appscheme');
} catch (e) {
    print('Error creating products metadata:', e);
}
