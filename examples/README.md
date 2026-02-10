Examples for idae.api

This folder contains ready-to-run curl examples for the IDQL endpoints.

Files:

- `curl_examples.sh` — Bash script with three examples:
  - Simple `find` on `products`.
  - `parallel` example combining a `find` and a `distinct`.
  - `group` (aggregation) example on `orders`.

Requirements:

- `curl` and `jq` installed (jq used for pretty JSON output).
- API reachable at `http://localhost:8081` (adjust `BASE_URL` in the script if needed).

Run:

```bash
bash examples/curl_examples.sh
```

If you want Windows `.bat` versions or Postman collection, tell me and I will add them.

Postman collection

You can import `examples/postman_collection.json` into Postman (File → Import) — it contains the same three IDQL examples and a `base_url` variable.