story: S1-01
objective: Verify products find endpoint works
steps:
  - Perform GET request to /api/products?query=test
  - Expect 200 response with JSON array
  - Validate input sanitation
  - Run PHP unit tests for query builder
expected_results:
  - JSON output matches sample
  - No SQL injection
