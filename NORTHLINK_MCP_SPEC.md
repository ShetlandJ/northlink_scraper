# NorthLink Ferries MCP Server - Full Specification

> This document contains everything needed to build an MCP (Model Context Protocol) server
> that scrapes and serves NorthLink Ferries booking data. Feed this to Claude Code to build
> the project from scratch.

---

## 1. What This Is

NorthLink Ferries operates ferry services between mainland Scotland and the Northern Isles
(Shetland and Orkney). Their website has an undocumented JSON API that returns sailing
availability, pricing, cabin accommodation, and vehicle capacity data.

This spec describes how to build an **MCP server** that:
1. **Scrapes** the NorthLink API and stores the data locally (SQLite)
2. **Exposes MCP tools** so Claude (or any MCP client) can query ferry availability,
   pricing, cabin types, car spaces, and pet cabin availability in natural language

---

## 2. NorthLink API Details

**Base URL:** `https://www.northlinkferries.co.uk/api/`

All authenticated requests require a Bearer token in the `Authorization` header.

### 2.1 Token Generation

The API uses a session token system. You must create a fake booking to get a token.

**Endpoint:** `POST https://www.northlinkferries.co.uk/api/booking/create`

**Payload (1 passenger, no vehicle):**
```json
{
  "departureRequests": [
    {
      "route": "LEAB",
      "date": "2026-04-10",
      "resources": [
        {
          "resourceCode": "PAX",
          "amount": "1",
          "resourceType": "A",
          "type": "STD"
        }
      ],
      "vehicles": []
    },
    {
      "route": "ABLE",
      "date": "2026-04-15",
      "resources": [
        {
          "resourceCode": "PAX",
          "amount": "1",
          "resourceType": "A",
          "type": "STD"
        }
      ],
      "vehicles": []
    }
  ]
}
```

**Important notes:**
- The payload always requires BOTH an outbound and return leg (even if you only care about one direction)
- Dates should be valid future dates (e.g. tomorrow and +5 days)
- The `route` field uses 4-character route codes (see section 3)
- Response contains a `token` field - store and reuse this for subsequent requests

**Payload (1 passenger WITH vehicle):**
```json
{
  "departureRequests": [
    {
      "route": "LEAB",
      "date": "2026-04-10",
      "resources": [
        {
          "resourceCode": "PAX",
          "amount": "1",
          "resourceType": "A",
          "type": "STD"
        }
      ],
      "vehicles": [
        {
          "resourceCode": "CAR",
          "length": "",
          "licenseNumber": "",
          "rental": true
        }
      ]
    },
    {
      "route": "ABLE",
      "date": "2026-04-15",
      "resources": [
        {
          "resourceCode": "PAX",
          "amount": "1",
          "resourceType": "A",
          "type": "STD"
        }
      ],
      "vehicles": [
        {
          "resourceCode": "CAR",
          "length": "",
          "licenseNumber": "",
          "rental": true
        }
      ]
    }
  ]
}
```

### 2.2 Get Available Dates

Returns all dates that have sailings for a given route.

**Endpoint:** `GET https://www.northlinkferries.co.uk/api/dates/{routeCode}`

No authentication required.

**Response:**
```json
{
  "dates": ["2026-04-10", "2026-04-11", "2026-04-12", ...]
}
```

### 2.3 Get Departure Prices & Availability

Returns pricing and availability data for a specific date and route.

**Endpoint:** `GET https://www.northlinkferries.co.uk/api/departures/{routeCode}/prices/{date}`

**Headers:** `Authorization: {token}`

**Response structure:**
```json
{
  "res": {
    "result": [
      {
        "price": 42.50,
        "bookable": true,
        "noAccommodationsAvailable": false,
        "noVehicleCapacity": false,
        "noPassengerCapacity": false,
        "supplier": "LEAB",
        "startDate": "2026-04-10",
        "hashId": "abc123",
        "identifier": "dep-identifier-string",
        "prices": [
          {
            "resourceCode": "PAX",
            "price": 42.50,
            "ticketType": "STD",
            "type": "STD",
            "available": true,
            "yieldClass": "Y1",
            "capacity": 150,
            "intervalValue": 0,
            "resourceType": 1
          },
          {
            "resourceCode": "NI4",
            "price": 85.00,
            "ticketType": null,
            "type": null,
            "available": true,
            "yieldClass": null,
            "capacity": 12,
            "intervalValue": null,
            "resourceType": null
          },
          {
            "resourceCode": "CAR",
            "price": 95.00,
            "ticketType": "STD",
            "type": "STD",
            "available": true,
            "yieldClass": "Y1",
            "capacity": 80,
            "intervalValue": 0,
            "resourceType": 1
          }
        ]
      }
    ]
  }
}
```

The `prices` array contains entries for every resource type on that sailing: passengers (PAX),
each cabin type, vehicles (CAR with different ticketTypes), etc.

### 2.4 Get Cabin Accommodation Details

Returns detailed cabin/room information. Requires a more complex flow:

1. First, generate a fresh token (as above)
2. "Mock depart" - simulate selecting departures
3. Then fetch accommodation

**Step 2 - Mock Depart:**
`POST https://www.northlinkferries.co.uk/api/book/departures`

```
Headers: Authorization: {token}
Body (form_params):
  identifiers[0] = {outbound_trip_identifier}
  identifiers[1] = {return_trip_identifier}
```

The `identifier` values come from the departure prices response (section 2.3). You need
identifiers for both an outbound and return trip.

**Step 3 - Fetch Accommodation:**
`POST https://www.northlinkferries.co.uk/api/accommodations/{routeCode}`

```
Headers: Authorization: {token}
```

**Response structure:**
```json
{
  "res": {
    "result": {
      "cabins": [
        {
          "amount": 1,
          "bnBIncluded": false,
          "capacity": 12,
          "description": "Inside 4 berth cabin (bunk beds)",
          "extrasIncluded": false,
          "hasSeaView": false,
          "identifier": "cabin-id-string",
          "isAccessibleCabin": false,
          "price": 85,
          "resourceCode": "NI4",
          "sleeps": 4
        }
      ]
    }
  }
}
```

---

## 3. Routes

NorthLink route codes are 4 characters: first 2 = departure port, last 2 = arrival port.

| Code | Route | Port Codes |
|------|-------|------------|
| `LEAB` | Lerwick to Aberdeen | LE=Lerwick, AB=Aberdeen |
| `ABLE` | Aberdeen to Lerwick | |
| `ABKI` | Aberdeen to Kirkwall | KI=Kirkwall |
| `KIAB` | Kirkwall to Aberdeen | |
| `KILE` | Kirkwall to Lerwick | |
| `LEKI` | Lerwick to Kirkwall | |
| `SCST` | Scrabster to Stromness | SC=Scrabster, ST=Stromness |
| `STSC` | Stromness to Scrabster | |

**Active routes** (scrape these): `LEAB`, `ABLE`, `ABKI`, `KIAB`, `KILE`, `LEKI`

**Disabled routes** (Pentland Firth - different service): `SCST`, `STSC`

**Route pairs** (every outbound has a return):
- `LEAB` <-> `ABLE`
- `ABKI` <-> `KIAB`
- `KILE` <-> `LEKI`
- `SCST` <-> `STSC`

When generating tokens, you always need both an outbound and return route.

---

## 4. Cabin / Accommodation Types

These are the `resourceCode` values for accommodation:

| Code | Description | Notes |
|------|-------------|-------|
| `NPREM` | Premium Outside 2 berth cabin | Top tier, sea view |
| `NEXEC` | Executive Outside 2 berth cabin (bunk beds) | High tier, sea view |
| `NO2` | Outside 2 berth cabin | Sea view |
| `NO3` | Outside 3 berth cabin (bunk beds) | Sea view |
| `NI2` | Inside 2 berth cabin (bunk beds) | No window |
| `NI4` | Inside 4 berth cabin (bunk beds) | No window, budget option |
| `NI4S` | Inside 4 berth cabin with Privacy Curtains (bunk beds) | No window, curtains |
| `NPETPR` | Premium Outside 2 berth Pet-friendly cabin | Pet-friendly, sea view |
| `NPETI2` | Inside 2 berth Pet-friendly cabin | Pet-friendly |
| `NPETO2` | Outside 2 berth Pet-friendly cabin | Pet-friendly, sea view |
| `NPETI4` | Inside 4 berth Pet-friendly cabin (bunk beds) | Pet-friendly |
| `POD` | Pod in Pod Lounge 1 | Budget sleeping pod |
| `POD2` | Pod in Pod Lounge 2 | Budget sleeping pod |
| `POD3` | Pod in Pod Lounge 3 | Budget sleeping pod |
| `NRSEAT` | Reclining seat | Cheapest option |

**Pet-friendly cabins** (prefix `NPET`): `NPETPR`, `NPETI2`, `NPETO2`, `NPETI4`

---

## 5. Vehicle Types

Vehicles use `resourceCode: "CAR"` with different `ticketType` values:

| ticketType | Description |
|------------|-------------|
| `STD` | Standard car ticket |
| `BST` | Booked standard |
| `DD0` | Deck/discount type |
| `VCN` | Vehicle cancellation/other |

The main one to track for availability is `STD`.

---

## 6. Other Resource Codes

| resourceCode | Description |
|-------------|-------------|
| `PAX` | Passenger (adult) |
| `CAR` | Vehicle |
| All cabin codes above | Cabin/accommodation |

---

## 7. Data Model for Storage

Use SQLite for local storage. Here's the schema:

### trips
```sql
CREATE TABLE trips (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date TEXT NOT NULL,              -- YYYY-MM-DD
    price REAL,                      -- base passenger price
    bookable INTEGER DEFAULT 1,      -- boolean
    no_accommodations_available INTEGER DEFAULT 0,
    no_vehicle_capacity INTEGER DEFAULT 0,
    no_passenger_capacity INTEGER DEFAULT 0,
    route_code TEXT NOT NULL,        -- e.g. 'LEAB'
    depart_from TEXT,                -- e.g. 'Lerwick'
    return_from TEXT,                -- e.g. 'Aberdeen' (destination)
    start_date TEXT,                 -- sailing date from API
    hash_id TEXT,                    -- API hash identifier
    identifier TEXT,                 -- API departure identifier (needed for accommodation fetching)
    created_at TEXT,
    updated_at TEXT,
    UNIQUE(date, route_code)
);
```

### trip_prices
```sql
CREATE TABLE trip_prices (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    trip_id INTEGER NOT NULL,
    resource_code TEXT NOT NULL,      -- 'PAX', 'CAR', 'NI4', etc.
    price REAL,
    ticket_type TEXT,                -- 'STD', 'BST', etc. (for CAR)
    type TEXT,
    available INTEGER DEFAULT 1,     -- boolean
    yield_class TEXT,
    capacity INTEGER,                -- remaining spaces
    interval_value INTEGER,
    resource_type INTEGER,
    created_at TEXT,
    updated_at TEXT,
    FOREIGN KEY (trip_id) REFERENCES trips(id)
);
```

### trip_accommodations
```sql
CREATE TABLE trip_accommodations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    trip_id INTEGER NOT NULL,
    amount INTEGER,
    bnb_included INTEGER DEFAULT 0,  -- boolean
    capacity INTEGER,                -- rooms remaining
    description TEXT,                -- human-readable cabin description
    extras_included INTEGER DEFAULT 0,
    has_sea_view INTEGER DEFAULT 0,
    identifier TEXT,                 -- cabin identifier from API
    is_accessible_cabin INTEGER DEFAULT 0,
    price REAL,
    resource_code TEXT,              -- cabin type code
    sleeps INTEGER,                  -- how many people it sleeps
    created_at TEXT,
    updated_at TEXT,
    FOREIGN KEY (trip_id) REFERENCES trips(id)
);
```

---

## 8. Scraping Strategy

The scraper should run in 3 phases per route (matching the original `scrape:all` command):

### Phase 1: Passenger & Pricing Data
1. Generate a token with 1-passenger payload (no vehicle)
2. Fetch available dates for the route via `/api/dates/{routeCode}`
3. For each date, fetch `/api/departures/{routeCode}/prices/{date}`
4. Upsert `trips` record and all `trip_prices` records from the prices array

### Phase 2: Vehicle Availability
1. Generate a token with 1-passenger + CAR vehicle payload
2. Fetch available dates
3. For each date, fetch departure prices (same endpoint as Phase 1 but with vehicle token)
4. Filter prices for `resourceCode: "CAR"` with `ticketType` in `['BST', 'STD', 'DD0', 'VCN']`
5. Update `trip_prices` records for vehicles (available, capacity fields)

### Phase 3: Accommodation Details
1. Generate a fresh token (1 passenger, no vehicle)
2. Fetch available dates for both outbound AND return routes
3. For each date:
   a. Look up the trip record to get its `identifier`
   b. Find a valid return trip (next available date) and get its `identifier`
   c. Mock depart with both identifiers (`POST /api/book/departures`)
   d. Fetch accommodation (`POST /api/accommodations/{routeCode}`)
   e. Delete existing accommodation records for this trip
   f. Insert new accommodation records from the cabins array

### Rate Limiting & Error Handling
- The API doesn't appear to rate limit aggressively, but be respectful
- Add a small delay between requests (200-500ms)
- If a request fails, log it and continue to the next date (don't abort the whole run)
- Wrap individual date processing in try/catch
- Token generation creates a fresh session, so generate a new token before each phase

### Scheduling
- Run the full scrape (all 3 phases) for each active route periodically
- Active routes to scrape: `LEAB`, `ABLE`, `ABKI`, `KIAB`, `KILE`, `LEKI`
- A full scrape of all routes takes several minutes due to per-date API calls

---

## 9. MCP Tools to Expose

Build these as MCP tools that Claude can call:

### 9.1 `search_sailings`
Search for available sailings between ports on a date or date range.

**Parameters:**
- `from` (string, required): Departure port - "Lerwick", "Aberdeen", or "Kirkwall"
- `to` (string, required): Arrival port - "Lerwick", "Aberdeen", or "Kirkwall"
- `date` (string, optional): Specific date (YYYY-MM-DD)
- `date_from` (string, optional): Start of date range
- `date_to` (string, optional): End of date range
- `month` (integer, optional): Month number (1-12) - gets all sailings in that month
- `year` (integer, optional): Year (defaults to current year)

**Returns:** List of sailings with date, base price, bookable status, and capacity flags.

### 9.2 `check_cabin_availability`
Check availability and pricing for a specific cabin type.

**Parameters:**
- `from` (string, required): Departure port
- `to` (string, required): Arrival port
- `cabin_type` (string, required): Cabin code (e.g. "NI4", "NPREM", "NO2") or descriptive name
- `date` (string, optional): Specific date
- `date_from` / `date_to` (string, optional): Date range
- `month` / `year` (integer, optional): Month-based query

**Returns:** Per-date cabin availability with price, capacity (rooms remaining), description,
sea view status, sleeps count.

### 9.3 `check_car_availability`
Check vehicle space availability.

**Parameters:**
- `from` (string, required): Departure port
- `to` (string, required): Arrival port
- `date` (string, optional): Specific date
- `date_from` / `date_to` (string, optional): Date range
- `month` / `year` (integer, optional): Month-based query

**Returns:** Per-date vehicle availability with capacity count and traffic-light status
(green = plenty of space >50, yellow = limited <=50, red = unavailable).

### 9.4 `check_pet_cabin_availability`
Check pet-friendly cabin availability.

**Parameters:**
- `from` (string, required): Departure port
- `to` (string, required): Arrival port
- `date` (string, optional): Specific date
- `date_from` / `date_to` (string, optional): Date range
- `month` / `year` (integer, optional): Month-based query

**Returns:** Per-date pet cabin availability (checks `NPETI4`, `NPETO2`, `NPETI2`, `NPETPR`
cabin types) with price and capacity.

### 9.5 `get_cheapest_sailings`
Find the cheapest sailings for a route within a date range.

**Parameters:**
- `from` (string, required): Departure port
- `to` (string, required): Arrival port
- `date_from` (string, optional): Start date (defaults to tomorrow)
- `date_to` (string, optional): End date (defaults to 3 months out)
- `cabin_type` (string, optional): Specific cabin type to price
- `include_car` (boolean, optional): Include car pricing
- `limit` (integer, optional): Number of results (default 10)

**Returns:** Sailings sorted by price (ascending), with breakdown of passenger + cabin + car costs.

### 9.6 `find_return_trips`
Find available return trip options given an outbound sailing.

**Parameters:**
- `from` (string, required): Where the outbound departs FROM (this becomes the return destination)
- `to` (string, required): Where the outbound goes TO (this becomes the return departure)
- `outbound_date` (string, required): The outbound travel date
- `min_stay_days` (integer, optional): Minimum days between outbound and return (default 1)
- `max_stay_days` (integer, optional): Maximum days gap (default 14)
- `cabin_type` (string, optional): Desired cabin type
- `need_car` (boolean, optional): Whether vehicle space is needed

**Returns:** Available return sailings with pricing and availability.

### 9.7 `scrape_route`
Trigger a fresh scrape of a specific route (or all routes).

**Parameters:**
- `route_code` (string, optional): Specific route code (e.g. "LEAB") or "all"

**Returns:** Status of the scrape operation.

### 9.8 `get_route_info`
Get information about available routes, ports, and cabin types.

**Parameters:**
- `info_type` (string, optional): "routes", "cabins", "ports", or "all"

**Returns:** Reference data about routes, cabin type descriptions, and port names.

### 9.9 `get_data_freshness`
Check when data was last scraped.

**Parameters:** None (or optional `route_code`)

**Returns:** Last scrape time per route, number of records, date range covered.

---

## 10. Tech Stack Recommendation

Build this as a **TypeScript/Node.js** or **Python** MCP server:

### Option A: TypeScript (recommended for MCP ecosystem)
- `@modelcontextprotocol/sdk` for the MCP server
- `better-sqlite3` for SQLite storage
- `node-fetch` or `axios` for HTTP requests
- `node-cron` for scheduling

### Option B: Python
- `mcp` Python SDK for the MCP server
- `sqlite3` (built-in) for storage
- `httpx` or `requests` for HTTP
- `schedule` or `apscheduler` for scheduling

### Project structure (TypeScript example):
```
northlink-mcp/
  src/
    index.ts              # MCP server entry point
    scraper/
      token.ts            # Token generation & management
      departures.ts       # Departure/pricing scraper
      vehicles.ts         # Vehicle availability scraper
      accommodation.ts    # Cabin accommodation scraper
      scheduler.ts        # Scheduled scraping
    tools/
      search-sailings.ts
      cabin-availability.ts
      car-availability.ts
      pet-cabins.ts
      cheapest.ts
      return-trips.ts
      scrape.ts
      info.ts
    db/
      schema.ts           # SQLite schema setup
      queries.ts          # Reusable query helpers
    utils/
      routes.ts           # Route code mappings & helpers
      types.ts            # TypeScript type definitions
  package.json
  tsconfig.json
  README.md
```

---

## 11. Port Name Resolution

Map user-friendly port names to route codes:

```
Lerwick  -> LE
Aberdeen -> AB
Kirkwall -> KI
Scrabster -> SC
Stromness -> ST
```

The MCP tools should accept human-readable port names ("Lerwick", "Aberdeen", "Kirkwall")
and resolve them to route codes internally. Also accept common variations:
- "Shetland" -> Lerwick
- "Orkney" -> Kirkwall
- "ABZ" / "ABD" -> Aberdeen

---

## 12. Important Behavioural Notes

1. **Token lifecycle**: Tokens are session-based. Generate a fresh one before each scrape phase.
   Don't try to reuse tokens across long time gaps.

2. **The accommodation flow is fiddly**: You MUST do the mock depart step before fetching
   accommodation. Without it, the accommodation endpoint returns nothing or errors.

3. **Identifiers matter**: The `identifier` field from departure data is essential for the
   accommodation flow. Always store it when scraping departures.

4. **Dates from the API**: Use `/api/dates/{routeCode}` to get valid sailing dates rather
   than generating date ranges yourself. Not every day has a sailing.

5. **Prices are in GBP (pounds sterling)** and appear to be stored as integers representing
   pence in some contexts, but the API returns them as floats. Be consistent - store as
   float or convert to pence integers.

6. **Capacity = 0 means sold out** for that resource type. The `available` boolean flag
   is the primary indicator, but check capacity too.

7. **The car scraper needs a CAR in the token payload**: When generating a token for vehicle
   scraping, include a vehicle in the departure request. Otherwise vehicle pricing/capacity
   data may not be returned accurately.

8. **Pet cabins are a subset of accommodations**: They're just regular cabins with resource
   codes starting with `NPET`. The accommodation endpoint returns them alongside other cabins.

---

## 13. Example User Queries the MCP Should Handle

These are the kinds of natural language questions people will ask Claude with this MCP:

- "Are there any ferries from Lerwick to Aberdeen next Friday?"
- "What's the cheapest cabin from Aberdeen to Shetland in July?"
- "Is there car space on the Lerwick to Aberdeen ferry on the 15th?"
- "Can I get a pet cabin from Aberdeen to Lerwick this month?"
- "Find me a return trip: Lerwick to Aberdeen on the 10th, coming back within a week"
- "What cabins are available on tomorrow's sailing from Kirkwall?"
- "Show me all sold-out dates for the Aberdeen to Lerwick route in August"
- "What's the price difference between an inside 4-berth and a premium cabin?"
- "Are there any last-minute deals for this week?"
- "When's the next sailing with car space from Lerwick?"

---

## 14. MCP Server Configuration

The server should be configurable via environment variables or a config file:

```
NORTHLINK_DB_PATH=./northlink.db        # SQLite database path
SCRAPE_INTERVAL_MINUTES=60               # How often to auto-scrape
SCRAPE_ON_STARTUP=true                   # Whether to scrape when server starts
REQUEST_DELAY_MS=300                     # Delay between API requests
LOG_LEVEL=info                           # Logging level
```

---

## 15. Quick Start for Claude Code

When you receive this document, here's the order of operations:

1. **Scaffold the project** - Create the directory structure, package.json/pyproject.toml, config
2. **Set up the database** - Create SQLite schema with the tables from section 7
3. **Build the scraper** - Implement token generation, then the 3 scraping phases
4. **Test the scraper** - Run it against one route to verify data flows correctly
5. **Build the MCP tools** - Implement each tool from section 9
6. **Wire up the MCP server** - Register all tools with the MCP SDK
7. **Add scheduling** - Periodic background scraping
8. **Test end-to-end** - Verify tools return correct data from the database
