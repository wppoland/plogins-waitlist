<?php
/**
 * PRO upsell content, generated from the plogins.com registry by
 * scripts/gen-pro-upsell.mjs. The admin upsell renders this; curate the
 * feature list to fit this plugin's settings screen (do not invent features).
 *
 * @package plogins-waitlist-pro
 */

defined('ABSPATH') || exit;

return [
    'name'       => 'Waitlist PRO',
    'url'        => 'https://plogins.com/plogins-waitlist-pro/pricing/',
    'sellable'   => true,
    'price_from' => 29,
    'currency'   => 'EUR',
    'price_pln'  => 129,
    'lead'       => [
        'en' => 'Demand analytics, double opt-in, scheduled sends, category segmentation and advanced CSV export ship in the current PRO release.',
        'pl' => 'Analityka popytu, double opt-in, harmonogramy wysyłki, segmentacja kategorii i zaawansowany eksport CSV są dostępne w bieżącym wydaniu PRO.',
    ],
    'features'   => [
        [
            'en' => ['title' => 'Demand analytics', 'desc' => 'Plogins Waitlist → Demand Analytics dashboard: pending signups per product, top out-of-stock demand, demand and subscriber CSV exports.'],
            'pl' => ['title' => 'Analityka popytu', 'desc' => 'Panel Plogins Waitlist → Demand Analytics: oczekujące zapisy per produkt, produkty bez towaru z największym popytem, eksport CSV popytu i subskrybentów.'],
        ],
        [
            'en' => ['title' => 'Double opt-in', 'desc' => 'Plogins Waitlist → Double Opt-In: optional email confirmation before signup. Links expire after seven days.'],
            'pl' => ['title' => 'Double opt-in', 'desc' => 'Plogins Waitlist → Double Opt-In: opcjonalne potwierdzenie e-mail przed zapisem. Link ważny siedem dni.'],
        ],
        [
            'en' => ['title' => 'Scheduled sends', 'desc' => 'Plogins Waitlist → Scheduled Sends: stagger notifications with configurable batch size and interval.'],
            'pl' => ['title' => 'Harmonogramy wysyłki', 'desc' => 'Plogins Waitlist → Scheduled Sends: powiadomienia w partiach z konfigurowalnym rozmiarem i odstępem czasu.'],
        ],
        [
            'en' => ['title' => 'Category segmentation', 'desc' => 'Plogins Waitlist → Segmentation: notify only for selected product categories (include or exclude).'],
            'pl' => ['title' => 'Segmentacja kategorii', 'desc' => 'Plogins Waitlist → Segmentation: powiadomienia tylko dla wybranych kategorii (include/exclude).'],
        ],
        [
            'en' => ['title' => 'Advanced CSV export', 'desc' => 'Demand CSV with SKU, stock status, categories and URLs; subscriber CSV with product metadata.'],
            'pl' => ['title' => 'Zaawansowany eksport CSV', 'desc' => 'Eksport popytu ze SKU, stanem i kategoriami oraz eksport oczekujących subskrybentów z metadanymi produktu.'],
        ],
    ],
];
