# Diamond Shine — PMS Gap Analysis

**Date:** 2026-08-15
**Scope:** the whole first-party codebase (~10,800 lines), measured against how a real multi-property hotel PMS operates.
**Audience assumption:** this is a **portfolio piece**. Ratings weight *"a technical reviewer would notice this is missing"* over *"a real hotel would be fined for this."* That deliberately pushes some genuinely serious operational gaps (tax handling, data retention) down the list and pulls some visible ones (housekeeping, rate plans) up.

## How to read the ratings

| | Meaning |
|---|---|
| **P0** | A reviewer opening this project would immediately register the absence. It breaks the claim that this is a hotel system rather than a booking CRUD app. |
| **P1** | Noticeably missing to anyone who knows the domain. Adds real credibility but the system reads as coherent without it. |
| **P2** | Depth. Worth doing if the goal is to show range, not worth doing first. |

Size estimates are rough implementation effort in this codebase's existing style (fat controllers, Blade, no service layer unless warranted).

---

## Summary table

| # | Capability | State | Rating | Size |
|---|---|---|---|---|
| 1 | Rates & inventory | Not at all | **P0** | L |
| 2 | Housekeeping | Not at all | **P0** | M |
| 3 | Reservation lifecycle & audit | Partial (broken) | **P0** | M |
| 4 | Front office operational views | ✅ built (room move deferred) | ~~P0~~ | S–M |
| 5 | Billing & folio | Partial | **P1** | M–L |
| 6 | Reporting & revenue (ADR/RevPAR) | Partial | **P1** | S–M |
| 7 | Guest profile | Not at all | **P1** | M |
| 8 | Policies (cancellation/deposit) | Partial | **P1** | M |
| 9 | Night audit | Partial (misleading) | **P1** | M |
| 10 | Payments | Not at all | **P1** | M |
| 11 | Availability & overbooking | Partial (good core) | **P2** | S |
| 12 | Group / corporate / agency | Partial | **P2** | M |
| 13 | Multi-branch depth | Partial | **P2** | M |
| 14 | Communications | Partial | **P2** | S |
| 15 | Code health & tests | Tests ✅ / duplication outstanding | **P1** | M |

---

## 1. Rates & inventory — **P0**, size L

**Why a chain needs it.** Rate management *is* the commercial core of a hotel. A property that cannot charge more on a Saturday in August than a Tuesday in February is not running a hotel business. Real systems model rate plans (BAR, corporate, advance-purchase, non-refundable), date-ranged seasonal rates, day-of-week differentials, and length-of-stay restrictions.

**Current state — not at all.** Pricing is a single scalar per room type:

- `database/migrations/2025_05_23_000003_create_room_types_table.php` — `price_per_night`, `weekly_rate`, `monthly_rate` are three flat columns on `room_types`. There is no date dimension anywhere in the pricing model.
- `app/Http/Controllers/ClerkController.php` — `calculateTotal()` multiplies nights by that one column.
- `database/seeders/` seeds exactly three room types at 50 / 80 / 1000-per-week.

Every stay of the same room type costs the same amount forever, in every branch.

**What it would take.**

```
rate_plans        id, branch_id, room_type_id, name, code,
                  is_refundable, min_nights, max_nights, is_active
rates             id, rate_plan_id, starts_on, ends_on,
                  dow_mask (bitmask Mon–Sun), amount, currency
```

Then a `App\Services\RateResolver` — this genuinely warrants a service, the same way `RoomAvailability` does — exposing `resolve($branchId, $roomTypeId, $checkIn, $checkOut): RateBreakdown`, returning a per-night breakdown plus a total. The four duplicated pricing call sites collapse onto it.

**Note:** this is the single highest-leverage item in the document. It also unlocks #6 (ADR/RevPAR become meaningful only once rates vary) and #8 (non-refundable rates are a policy attached to a rate plan).

---

## 2. Housekeeping — **P0**, size M

**Why a chain needs it.** Housekeeping is one of the four modules every PMS ships with. A room that a guest has checked out of is *dirty*, not *available* — it cannot be re-sold until it has been cleaned and inspected. Without this distinction the front desk can assign a filthy room to an arriving guest.

**Current state — entirely absent.** `rooms.status` is a three-value enum:

- `database/migrations/2025_05_23_000004_create_rooms_table.php:16` — `enum('status', ['available','occupied','maintenance'])`.

There is no dirty state, no inspection step, no attendant assignment, no work orders. `ClerkController::storeCheckIn()` flips a room to `occupied`; check-out flips it back to `available` with nothing in between.

**What it would take.**

```
-- widen the enum
rooms.housekeeping_status  enum(clean, dirty, inspected, out_of_order) default 'clean'
rooms.last_cleaned_at      timestamp null

housekeeping_tasks  id, room_id, branch_id, assigned_to (users.id),
                    task_type (departure|stayover|deep_clean|turndown),
                    status (pending|in_progress|done|verified),
                    scheduled_for, completed_at, notes

maintenance_orders  id, room_id, branch_id, reported_by, priority,
                    description, status, resolved_at
```

Plus a `HousekeepingController` with an attendant-facing task list, a supervisor inspection screen, and a rule in `RoomAvailability::capacity()` that excludes `out_of_order`. Check-out should automatically create a departure-clean task.

**Why P0 for a portfolio:** it is the most conspicuous "this person has actually thought about how a hotel runs" signal available, and it is self-contained enough to build cleanly.

---

## 3. Reservation lifecycle & audit — **P0**, size M

**Why a chain needs it.** Reservations are money. Every status change needs to be attributable — who confirmed it, who cancelled it, who moved the dates, and when.

**Current state — partial and visibly broken.**

- `database/migrations/2025_05_23_000005_create_reservations_table.php:20` declares six states, but **`confirmed` is never written by any code path.** Everything is created `pending` and jumps straight to `checked_in`. `confirmed` is only ever read (e.g. `App\Services\RoomAvailability::ACTIVE_STATUSES`, `CancelNoCreditReservations`).
- There is **no audit trail at all**. `reservations` carries only `created_at`/`updated_at`. A cancelled reservation cannot tell you who cancelled it.
- `reservations.is_suite` (line 21) is **never written** — it defaults to `0` and suite-ness is determined by joining to `room_types.is_suite`. A dead column that actively misleads.

**What it would take.**

```
reservation_events  id, reservation_id, user_id, from_status, to_status,
                    changed_fields (json), note, created_at
```

Write to it from a model observer (`App\Observers\ReservationObserver`) so no call site can forget. Add a confirm action that moves `pending → confirmed` — the natural trigger is "a guarantee is on file", which now exists as `App\Support\CardGuarantee`. Drop `is_suite` in a migration.

**This is cheap and high-signal.** An audit trail is the thing that most obviously separates a real system from a CRUD demo, and the observer approach is idiomatic Laravel that a reviewer will recognise.

---

## 4. Front office operational views — ~~**P0**~~ **mostly built**, size S–M

**Why a chain needs it.** The three lists a front desk lives on are *arrivals today*, *departures today*, and *in-house*. They are the first thing anyone with hotel experience looks for.

**Built (2026-08-15).** `ClerkController::frontDesk()` → `clerk/front-desk`, linked from the clerk dashboard:

- **Arrivals / Departures / In-house** as three scoped, branch-filtered tabs with counts, plus a stat row (arrivals, departures, in-house, rooms free) and a room census.
- **Overdue departures** — a derived state, not a status: guests still checked in past their check-out date. Surfaced as a banner and highlighted rows, because their rooms are being counted as occupied. This immediately found two real stale rows in the development database.
- **Guarantee column on arrivals**, showing which same-day arrivals the 19:00 job will auto-cancel.
- **Walk-in** (`clerk/walk-in`) — books and checks in a guest in one transaction: creates the reservation already `checked_in`, assigns the physical room, marks it occupied and opens the folio. Suites are excluded (priced by duration, not a walk-in product). Validates the occupancy cap, that the room belongs to the branch and is free, and — importantly — still calls `RoomAvailability::hasCapacityFor()`, because a room being free *today* says nothing about reservations already booked across the requested stay.
- A small JSON endpoint (`clerk/room-types/{roomType}/available-rooms`) drives the room picker.

Covered by `tests/Feature/FrontDeskTest.php` (11 tests). `DatabaseSeeder` now seeds a front-desk day so a fresh `migrate:fresh --seed` shows populated tabs.

**Still outstanding in this area:**

- **Room move** — deferred deliberately. Moving a guest between rooms without an audit trail loses information, so this should follow #3 (`reservation_events`).
- **Early check-in / late check-out** — no representation; these are fee-bearing events and belong with #8 (policies).
- **Business date** — "today" is the server date, not a per-branch business date. See #9.

---

## 5. Billing & folio — **P1**, size M–L

**Why a chain needs it.** Charges arrive continuously through a stay from many outlets, each with its own timestamp, tax treatment and posting user. Guests split folios (company pays room, guest pays extras) and dispute individual lines.

**Current state — partial, and modelled as fixed columns.**

`database/migrations/2025_05_23_000006_create_billings_table.php:20-24` hardcodes five charge buckets as columns:

```php
$table->decimal('restaurant_charges', 10, 2)->default(0.00);
$table->decimal('room_service_charges', 10, 2)->default(0.00);
$table->decimal('laundry_charges', 10, 2)->default(0.00);
$table->decimal('telephone_charges', 10, 2)->default(0.00);
$table->decimal('club_facility_charges', 10, 2)->default(0.00);
```

Consequences: a sixth revenue category requires a migration; you cannot post two restaurant charges on different days; there is no posting date, no posting user, no description; there is no tax, no service charge, no discount, no invoice number; and the whole folio is entered once at check-out (`ClerkController::storeCheckOut()`) rather than accumulated during the stay.

**What it would take.**

```
charge_types   id, code, name, is_taxable, tax_rate, revenue_category
folio_lines    id, billing_id, charge_type_id, posted_by (users.id),
               posted_at, description, quantity, unit_amount,
               tax_amount, total_amount, is_void, voided_by, voided_at
```

Keep `billings` as the folio header and add `invoice_number`, `subtotal`, `tax_total`, `service_charge`, `discount_total`, `grand_total` as derived columns. The five existing columns become seeded `charge_types` rows; a migration backfills current data into `folio_lines` so nothing is lost.

**Not P0 only because** the current model does technically capture the money. It is the largest single piece of work here.

---

## 6. Reporting & revenue metrics — **P1**, size S–M

**Why a chain needs it.** Occupancy alone is a vanity metric. The industry runs on **ADR** (average daily rate = room revenue ÷ rooms sold) and **RevPAR** (revenue per available room = room revenue ÷ rooms available), because they separate "we were full" from "we were full because we gave it away."

**Current state — partial.** `reports` stores only three figures:

- `database/migrations/2025_05_23_000009_create_reports_table.php` — `total_occupancy`, `total_revenue`, `no_show_count`.

No ADR, no RevPAR, no forecast, no booking pace, no source-of-business or market segment. There is also a real defect: `ManagerController::occupancyReport()` and `revenueReport()` **write `reports` rows as a side effect of a GET request** (`app/Http/Controllers/ManagerController.php:121`, `:183`, `:252`, `:324` — four `Report::updateOrCreate()` calls across two reports and their download twins). Refreshing a report page mutates stored history.

**What it would take.** Add `rooms_available`, `rooms_sold`, `room_revenue`, `adr`, `revpar` to `reports`; compute them in the nightly command rather than in the controller; and make the controllers read-only. Source-of-business needs a `reservations.source` enum (`direct|travel_agency|walk_in|corporate`), which is nearly free and unlocks segment reporting.

**Fixing the GET-writes defect is a one-line-per-site change** and is worth doing regardless of what else is approved.

---

## 7. Guest profile — **P1**, size M

**Why a chain needs it.** The commercial reason a chain exists is that a guest who stayed in Colombo can be recognised in Kandy. Repeat-guest recognition, preferences, stay history and loyalty tiers are the entire argument for being a chain rather than two independent hotels.

**Current state — not at all.** `users` (`database/migrations/2025_05_23_000002_create_users_table.php`) has `name`, `email`, `password`, `role`, `branch_id`, `nationality`, `contact_number`. That is it. No stay history view, no ID/passport capture (a legal requirement in most jurisdictions, Sri Lanka included), no preferences, no accompanying-guest list, no loyalty tier, no VIP or blacklist flag.

Note also that `number_of_occupants` is an integer — the system knows *how many* people are in the room but not *who*.

**What it would take.**

```
guest_profiles     id, user_id, id_document_type, id_document_number,
                   date_of_birth, address, loyalty_tier, vip_flag,
                   preferences (json), notes, is_blacklisted
reservation_guests id, reservation_id, full_name, id_document_number,
                   is_primary
```

Plus a guest-history panel on the clerk's check-in screen showing prior stays across all branches — that cross-branch query is the demo moment.

---

## 8. Policies — **P1**, size M

**Why a chain needs it.** "Free cancellation until 48h before arrival, then one night's penalty" is a product decision that changes per rate plan and per season. Hardcoding one rule means the hotel has exactly one commercial policy forever.

**Current state — partial, one hardcoded rule.** The only policy in the system is the 19:00 no-credit-card auto-cancel (`app/Console/Commands/CancelNoCreditReservations.php`), and its threshold, timing and behaviour are all constants. There are no deposits, no guarantee types beyond "card present / card absent", no cancellation windows, no no-show penalty configuration (the no-show charge is hardcoded to the full room rate in `ProcessNoShowsAndReport`), and no early-departure or late-checkout fees.

**What it would take.**

```
cancellation_policies  id, branch_id, name, free_until_hours_before,
                       penalty_type (none|first_night|percentage|full),
                       penalty_value
rate_plans.cancellation_policy_id   -- policy attaches to the rate plan
reservations.deposit_amount, deposit_paid_at
```

**Depends on #1** — policies attach to rate plans, so this is most naturally built immediately after rates.

---

## 9. Night audit — **P1**, size M

**Why a chain needs it.** The night audit is the daily close: it rolls the business date, posts room-and-tax to every in-house folio, settles no-shows, and produces the manager's figures. It is a transactional boundary, and it must be safely re-runnable — audits fail and get restarted.

**Current state — partial, and the name oversells it.** Two commands run at 18:59 and 19:00 (`app/Console/Kernel.php`):

- `CancelNoCreditReservations` — cancels same-day reservations without a guarantee.
- `ProcessNoShowsAndReport` (195 lines) — bills yesterday's unfulfilled reservations, flips them to `no_show`, computes occupancy/revenue, writes one `reports` row per branch and emails managers.

What is missing versus a real audit: there is **no business date** (the system uses `Carbon::today()`, so the hotel's day is the server's day); **no idempotency guard** — re-running mid-failure would re-bill; **no room-and-tax posting** to in-house folios, so a five-night stay accrues nothing until check-out; and **no audit record** of whether the close succeeded.

It also runs at 19:00, which is not when hotels close the day — real night audits run around 02:00–04:00. That is a one-line change in `Kernel.php` and a nice detail to get right.

**What it would take.**

```
business_dates  id, branch_id, current_date, last_closed_at, closed_by
night_audit_runs id, branch_id, business_date, started_at, finished_at,
                 status (running|completed|failed), summary (json)
```

Wrap the existing logic in a transaction keyed on `(branch_id, business_date)` so a second run is a no-op. `calculateTotal()` is currently **duplicated** between `ClerkController` and `ProcessNoShowsAndReport` — collapse it while here.

---

## 10. Payments — **P1**, size M

**Why a chain needs it.** A folio balance is meaningless without payments against it. Deposits, partial payments, multiple payment methods on one stay, refunds and chargebacks are all normal.

**Current state — not at all.** `billings.payment_method` and `billings.payment_status` are two enum columns on the folio header (`database/migrations/2025_05_23_000006_create_billings_table.php:18-19`). There is no payment *record* — no amount, no timestamp, no reference, no ability to take two payments or issue a refund.

Card storage itself has been fixed: `reservations.credit_card_details` now holds only a masked guarantee (`App\Support\CardGuarantee`), and raw PANs no longer reach the database or the log.

**What it would take.**

```
payments  id, billing_id, branch_id, taken_by (users.id), amount,
          method (cash|card|bank_transfer|travel_agency|voucher),
          reference, status (authorised|captured|refunded|failed),
          processed_at, notes
```

`billings.payment_status` becomes derived from the sum of payments versus the folio total. **Do not add a real payment gateway** — for a portfolio piece, a well-modelled payments table with a simulated capture step demonstrates the same understanding without API keys or PCI surface.

---

## 11. Availability & overbooking — **P2**, size S

**Why a chain needs it.** Hotels deliberately oversell, because a predictable percentage of bookings do not arrive. They also need to stop-sell a room type on specific dates.

**Current state — partial, but the core is genuinely good.** `app/Services/RoomAvailability.php` (99 lines) derives date-aware availability by counting overlapping active reservations against branch inventory, with correct hotel overlap semantics (a stay ending on the day another begins does not conflict) and an `$excludeReservationId` for edits. It is enforced on all five booking paths. **This is the strongest code in the repository** and needs no rework.

What is missing sits on top of it: no overbooking allowance, no stop-sell/closed-to-arrival controls, and no inventory hold — a `pending` reservation holds a room indefinitely with no expiry.

**What it would take.**

```
inventory_controls  id, branch_id, room_type_id, date,
                    overbook_allowance, stop_sell, closed_to_arrival,
                    min_stay
reservations.hold_expires_at
```

`RoomAvailability::remaining()` adds the allowance and returns zero when `stop_sell` is set. Small, surgical change to well-structured existing code.

---

## 12. Group / corporate / agency — **P2**, size M

**Why a chain needs it.** Agency and corporate business is booked on negotiated rates, earns commission, and is invoiced to the account rather than collected at the desk.

**Current state — partial.** Travel agency block booking exists and is the most sophisticated flow in the codebase — minimum 3 rooms, maximum 3 suites, live availability check per room type, quotation split evenly across rooms, wrapped in a DB transaction (`ClerkController::storeTravelAgencyBooking()`). `travel_agencies` and `travel_agency_bookings` back it.

Missing: no commission rate or commission tracking, no negotiated corporate rate (the agency pays rack rate minus an ad-hoc discount percentage typed in at booking time), no settlement or invoicing against the agency account, no rooming list (the names of who is actually staying), and no allotment with a release-back date.

**What it would take.** `travel_agencies.commission_rate` and `default_discount_percentage`; a `corporate_accounts` table mirroring it; `agency_invoices` + line items for settlement; and the rooming list falls out of `reservation_guests` from #7.

**P2 because** the existing implementation already reads as competent — the marginal signal from extending it is lower than from building an absent module.

---

## 13. Multi-branch depth — **P2**, size M

**Why a chain needs it.** Chain-level consolidated reporting and cross-property search are the reason head office runs one system instead of several.

**Current state — partial.** Branch scoping is enforced consistently: `RoleMiddleware` handles roles, clerks and managers are pinned to `Auth::user()->branch_id`, admins select a branch into `session('admin_selected_branch')`. That part is sound.

Missing: no cross-branch availability search ("Colombo is full — is Kandy free?"), no inter-property reservation transfer, and no consolidated chain-level report — `AdminController::dashboard()` aggregates, but every manager report is single-branch. Per-branch configuration is entirely implicit: `branches` has only `name`, `address`, `contact_number`, so check-in/check-out times, tax rate, currency and cancellation defaults are all hardcoded or absent.

Also note the duplication flagged in `CLAUDE.md`: `getBranchId()` exists in **three** near-identical private copies (`ClerkController`, `ManagerController`, `ReservationController`).

**What it would take.** Add `check_in_time`, `check_out_time`, `tax_rate`, `timezone`, `currency` to `branches`; a chain-level report that groups by branch; and collapse the three `getBranchId()` copies onto one trait or base-controller method.

---

## 14. Communications — **P2**, size S

**Current state — partial.** Six mail templates exist (`resources/views/emails/`): registration confirmation, reservation confirmation, reservation cancelled, payment confirmation, daily report, daily manager report.

Missing from the guest lifecycle: no pre-arrival email (typically 24–48h before, the natural upsell moment), no post-stay thank-you or review request, no modification confirmation when a booking changes, and no check-out receipt separate from the payment confirmation. There is no SMS channel and no notification preferences.

**What it would take.** Three Mailables plus a scheduled command for pre-arrival. Cheap, and it rounds out the lifecycle story. Laravel's notification system would be the idiomatic route if a second channel is ever wanted.

---

## 15. Code health & tests — **P1**, size M

**Current state — substantially improved. The test suite now passes: 47 tests, 123 assertions.**

- ~~The test suite does not pass.~~ **Resolved.** All four feature test files were rewritten against real routes and real behaviour, and `HasFactory` was added to the six models that lacked it (`User::factory()` previously fatalled). The suite covers the occupancy cap, the 4-weeks rule, availability refusal, back-to-back stays not conflicting, cancellation freeing inventory, card masking and Luhn rejection, the travel-agency 3-room minimum, per-branch report isolation, and nightly-command idempotency.
- Two dead-value defects were surfaced by writing the tests and are documented in `CLAUDE.md`: `billings.payment_status` has a `no_show` value that nothing writes, alongside the already-known dead `reservations.is_suite`.
- **Business logic is duplicated by convention.** The 4-weeks-to-1-month rule is copy-pasted across `ReservationController::store`/`update` and `ClerkController::storeReservation`/`updateReservation`; `calculateTotal()` exists twice; `getBranchId()` exists three times.
- **`AdminController::generateReport()` is unrouted dead code** that duplicates `ManagerController`.
- **PII in logs.** Heavy `Log::info` use throughout. Card fields are now excluded from the logged request payload in `ClerkController`, but names, emails and phone numbers still flow into `storage/logs/laravel.log`, and `APP_DEBUG=true` locally.

**What it would take.** Align the existing tests with the real routes and delete the ones asserting behaviour that was never built; add feature tests for the genuinely valuable invariants (double-booking prevention via `RoomAvailability`, the occupancy cap, the 4-weeks rule, branch scoping); collapse the duplicated helpers; delete `generateReport()`.

**A passing suite with ~15 meaningful tests is worth more here than any single new feature.** It is the difference between "wrote a lot of code" and "engineers software."

---

## Recommended order

If the goal is maximum credibility per unit of work, build in this order — each step makes the next cheaper.

1. ~~**#15 tests** — get the suite green first, so everything after is verifiable.~~ **Done: 47 passing.**
2. ~~**#4 front office views** — cheapest P0, almost no new schema.~~ **Done**, except room move.
3. **#3 lifecycle & audit** — small, unlocks honest room moves and status history. **Now the next item**, and it completes #4.
4. **#2 housekeeping** — self-contained, most visible domain signal.
5. **#1 rates & inventory** — the big one; unlocks #6 and #8.
6. **#6 ADR/RevPAR** + fix the GET-writes-rows defect — nearly free once #1 exists.
7. **#7 guest profile** — the cross-branch stay history is the best single demo moment.
8. Then #5, #8, #9, #10 as appetite allows.

Items #11–#14 are polish. #12 in particular is already the most competent flow in the codebase and gains the least from further work.

---

## Appendix: what is already good

Worth stating plainly, since a gap analysis reads as uniformly negative otherwise.

- **`App\Services\RoomAvailability`** is correct, well-factored and properly enforced on all five booking paths. The overlap semantics are right, including the subtle case that a stay ending on the day another begins does not conflict.
- **The travel agency block-booking flow** handles multi-room-type quantities, live availability, even quotation splitting and transactional integrity.
- **Branch scoping** is applied consistently across roles.
- **The residential-suite duration model** — including the 4-weeks-becomes-1-month rewrite — is a real, non-obvious business rule, correctly implemented (if duplicated).
- **Login** is stricter than typical: email + password + matching role + correct branch for staff, with rate limiting.
