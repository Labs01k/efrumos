<?php

namespace App\Services\Integration\OneC;

use Illuminate\Support\Facades\Log;

/**
 * Maps our `delivery_states.id` (Moldova raions, `country_id=140` — the
 * dropdown a customer picks at checkout) onto 1С's `City` code, required by
 * `CreateClientPoint` on the ws_amo.1cws WSDL. 1С's own city list is not a
 * clean raion enum — it's ~1530 "Raion, Locality" rows (whole village-level
 * database) — but every raion also has its own top-level, no-comma entry
 * (id 1-70ish), which is what this maps to. Verified live against
 * http://agent.solvex.md/test_db/ws/ws_amo.1cws GetCities() on 2026-09-09.
 *
 * Five raions have no clean, unambiguous match and fall back to
 * DEFAULT_CITY_CODE with a logged warning rather than a silent guess:
 *   - Gagauzia — an autonomous region grouping several raions (Comrat,
 *     Ceadîr-Lunga, Vulcănești), not a single 1С city row.
 *   - Lapusna — a pre-2003 Soviet-era raion name, no longer an
 *     administrative unit; 1С's list has nothing under this name.
 *   - Tighina, Stînga Nistrului, Rîbnița — Transnistria; not present in
 *     1С's city list at all (only Tiraspol has a — z-prefixed, low
 *     confidence — entry, mapped below).
 * DEFAULT_CITY_CODE (8 = "Chisinau") was chosen as the plain, low-numbered
 * ("Chisinau" with the id-1..70-ish raion-level rows) entry — NOT the
 * higher ids 1000/1527 ("zChișinău"/"Chișinău"), which sit in a later batch
 * mixed with foreign cities (Barcelona/Milano/Yerevan/Germany) and look
 * like an unrelated, possibly duplicate, addition. This is a reasoned
 * best guess, not a confirmed fact — see efrumos-docs/open-decisions.md п.7.
 */
class OneCCityMapper
{
    public const DEFAULT_CITY_CODE = 8; // "Chisinau" — see class docblock

    /** @var array<int,int> delivery_states.id => 1С City code */
    private const MAP = [
        2187 => 7,   // Orhei
        2188 => 2,   // Soroca
        2190 => 31,  // Ungheni
        4232 => 35,  // Anenii Noi
        4233 => 10,  // Cimișlia
        4234 => 17,  // Căușeni
        4235 => 33,  // Cantemir
        4236 => 43,  // Călărași
        4237 => 23,  // Cahul
        4238 => 3,   // Çadır-Lunga
        4239 => 15,  // Briceni
        4240 => 13,  // Basarabeasca
        4241 => 16,  // Balti
        4242 => 6,   // Comrat
        4243 => 21,  // Criuleni
        4244 => 19,  // Dondușeni
        4245 => 14,  // Drochia
        4246 => 70,  // Dubăsari
        4247 => 28,  // Edineț
        4248 => 18,  // Fălești
        4249 => 25,  // Florești
        4251 => 22,  // Glodeni
        4252 => 30,  // Hîncești
        4254 => 32,  // Leova
        4255 => 29,  // Nisporeni
        4256 => 9,   // Ocnița
        4258 => 34,  // Rezina
        4260 => 11,  // Rîșcani
        4261 => 4,   // Sîngerei
        4262 => 20,  // Șoldănești
        4264 => 5,   // Ștefan Vodă
        4266 => 26,  // Strășeni
        4267 => 39,  // Taraclia
        4268 => 40,  // Telenești
        4273 => 1,   // Ialoveni (raion, distinct from the Chișinău suburb of the same name)
        4270 => 58,  // Tiraspol — only "zTiraspol" exists; low confidence, kept as closest real match

        // Chișinău municipality + suburbs — exact "Chisinau, <locality>" matches
        2182 => self::DEFAULT_CITY_CODE, // Chisinau itself
        4272 => 36,  // Chisinau, Truseni
        4274 => 45,  // Chisinau, Stauceni
        4275 => 55,  // Chisinau, Durlesti
        4276 => 64,  // Chisinau, Dumbrava
        4277 => 57,  // Chisinau, Vatra
        4278 => 1023, // Chisinau, Tohatin
        4279 => 69,  // Chisinau, Bubuieci
        4280 => 95,  // Chisinau, Singera
    ];

    public function cityCodeFor(?int $deliveryStateId): int
    {
        if ($deliveryStateId !== null && isset(self::MAP[$deliveryStateId])) {
            return self::MAP[$deliveryStateId];
        }

        Log::warning('OneCCityMapper: no mapping for delivery_states id, using default', [
            'delivery_state_id' => $deliveryStateId,
            'default_used' => self::DEFAULT_CITY_CODE,
        ]);

        return self::DEFAULT_CITY_CODE;
    }
}
