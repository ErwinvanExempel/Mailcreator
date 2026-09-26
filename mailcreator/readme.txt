=== MailCreator ===
Contributors: drumfanfare-exempel
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 0.3.7

Nieuwsbriefeditor met instelbare huisstijl per vereniging.

== Description ==
De plugin voegt `Nieuwe nieuwsbrief`, `Concepten` en `Beheer` toe aan het WordPress-dashboard. Nieuwsbrieven kunnen als WordPress-concept worden opgeslagen, later opnieuw worden geopend, en ook worden verwijderd. Via `Beheer` (capability `manage_options`) stelt elke installatie zijn eigen huisstijl in: verenigingsnaam, accentkleur, logo voor de tool en voor de mail, en de standaard afzender-, website-, uitschrijf- en adresgegevens.

== Installation ==
1. Upload de volledige repository-ZIP of de map mailcreator naar WordPress.
2. Activeer MailCreator via Plugins.
3. Open MailCreator in het WordPress-dashboard.

== REST API ==
De plugin registreert de beveiligde uploadroute:
`/wp-json/mailcreator/v1/media`

De route accepteert een multipart-bestand met veldnaam `image` en vereist een ingelogde gebruiker met de capability `upload_files`.

De plugin registreert ook:
`/wp-json/mailcreator/v1/drafts`
`/wp-json/mailcreator/v1/drafts/{id}`

Deze routes vereisen een ingelogde gebruiker met de capability `upload_files` en een geldige WordPress REST-nonce.
