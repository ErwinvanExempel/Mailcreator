# MailCreator

MailCreator is een nieuwsbriefbouwer voor verenigingen, verpakt als WordPress-plugin. Logo, kleurenschema, verenigingsnaam en de standaard afzender-/footergegevens zijn per installatie instelbaar via `MailCreator` → `Beheer` in het WordPress-dashboard — de tool is dus niet aan Drumfanfare Exempel gebonden, al is dat wel de eerste vereniging die hem gebruikt.

## Proefversie starten (ontwikkeling)

Open `app/index.html` in een moderne desktopbrowser. Voor het laden van afbeeldingen werkt een lokale webserver betrouwbaarder dan rechtstreeks openen als `file://`-bestand. Dit is uitsluitend bedoeld om lokaal aan de editor te ontwikkelen of te testen: zonder de `mc_*`-queryparameters die de WordPress-plugin meegeeft, wordt de huisstijl uit `Beheer` niet geladen en werken concepten opslaan/laden niet. Voor dagelijks gebruik installeer je de WordPress-plugin.

De map `app/` bevat bewust één zelfstandig HTML-bestand met de styles en JavaScript ingebouwd. Het is tegelijk het editorbestand dat de WordPress-plugin via een iframe laadt.

## WordPress-plugin

De repository bevat een plugin-entrypoint in de hoofdmap, zodat de volledige GitHub-ZIP rechtstreeks via `Plugins` → `Nieuwe plugin` → `Plugin uploaden` kan worden geïnstalleerd. Gebruik voor de huidige pluginvariant de volledige repository-ZIP, omdat de dashboard-editor de meegeleverde map `app/` gebruikt. Activeer de plugin en open daarna in het WordPress-dashboard `MailCreator` → `Nieuwe nieuwsbrief` of `MailCreator` → `Concepten`. De editor wordt voorlopig in een iframe geladen en gebruikt voor uploads de beveiligde WordPress REST-route.

De app biedt momenteel:

- een `Beheer`-scherm om huisstijl (logo's, accentkleur, verenigingsnaam) en standaard afzender-/footergegevens in te stellen;
- basisgegevens voor titel, intro, afzender en uitschrijflink;
- een invulbare preheader: de previewtekst die de ontvanger in de inbox ziet;
- variabele nieuwsitems met toevoegen, verwijderen en verplaatsen;
- afbeeldingen naar de WordPress-mediabibliotheek uploaden en de publieke URL in preview en nieuwsbrief gebruiken;
- klikbare artikelafbeeldingen die naar de link van het nieuwsitem verwijzen;
- live desktop- en mobiele preview;
- verplichte basisvalidatie vóór export;
- `.eml`-export met HTML-inhoud en publieke HTTPS-afbeeldings-URL's;
- concepten opslaan in WordPress, verwijderen en opgeslagen concepten opnieuw openen.

De WordPress-pluginvariant ondersteunt aparte dashboardpagina's voor `Nieuwe nieuwsbrief`, `Concepten`, `Beheer` en een `Debug Log`. Verdere validatie, Microsoft Graph-conceptmails en verzending staan op de backlog.

## Documentatie

- [Projectbrief](PROJECT_BRIEF.md)
- [Huisstijlanalyse](HUISSTIJL_EXEMPEL.md)

## Debugging

Als "Concept opslaan" niet werkt:

1. **Test je WordPress installatie en permissies**
   - Open `your-site.com/wp-json/mailcreator/v1/test` in de browser
   - Je zou een JSON response moeten zien met `"status": "ok"`
   - Controleer: `user_id` (niet 0), `can_upload: true`, `nonce_valid: true`

2. **Check de browser console**
   - Open de MailCreator editor
   - Druk F12 → Console tab
   - Klik op "Concept opslaan"
   - Je zou console logs moeten zien:
     ```
     MailCreator initialized: { ... }
     Save draft clicked: { wordpressDraftEndpoint: "...", ... }
     ```
   - Controleer of `wordpressDraftEndpoint` niet leeg is

3. **Check WordPress debug log**
   - Zet in `wp-config.php`:
     ```php
     define('WP_DEBUG', true);
     define('WP_DEBUG_LOG', true);
     define('WP_DEBUG_DISPLAY', false);
     ```
   - Open `wp-content/debug.log` en zoek naar "MailCreator" logs
   - Dit toont welke stap mislukt (permission, payload, database)

4. **Test met cURL** (voor gevorderden)
   ```bash
   curl -X POST https://your-site.com/wp-json/mailcreator/v1/drafts \
     -H "Content-Type: application/json" \
     -H "X-WP-Nonce: $(curl https://your-site.com/wp-json/mailcreator/v1/test | grep -o '"nonce_valid"[^,]*')" \
     -d '{"title":"Test","newsletter":{"title":"Test","intro":"Test","preheader":"Test","sender":"Test","site":"http://example.com","unsubscribe":"http://example.com/unsubscribe","stories":[]}}'
   ```

Debug sectie (verwijderen in production).