# PRD – idae.api.lan

## Overview
Idae.api.lan is a PHP-based REST API exposing data access endpoints backed by MongoDB. Core entrypoints include web/index.php and a JSON query interface /api/idql/[scheme]; Node helpers under web/bin/node support sockets/cron tasks. This PRD defines scope, goals and success metrics for the next planning phase.

## Goals & Success Metrics
| Goal | Metric | Target |
|---|---:|---:|
| Provide stable, documented REST API | API availability | 99.0% |
| Support idql query interface for complex queries | Idql response time (median) | <200ms |
| Deliver automated tests and CI | Unit/integration test coverage | >=80% |
| Containerized local dev & reproducible environment | Docker-compose up success | 100% in CI |

## User Personas
### Developer Integrator
- Role: Internal backend/frontend developers
- Needs: Reliable API contract, examples, SDKs
- Pain points: Ambiguous schema, inconsistent pagination

### Data Analyst
- Role: Analysts consuming idql
- Needs: Flexible query language, predictable semantics
- Pain points: Large resultsets, unclear performance characteristics

### Admin / Ops
- Role: Run & monitor service
- Needs: Observability, easy deployment
- Pain points: Missing metrics, unclear scaling guidance

## Core Use Cases
### UC-01 – Query data (idql)
Actor: Data Analyst / Integrator
Trigger: Client sends POST /api/idql/{scheme} with a JSON idql payload
Flow: API validates payload → executes Idql query against MongoDB → returns JSON envelope (raw or raw_casted)
Expected outcome: Correct, paginated resultset within SLA
Edge cases: Large resultsets, malformed queries

### UC-02 – REST CRUD for resources
Actor: Developer Integrator
Trigger: Client uses /api/{resource} endpoints (GET, POST, PUT, DELETE)
Flow: Router dispatches to IdaeApiRest -> IdaeQuery -> MongoDB
Expected outcome: CRUD operations succeed with proper validation, errors surfaced

### UC-03 – Background jobs & sockets
Actor: Admin / Backend
Trigger: System triggers job or socket event via Node helpers
Flow: Node helper processes job or emits socket notification
Expected outcome: Jobs execute reliably and failures are logged

## Functional Requirements
| ID | Requirement | Priority | Notes |
|---|---|---:|---|
| FR-01 | Expose POST /api/idql/{scheme} with idql payload support | Must | Support 'find', 'distinct', 'group', 'parallel' behaviors |
| FR-02 | Implement generic REST endpoints under /api/* | Must | Use existing ClassRouter dispatch |
| FR-03 | Provide JSON response modes: raw, raw_casted, html | Should | Follow IdaeApiRest semantics |

## Non-Functional Requirements
| Category | Requirement | Acceptance Criteria |
|---|---|---|
| Performance | Typical idql queries respond within 200ms median | Benchmarks in CI |
| Security | Input validation + basic auth for sensitive endpoints | Threat model documented |
| Observability | Expose basic metrics and structured logs | Logs + metrics sent to dev env |

## Out of Scope
- Frontend UI components and SDKs
- Multi-tenant isolation
- Third-party authentication providers (OAuth) for now

## Dependencies
- PHP runtime and composer dependencies (web/bin/composer.json)
- MongoDB and mongodb/mongodb PHP driver
- Node for sockets/cron helpers (web/bin/node)

## Open Questions
- Which authentication mechanism is required (API key, JWT, OAuth)?
- Are there data retention or compliance constraints?
- Indexing strategy for large collections?

## Revision History
| Date | Author | Change |
|---|---|---|
| 2026-03-07 | BMAD PM Agent | Initial draft |

