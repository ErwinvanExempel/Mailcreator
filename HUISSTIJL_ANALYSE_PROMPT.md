# Prompt voor M365 Copilot: analyse huisstijl Drumfanfare Exempel

Gebruik onderstaande prompt in M365 Copilot nadat je alle relevante huisstijlmaterialen hebt toegevoegd of beschikbaar hebt gemaakt, zoals logo's, afbeeldingen, flyers, bestaande nieuwsbrieven, Word-documenten, PowerPoint-presentaties, PDF's en andere communicatiemiddelen.

---

## Prompt

Je bent een ervaren brand designer, communicatiespecialist en front-end e-maildesigner. Analyseer alle aangeleverde documenten en afbeeldingen van Drumfanfare Exempel zorgvuldig. Het doel is niet om nu een ontwerp te maken, maar om een feitelijke en bruikbare huisstijlreferentie op te stellen voor een toekomstige tool waarmee nieuwsbrieven als HTML worden gemaakt.

Lever je antwoord uitsluitend aan als de inhoud van een Markdown-bestand met de bestandsnaam:

`HUISSTIJL_EXEMPEL.md`

Geef geen losse toelichting buiten dit Markdown-bestand. Als je omgeving het ondersteunt, maak dan daadwerkelijk een downloadbaar `.md`-bestand aan. Gebruik alleen informatie die uit de aangeleverde materialen blijkt. Maak duidelijk onderscheid tussen:

- direct waargenomen kenmerken;
- redelijke interpretaties;
- aanbevelingen of aannames die nog door Drumfanfare Exempel bevestigd moeten worden.

## Analyseer minimaal de volgende onderdelen

### 1. Algemene merkidentiteit

- Welke uitstraling, sfeer en persoonlijkheid heeft Drumfanfare Exempel?
- Welke waarden of boodschappen komen naar voren?
- Voor welke doelgroep lijken de communicatiemiddelen bedoeld?
- Welke visuele kenmerken zijn kenmerkend en consequent aanwezig?

### 2. Logo en beeldmerk

- Beschrijf alle aangetroffen logo's en varianten.
- Noteer welke variant waarvoor gebruikt lijkt te worden.
- Beschrijf kleuren, verhoudingen, vrije ruimte en plaatsing.
- Noteer eventuele regels of aandachtspunten voor gebruik op lichte en donkere achtergronden.
- Als logo-bestanden beschikbaar zijn, vermeld per bestand het formaat, de transparantie en eventuele technische aandachtspunten.
- Neem geen logo over in dit Markdown-bestand en verzin geen ontbrekende logo-regels.

### 3. Kleuren

Maak een tabel met alle herkenbare kleuren. Vermeld waar mogelijk:

- naam of functie van de kleur;
- HEX-waarde;
- RGB-waarde;
- CMYK-waarde;
- Pantone-waarde, alleen als die expliciet uit het bronmateriaal blijkt;
- gebruik, bijvoorbeeld achtergrond, tekst, accent, link of knop;
- bron en betrouwbaarheid van de waarde.

Als een kleur uit een afbeelding is geschat, markeer die waarde duidelijk als benadering en geef geen schijnprecisie.

### 4. Typografie

- Identificeer gebruikte lettertypes en varianten, als dat betrouwbaar kan.
- Beschrijf het gebruik van koppen, tussenkoppen, lopende tekst, bijschriften en knoppen.
- Noteer gewicht, kapitalisatie, uitlijning, regelafstand en eventuele kenmerkende letterspatiëring.
- Geef geschikte web- en e-mailveilige alternatieven wanneer het oorspronkelijke lettertype niet bruikbaar is in HTML-e-mail.
- Markeer duidelijk welke alternatieven aanbevelingen zijn en geen vastgestelde huisstijlregels.

### 5. Fotografie en afbeeldingen

- Beschrijf onderwerp, compositie, uitsnede, kleurgebruik, contrast en sfeer.
- Beschrijf hoe muzikanten, optredens, instrumenten, publiek en evenementen in beeld worden gebracht.
- Noteer eventuele voorkeuren voor beeldverhouding, beeldkaders of overlays.
- Benoem wat vermeden lijkt te worden.
- Geef praktische richtlijnen voor afbeeldingen in een nieuwsbrief, inclusief aanbevolen verhouding en veilige uitsnede, maar markeer nieuwe aanbevelingen als aanbeveling.

### 6. Grafische elementen en vormtaal

Analyseer waar relevant:

- lijnen, kaders, patronen en achtergronden;
- vormen, hoeken en eventuele rondingen;
- iconen en illustraties;
- schaduwen, texturen en effecten;
- witruimte, marges en informatiedichtheid;
- visuele hiërarchie en uitlijning.

### 7. Schrijfstijl en tone of voice

- Beschrijf de toon van teksten.
- Noteer of de communicatie formeel, informeel, enthousiast, verenigingsgericht of uitnodigend is.
- Beschrijf aanspreekvorm, woordkeuze, zinslengte en gebruik van uitroeptekens.
- Geef enkele korte, zelfgeschreven voorbeeldzinnen die de stijl illustreren. Kopieer geen lange passages uit bronmateriaal.
- Benoem woorden, formuleringen of toon die waarschijnlijk niet passen.

### 8. Nieuwsbriefspecifieke vertaling

Vertaal de waargenomen huisstijl naar praktische richtlijnen voor een vaste HTML-nieuwbrief-template. Beschrijf in ieder geval:

- aanbevolen breedte en algemene structuur;
- header en positie van het logo;
- titel en introtekst;
- opbouw van herhaalbare nieuwsitems;
- gebruik van afbeeldingen per nieuwsitem;
- koppen, bodytekst en links;
- call-to-action-knoppen, als die bij de huisstijl passen;
- footer met afzenderdetails en uitschrijflink;
- leesbaarheid op desktop en in gangbare e-mailclients;
- aandachtspunten voor Outlook en beperkte CSS-ondersteuning.

Geef hier nog geen volledige HTML en ontwerp geen nieuwe visuele stijl. Beschrijf alleen de richtlijnen die later door een ontwikkelaar kunnen worden toegepast.

### 9. Consistentie en onzekerheden

- Welke kenmerken komen in meerdere bronnen terug?
- Welke bronnen wijken van elkaar af?
- Welke onderdelen lijken verouderd of incidenteel?
- Welke ontbrekende informatie moet Drumfanfare Exempel nog bevestigen?
- Welke punten mogen niet als vaste huisstijlregel worden aangenomen?

### 10. Samenvatting voor ontwikkelaars

Sluit af met een compacte sectie met:

- vaste stijlregels;
- waarschijnlijke stijlregels;
- nog te bevestigen keuzes;
- concrete technische consequenties voor de nieuwsbriefbouwer;
- een lijst van benodigde assets, zoals logo's, lettertypes en voorbeeldafbeeldingen.

## Gewenste structuur van het Markdown-bestand

Gebruik precies deze hoofdstructuur, tenzij een onderdeel niet van toepassing is:

```markdown
# Huisstijlanalyse Drumfanfare Exempel

- Analyseversie:
- Datum:
- Geanalyseerde bronnen:
- Algemene betrouwbaarheid van de analyse:

## 1. Algemene merkidentiteit
## 2. Logo en beeldmerk
## 3. Kleuren
## 4. Typografie
## 5. Fotografie en afbeeldingen
## 6. Grafische elementen en vormtaal
## 7. Schrijfstijl en tone of voice
## 8. Nieuwsbriefspecifieke vertaling
## 9. Consistentie en onzekerheden
## 10. Samenvatting voor ontwikkelaars

## Openstaande bevestigingen
```

Gebruik duidelijke tabellen waar dat helpt. Voeg bij twijfel een expliciete onzekerheidsmarkering toe, bijvoorbeeld `Vastgesteld`, `Waarschijnlijk`, `Benadering` of `Nog te bevestigen`.

Controleer vóór oplevering of:

1. alle belangrijke observaties aan een bron gekoppeld zijn;
2. geschatte kleurwaarden niet als officieel worden gepresenteerd;
3. aanbevelingen duidelijk gescheiden zijn van waargenomen huisstijlregels;
4. de uitschrijflink en afzenderinformatie in de toekomstige nieuwsbrief zijn meegenomen;
5. het resultaat zonder extra uitleg als zelfstandig Markdown-bestand gelezen kan worden;
6. je geen volledig nieuwsbriefontwerp of HTML-code hebt toegevoegd.
