# IT Service Level1 — Website Skeleton (DE/EN)

Dieses Gerüst ist bewusst **inhaltfrei**. Fülle die Seiten unter `/de` und `/en`.
- Styles in `/assets/css/style.css`
- Skripte in `/assets/js/script.js`
- PHPMailer-Dateien unter `/assets/php/` ablegen (oder per Composer installieren)
- `mailer.php` implementieren (Strato SMTP)

## Struktur
- /de und /en: je eigene Seiten pro Sprache (SEO-freundlich)
- /assets: gemeinsame Ressourcen
- /partials: optionale Bausteine für Header/Footer
- /docs: Hinweise

## Nächste Schritte
1. Titel + Meta Description je Seite setzen.
2. Header/Footer einbauen.
3. Inhalte erstellen (Start, Über uns, Leistungen, Kontakt).
4. `sitemap.xml` mit echten URLs füllen.
5. `robots.txt` ggf. anpassen.
