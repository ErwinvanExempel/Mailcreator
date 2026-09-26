# MailCreator

## Doel

MailCreator wordt een gebruiksvriendelijke tool om nieuwsbrieven op te stellen en als HTML te exporteren. De tool is niet langer aan één vereniging gebonden: logo, kleurenschema, verenigingsnaam en de standaard afzender-/footergegevens zijn per WordPress-installatie instelbaar via het `Beheer`-scherm van de plugin. Drumfanfare Exempel is de eerste vereniging die de tool gebruikt; hun huisstijl is nu de instelbare standaardconfiguratie in plaats van hardcoded gedrag. De nieuwsbrief wordt vervolgens vanuit de Microsoft 365-omgeving verstuurd, zodat de afhankelijkheid van een groeiende Mailchimp-contactlijst kleiner wordt.

## Eerste doelgroep

- Primair: de PR-verantwoordelijke van Drumfanfare Exempel.
- Later: meerdere leden van het PR-team.
- Ontvangers: bestaande fans en volgers van Drumfanfare Exempel.
- Andere verenigingen: kunnen de plugin op hun eigen WordPress installeren en via `Beheer` hun eigen huisstijl instellen, zonder dat de broncode hoeft te worden aangepast.

## Eerste versie

De eerste versie gebruikt een vaste template met een eenvoudige editor. De gebruiker kan:

- een titel invullen;
- een introtekst schrijven;
- een variabel aantal nieuwsitems toevoegen;
- per nieuwsitem tekst en eventueel een afbeelding opnemen;
- afzenderdetails invullen of toepassen;
- een uitschrijflink opnemen;
- de nieuwsbrief als `.eml`-bestand exporteren.

De tool is in eerste instantie gericht op desktopgebruik.

## Workflow versie 1

De gebruiker doorloopt de volgende vaste workflow:

1. **Startscherm**: een nieuwe nieuwsbrief starten.
2. **Basisgegevens**: titel, introtekst, afzenderdetails en uitschrijflink invullen.
3. **Nieuwsitems samenstellen**: een variabel aantal herhaalbare nieuwsitems toevoegen, verwijderen en van volgorde veranderen. Elk item bevat een kop, tekst, een optionele afbeelding en eventueel een link of knop.
4. **Live preview**: de volledige nieuwsbrief direct bekijken in de vaste template, met de huisstijl uit `Beheer`.
5. **`.eml` exporteren**: een e-mailbestand maken met de nieuwsbrief als HTML-body en publieke afbeeldings-URL's, zodat het bestand in Outlook kan worden geopend en gecontroleerd.
6. **Concept opslaan**: in de WordPress-pluginvariant de nieuwsbrief als WordPress-concept opslaan.
7. **Concepten beheren**: via `Concepten` een lijst openen en een opgeslagen nieuwsbrief opnieuw in de editor laden.
8. **Klaar-scherm**: bevestigen dat de nieuwsbrief klaar is, met opties om terug te gaan naar de editor of een nieuwe nieuwsbrief te starten.

Een eerste controle- en validatiestap is toegevoegd vóór `.eml`-export: titel, introtekst, uitschrijflink en minimaal één volledig nieuwsitem zijn verplicht. In de WordPress-pluginvariant kan de gebruiker concepten opslaan en later opnieuw openen. Uitgebreidere validatie en versiebeheer vallen nog buiten de huidige versie.

### Vaste structuur van de nieuwsbrief

Voor versie 1 is de volgorde vast:

1. header met logo;
2. titel;
3. introtekst;
4. één of meer nieuwsitems;
5. vaste footer met afzenderdetails en uitschrijflink.

In de WordPress-pluginvariant worden afbeeldingen vanuit de editor via een beveiligde interne REST-route naar de WordPress-mediabibliotheek geüpload. De teruggegeven publieke URL wordt direct gebruikt in de preview en in de gegenereerde nieuwsbrief. De losse standalone app behoudt voorlopig de eerdere CORS/Application Password-route als fallback.

## Vorm van de applicatie

De gekozen distributievorm is een WordPress-plugin in het dashboard. De zelfstandige HTML-app blijft beschikbaar als ontwikkelvariant (zie 'Voorlopige richting' hieronder). De oorspronkelijke opties waren:

| Vorm | Werking | Voordelen | Aandachtspunten |
|---|---|---|---|
| Losse `.html`-applicatie | De gebruiker opent een HTML-bestand in de desktopbrowser. | Geen installatie, eenvoudig te delen en snel te prototypen. | Browserbeperkingen rond lokaal bestanden, klembord en afbeeldingsverwerking moeten worden getest. |
| Lokale webapplicatie | De gebruiker start lokaal een klein programma en opent een vaste localhost-pagina. | Goede ontwikkelervaring en meer controle over bestanden en export. | Vereist een startcommando of launcher. |
| Installeerbare desktopapplicatie (`.exe`) | Een webinterface wordt verpakt als Windows-applicatie, bijvoorbeeld met Tauri of Electron. | Een herkenbare app met snelkoppeling en later ruimte voor lokale opslag. | Meer technische complexiteit, packaging en onderhoud. |

### Voorlopige richting

MailCreator wordt uitsluitend nog als WordPress-plugin gebruikt. Daarmee kunnen gebruikers, rechten, concepten en de WordPress-mediabibliotheek op dezelfde plek worden beheerd. `app/index.html` blijft bestaan als het editorbestand dat de plugin via een iframe laadt; alleen het losse/standalone-gebruik buiten de plugin om komt te vervallen.

Een `.exe` is voorlopig niet nodig. Een aparte desktopverpakking kan later opnieuw worden overwogen, maar is geen onderdeel van de huidige WordPress-richting.

### WordPress-pluginvariant

De plugin staat in `mailcreator/` en voegt beveiligde dashboardpagina's toe voor `Nieuwe nieuwsbrief` en `Concepten`, voor gebruikers met de capability `upload_files`. De plugin registreert `/wp-json/mailcreator/v1/media` voor media-upload, `/wp-json/mailcreator/v1/drafts` voor opslaan en `/wp-json/mailcreator/v1/drafts/{id}` voor het laden van een concept. De bestaande editor wordt voorlopig vanuit de pluginpagina in een iframe geladen; het verwijderen van die tussenlaag en het opsplitsen naar plugin-assets is technisch onderhoud.

### Beheer-instellingen (huisstijl per installatie)

De plugin voegt een `Beheer`-pagina toe, alleen zichtbaar voor gebruikers met de capability `manage_options`. Daarin is per WordPress-installatie instelbaar:

- naam van de vereniging;
- accentkleur (kleurenschema);
- logo in de tool (editor-header) en logo in de mail, elk apart te uploaden via de WordPress-mediabibliotheek;
- standaard afzendernaam, website, uitschrijflink en adres voor de mailfooter;
- optionele link 'Voorkeuren wijzigen'.

Deze instellingen zijn de nieuwe standaardwaarden voor nieuwe nieuwsbrieven en gelden ook met terugwerkende kracht voor eerder opgeslagen concepten, omdat huisstijl niet per concept wordt vastgelegd. Zolang niets is aangepast, blijven de huidige Exempel-waarden gehandhaafd als standaard, zodat de bestaande installatie ongewijzigd blijft werken.

### Exportformaat: `.eml`

Een `.eml`-bestand is een standaard MIME-e-mailbestand met een HTML-body. Het vaste headerlogo wordt via een publieke HTTPS-URL geladen. Outlook kan het bestand openen, waarna de gebruiker de inhoud kan controleren en als concept kan gebruiken.

Een `.msg`-export is definitief geen doel. Dat formaat is Outlook-specifiek en aanzienlijk complexer, terwijl `.eml` in de praktijk volstaat.

De `.eml`-export gebruikt voor het vaste logo en geüploade nieuwsitem-afbeeldingen publieke HTTPS-URL's. Alleen lokale fallback-afbeeldingen kunnen als inline MIME-onderdelen worden opgenomen. Outlook kan externe afbeeldingen standaard blokkeren totdat de ontvanger ze toestaat. De export moet worden gevalideerd met de gebruikte Outlook/M365-versie.

## Huisstijl

De huisstijlanalyse staat in [HUISSTIJL_EXEMPEL.md](HUISSTIJL_EXEMPEL.md). De analyse is gebaseerd op posters, flyers en bestaande Mailchimp-nieuwsbrieven en heeft een middelhoog betrouwbaarheidsniveau. Deze analyse is specifiek voor Drumfanfare Exempel en dient als bron voor hun eigen configuratie in het `Beheer`-scherm van de plugin; andere verenigingen stellen hun eigen huisstijl in via datzelfde scherm, zonder dat dit document van toepassing is.

Voor de eerste nieuwsbrief-template zijn de belangrijkste uitgangspunten:

- een kaderbreedte van 660 px met één kolom;
- een witte contentcontainer op een lichtgrijze buitenachtergrond;
- rood, wit en zwart als primaire kleuren, waarbij de definitieve roodwaarde nog moet worden bevestigd;
- Roboto met Helvetica/Arial-fallbacks;
- zwarte, links uitgelijnde koppen en bodytekst;
- bodytekst van 16 px en een footer van 12 px;
- een negatief logo op een rood headerblok;
- één beeld op volle breedte per nieuwsitem;
- tabelgebaseerde HTML met inline CSS voor Outlook-compatibiliteit;
- afbeeldingen met expliciete afmetingen en alt-tekst;
- een footer met afzendergegevens, adres, voorkeuren wijzigen en uitschrijven.

De analyse maakt onderscheid tussen vastgestelde kenmerken, waarschijnlijke kenmerken, benaderingen en nog te bevestigen keuzes. De kleurvoorstellen uit beeldanalyse zijn daarom nog geen definitieve design-tokens. Ook zijn de officiële logobestanden, lettertypen voor drukwerk en definitieve footer- en merknaamkeuzes nog niet aangeleverd.

## Nog niet geïmplementeerd

- versiebeheer van opgeslagen nieuwsbrieven;
- gebruikersaccounts, rollen en autorisatie;
- rechtstreeks verzenden van e-mails;
- een volledig vrije drag-and-drop-opmaak;
- integratie met Mailchimp of Microsoft 365.

Deze onderwerpen kunnen later aan de backlog worden toegevoegd.

## Vastgestelde vervolgstappen

1. Officiële logobestanden, kleurwaarde en merknaamgebruik van Drumfanfare Exempel vastleggen in het `Beheer`-scherm; fontkeuze blijft vooralsnog code-niveau (Roboto/Helvetica/Arial).
2. De keuze voor rechte of afgeronde hoeken in digitale uitingen bevestigen; voor Outlook zijn rechte hoeken het betrouwbaarste uitgangspunt.
3. De Nederlandse of Engelse footer en de definitieve afzendergegevens vastleggen.
4. De huidige iframe-editor eventueel opsplitsen naar afzonderlijke WordPress-plugin-assets.
5. `.eml`-export en externe afbeeldingen testen in de gebruikte Outlook/M365-versie.
6. Uitgebreidere validatie toevoegen vóór export.
7. Daarna aanvullende functies plannen, zoals meerdere gebruikers, multi-vereniging en Microsoft 365-verzending.

## Backlog

- Uitgebreidere controle- en validatiestap vóór export.
- Een conceptmail automatisch aanmaken via Microsoft Graph.
	- Vereist Microsoft-authenticatie en een Entra ID-appregistratie.
	- Vereist passende Graph-rechten om conceptberichten in Outlook aan te maken.
	- De Graph-route moet HTML en inline afbeeldingen met Content-ID correct kunnen overnemen.
- Meerdere gebruikers, rollen en autorisatie.
- Eén WordPress-installatie die meerdere verenigingen tégelijk bedient. `Beheer` ondersteunt nu één huisstijl per installatie; elke vereniging heeft dus voorlopig een eigen WordPress nodig.
	- Een WordPress-adminpagina voor het beheren en selecteren van verenigingsprofielen binnen dezelfde installatie.
	- Meerdere huisstijlprofielen naast elkaar (naast het huidige ene `Beheer`-profiel) met logo, kleuren, lettertypekeuzes, merknaam, afzendergegevens, adres en uitschrijflink.
	- De geselecteerde huisstijl toepassen op editor, live preview en `.eml`-export.
	- Verenigingsspecifieke concepten en rechten, zodat gebruikers alleen toegang krijgen tot de verenigingen waarvoor zij gemachtigd zijn.
	- Een standaardprofiel en fallback instellen wanneer een vereniging nog geen volledig profiel heeft.

## Open vragen voor de volgende ontwerpstap

- Welke gegevens moeten in de afzenderdetails staan?
- Komt de uitschrijflink uit een bestaande Microsoft 365-oplossing of moet de gebruiker deze zelf invoeren?
- Welke afbeeldingsformaten en maximale bestandsgroottes zijn gewenst?
- Moet een nieuwsbrief één hoofdafbeelding hebben, of alleen afbeeldingen per nieuwsitem?
- Welke Exempel-huisstijlmaterialen zijn al beschikbaar?
- Welke Outlook/M365-versie wordt gebruikt om `.eml`-bestanden te openen en als concept te gebruiken?
- Kan de gekozen Outlook/M365-versie MIME-inline-afbeeldingen uit `.eml` correct tonen?
